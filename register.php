<?php
// register.php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0); // Turn off display to not break JSON

require 'db.php'; // Uses the central connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Get Inputs
    $fullName = trim($_POST['fullName'] ?? '');
    $studentNumRaw = trim($_POST['studentNum'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $pin = trim($_POST['pin'] ?? '');

    // 2. Validation
    if (!$fullName || !$studentNumRaw || !$email || !$course || !$pin) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
        exit;
    }

    // validate 7 digits
    if (!preg_match('/^\d{7}$/', $studentNumRaw)) {
        echo json_encode(['success' => false, 'message' => 'Student number must be 7 digits.']);
        exit;
    }

    // validate 4 digits
    if (!preg_match('/^\d{4}$/', $pin)) {
        echo json_encode(['success' => false, 'message' => 'PIN must be 4 digits.']);
        exit;
    }

    // 3. Check for Duplicates
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? OR student_number = ?");
    $stmt->bind_param("ss", $email, $studentNumRaw);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email or Student Number already registered.']);
        exit;
    }
    $stmt->close();

    // 4. Insert User
    $hashedPin = password_hash($pin, PASSWORD_DEFAULT);

    // Note: Column names match your new DB: full_name, student_number
    $stmt = $conn->prepare("INSERT INTO users (email, student_number, pin, full_name, course) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $email, $studentNumRaw, $hashedPin, $fullName, $course);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Registration successful!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database Error: ' . $conn->error]);
    }

    $stmt->close();
    $conn->close();
    exit();
}
?>