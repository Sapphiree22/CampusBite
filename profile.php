<?php

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success"=>false, "message"=>"Not logged in"]);
    exit();
}

// Database connection
$dbHost = '127.0.0.1';
$dbUser = 'root';
$dbPass = 'fireandwater';
$dbName = 'campusbite';

$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]);
    exit;
}

// Fetch the logged-in user data
$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT user_id, full_name, email, student_number, course, date_created 
    FROM users 
    WHERE user_id = ?
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo json_encode([
        "success" => true,
        "name" => $user['full_name'],
        "studentNumber" => $user['student_number'],
        "course" => $user['course'],
        "email" => $user['email'],
        "dateJoined" => $user['date_created']
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "User not found."
    ]);
}

// Close connections
$stmt->close();
$conn->close();
?>
