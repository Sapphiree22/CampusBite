<?php
header('Content-Type: application/json');

// Database connection details
$host = "localhost";
$db = "campusbite";
$user = "rrot";
$pass = "fireandwater";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// Read input JSON
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

if ($action === 'update_reservation_status') {
    $reservation_id = $input['reservation_id'] ?? null;
    $status = $input['status'] ?? null; // 'Confirmed' or 'Cancelled'

    if (!$reservation_id || !$status) {
        echo json_encode(['success' => false, 'message' => 'Missing reservation ID or status.']);
        $conn->close();
        exit;
    }

    // Sanitize and validate inputs
    $reservation_id = $conn->real_escape_string($reservation_id);
    $status = $conn->real_escape_string($status);
    
    // Status validation
    if (!in_array($status, ['Confirmed', 'Cancelled', 'Pending', 'Completed'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid status value.']);
        $conn->close();
        exit;
    }

    $sql = "UPDATE reservation SET status = '{$status}' WHERE reservation_id = '{$reservation_id}'";
    
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['success' => true, 'message' => 'Reservation status updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating record: ' . $conn->error]);
    }
    $conn->close();
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
$conn->close();
?>