<?php
header('Content-Type: application/json');
require 'db.php';

// Total tables
$total_res = $conn->query("SELECT COUNT(*) FROM table_info");
$total = $total_res ? $total_res->fetch_row()[0] : 20; // Default 20 if table missing

// Reserved tables (Pending or Confirmed for a future time today, or current)
// Simple check: count reservations for today that are not cancelled or completed
$reserved_res = $conn->query("SELECT COUNT(*) FROM reservation WHERE DATE(reservation_time) = CURDATE() AND status IN ('Pending', 'Confirmed')");
$reserved = $reserved_res ? $reserved_res->fetch_row()[0] : 0;

echo json_encode([
    "success" => true,
    "totalTables" => (int)$total,
    "reservedCount" => (int)$reserved,
    "availableTables" => (int)($total - $reserved)
]);
$conn->close();
?>