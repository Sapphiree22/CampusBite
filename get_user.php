<?php
// get_user.php
session_start();
header('Content-Type: application/json');
require 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit();
}

$uid = $_SESSION['user_id'];

// Fetch logged-in user details
$sql = "SELECT user_id, full_name, email, student_number, course, date_created FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $uid);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode(["success" => true, "user" => $row]);
} else {
    echo json_encode(["success" => false, "message" => "User not found"]);
}
?>