<?php
header('Content-Type: application/json');

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);

// Debug line to see what PHP receives
file_put_contents('php://stderr', print_r($data, true));

$username = trim($data['username'] ?? '');
$password = trim($data['password'] ?? '');
$role     = trim($data['role'] ?? '');

if (!$username || !$password) {
    echo json_encode(["success" => false, "message" => "Username and Password required"]);
    exit();
}

$conn = new mysqli("localhost", "root", "fireandwater", "campusbite");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "DB Connection Error"]);
    exit();
}

// Hash password
$hashed = password_hash($password, PASSWORD_DEFAULT);

// Insert into Admin table
$stmt = $conn->prepare("INSERT INTO Admin (User_name, Password, role) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $hashed, $role);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Staff/Admin added successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to add staff: " . $conn->error]);
}

$stmt->close();
$conn->close();
?>