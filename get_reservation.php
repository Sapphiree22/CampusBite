<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "fireandwater", "campusbite");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database error"]);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

if ($action === 'view_all_reservations') {
    $sql = "SELECT r.reservation_id, r.user_id, u.full_name AS customer_name,
                   r.table_id, t.table_number,
                   r.reservation_time, r.party_size, r.status
            FROM reservation r
            LEFT JOIN users u ON r.user_id = u.user_id
            LEFT JOIN table_info t ON r.table_id = t.table_id
            ORDER BY r.reservation_id DESC";

    $result = $conn->query($sql);
    $reservations = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $reservations[] = [
                'reservation_id' => $row['reservation_id'],
                'customer_name' => $row['customer_name'] ?: 'Guest',
                'table_number' => $row['table_number'] ?: 'N/A',
                'reservation_time' => $row['reservation_time'],
                'party_size' => $row['party_size'],
                'status' => $row['status'],
                'payment_method' => (rand(0,1) ? 'Cash' : 'Cash')
            ];
        }
    }

    echo json_encode(['success' => true, 'reservations' => $reservations]);
    $conn->close();
    exit();
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
$conn->close();
?>
