<?php
// login.php
session_start();
require 'db.php'; // Uses central connection

$loginId = trim($_POST['loginId'] ?? '');
$loginPin = trim($_POST['loginPin'] ?? '');

if (empty($loginId) || empty($loginPin)) {
    // Redirect back with error
    header("Location: index.html?error=required"); 
    exit();
}

// 1. Find User (by Email OR Student Number)
$sql = "SELECT user_id, full_name, pin FROM users WHERE email = ? OR student_number = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $loginId, $loginId);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // 2. Verify PIN
    if (password_verify($loginPin, $row['pin'])) {
        // Success: Set Session
        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['full_name'] = $row['full_name'];
        
        // Redirect to Dashboard
        header("Location: dashboard.html");
        exit();
    }
}

// Failure: Redirect back
header("Location: index.html?error=invalid");
exit();
?>