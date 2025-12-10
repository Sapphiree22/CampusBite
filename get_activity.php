<?php
// get_activity.php
header('Content-Type: application/json');
require 'db.php';

$uid = $_GET['user_id'] ?? 0;

if (!$uid) {
    echo json_encode(["success" => false, "message" => "No user ID"]);
    exit();
}

// 1. GET RESERVATIONS (Active Tickets)
$sql_res = "SELECT r.*, t.table_number 
            FROM reservation r 
            LEFT JOIN table_info t ON r.table_id = t.table_id 
            WHERE r.user_id = $uid 
            ORDER BY r.reservation_time DESC LIMIT 5";
$result_res = $conn->query($sql_res);
$reservations = [];
while ($row = $result_res->fetch_assoc()) $reservations[] = $row;

// 2. GET ORDER HISTORY
$sql_ord = "SELECT order_id, total_amount, status, order_date_time 
            FROM orders 
            WHERE user_id = $uid 
            ORDER BY order_date_time DESC LIMIT 5";
$result_ord = $conn->query($sql_ord);
$orders = [];
while ($row = $result_ord->fetch_assoc()) $orders[] = $row;

echo json_encode([
    "success" => true,
    "reservations" => $reservations,
    "orders" => $orders
]);
?>