<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "root", "fireandwater", "campusbite");
if ($conn->connect_error) { echo json_encode(["success"=>false,"message"=>"DB Error"]); exit(); }

$action = $_GET['action'] ?? '';
$data = json_decode(file_get_contents("php://input"), true);
if(!$action && isset($data['action'])) $action = $data['action'];

// --- Get Staff List ---
if($action === 'get_staff'){
    $result = $conn->query("SELECT Admin_id, User_name, role FROM Admin");
    $staff = [];
    while($row = $result->fetch_assoc()) $staff[] = $row;
    echo json_encode(["success"=>true,"staff"=>$staff]);
    exit();
}

// --- Add Staff ---
if($action === 'add_staff'){
    $username = $conn->real_escape_string($data['User_name']);
    $password = password_hash($data['Password'], PASSWORD_DEFAULT);
    $role = $conn->real_escape_string($data['role']);

    $stmt = $conn->prepare("INSERT INTO Admin (User_name, Password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $password, $role);
    if($stmt->execute()){
        echo json_encode(["success"=>true,"message"=>"Staff added"]);
    } else {
        echo json_encode(["success"=>false,"message"=>"Failed to add staff"]);
    }
    exit();
}

// --- Update Role ---
if($action === 'update_role'){
    $id = (int)$data['Admin_id'];
    $role = $conn->real_escape_string($data['role']);
    $conn->query("UPDATE Admin SET role='$role' WHERE Admin_id=$id");
    echo json_encode(["success"=>true,"message"=>"Role updated"]);
    exit();
}

// --- Delete Staff ---
if($action === 'delete_staff'){
    $id = (int)$data['Admin_id'];
    $conn->query("DELETE FROM Admin WHERE Admin_id=$id");
    echo json_encode(["success"=>true,"message"=>"Staff deleted"]);
    exit();
}
$conn->close();
?>
