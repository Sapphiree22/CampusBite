<?php
header('Content-Type: application/json');
require 'db.php';

$data = json_decode(file_get_contents("php://input"), true);
$action = $data['action'] ?? $_GET['action'] ?? '';

// --- 1. USER: MAKE RESERVATION ---
if (isset($data['user_id']) && !isset($data['action'])) {
    $uid = $data['user_id'];
    $time = $data['reservation_time'];
    $size = $data['party_size'];

    // Find a free table
    $sql = "SELECT table_id FROM table_info 
            WHERE capacity >= ? 
            AND table_id NOT IN (
                SELECT table_id FROM reservation 
                WHERE reservation_time = ? AND status IN ('Pending', 'Confirmed')
            ) LIMIT 1";

    $stmt = $conn->prepare($sql);
    if (!$stmt) { echo json_encode(["success" => false, "message" => "DB Error"]); exit(); }
    
    $stmt->bind_param("is", $size, $time);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($row = $res->fetch_assoc()) {
        $tid = $row['table_id'];
        $stmt2 = $conn->prepare("INSERT INTO reservation (user_id, table_id, reservation_time, party_size, status) VALUES (?, ?, ?, ?, 'Pending')");
        $stmt2->bind_param("iisi", $uid, $tid, $time, $size);
        if ($stmt2->execute()) {
            echo json_encode(["success" => true, "message" => "Booked Table #$tid", "id" => $stmt2->insert_id]);
        } else {
            echo json_encode(["success" => false, "message" => "Save Failed"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "No tables available."]);
    }
    exit();
}

// --- 2. USER: GET MY RESERVATIONS (NEW FEATURE) ---
if ($action === 'get_user_reservations') {
    $uid = $_GET['user_id'];
    $sql = "SELECT r.*, t.table_number 
            FROM reservation r 
            LEFT JOIN table_info t ON r.table_id = t.table_id 
            WHERE r.user_id = ? AND r.status IN ('Pending', 'Confirmed') 
            ORDER BY r.reservation_time DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $res = $stmt->get_result();
    
    $data = [];
    while($row = $res->fetch_assoc()) $data[] = $row;
    
    echo json_encode(["success" => true, "reservations" => $data]);
    exit();
}

// --- 3. ADMIN ACTIONS ---
if ($action === 'view_all_reservations') {
    $res = $conn->query("SELECT r.*, u.full_name, t.table_number FROM reservation r LEFT JOIN users u ON r.user_id=u.user_id LEFT JOIN table_info t ON r.table_id=t.table_id ORDER BY r.reservation_time DESC");
    $data = [];
    while($r = $res->fetch_assoc()) $data[] = $r;
    echo json_encode(["success" => true, "reservations" => $data]);
    exit();
}

if ($action === 'get_system_status') {
    $res = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'SYSTEM_STATUS'");
    $status = $res->fetch_assoc()['setting_value'] ?? "OPEN";
    echo json_encode(["success" => true, "system_status" => $status]);
    exit();
}

if ($action === 'set_system_status') {
    $status = $data['status'];
    $conn->query("UPDATE system_settings SET setting_value = '$status' WHERE setting_key = 'SYSTEM_STATUS'");
    echo json_encode(["success" => true]);
    exit();
}

if ($action === 'update_reservation') {
    $id = $data['reservation_id'];
    $status = $data['status'];
    $conn->query("UPDATE reservation SET status='$status' WHERE reservation_id=$id");
    echo json_encode(["success" => true]);
    exit();
}
?>