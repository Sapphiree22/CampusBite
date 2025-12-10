<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/plain');

echo "1. Checking Database Settings...\n";
require 'db.php'; // This tests if db.php exists and connects
echo "   - Database Connected Successfully!\n\n";

echo "2. Checking 'orders' table for Revenue...\n";
$result = $conn->query("SELECT COUNT(*) as count, SUM(total_amount) as total FROM orders");
if ($result) {
    $row = $result->fetch_assoc();
    echo "   - Found " . $row['count'] . " orders.\n";
    echo "   - Total Revenue in DB: ₱" . ($row['total'] ?? '0.00') . "\n";
} else {
    echo "   - ERROR: Could not read 'orders' table. " . $conn->error . "\n";
}

echo "\n3. Checking 'food_item' for Images...\n";
$result = $conn->query("SELECT name, image_path FROM food_item LIMIT 1");
if ($result && $row = $result->fetch_assoc()) {
    echo "   - Found item: " . $row['name'] . "\n";
    echo "   - Image Path: " . $row['image_path'] . "\n";
} else {
    echo "   - ERROR: No items found or table missing.\n";
}
?>