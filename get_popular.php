<?php
header('Content-Type: application/json');
require 'db.php';

// 1. Try to get Real Sales
$sql = "SELECT f.item_id, f.name, f.image_path as image, v.price, SUM(oi.quantity) as total_sold
        FROM order_item oi
        JOIN orders o ON oi.order_id = o.order_id
        JOIN item_variant v ON oi.variant_id = v.variant_id
        JOIN food_item f ON v.item_id = f.item_id
        WHERE o.status IN ('Confirmed', 'Completed') AND DATE(o.order_date_time) = CURDATE()
        GROUP BY f.item_id
        ORDER BY total_sold DESC LIMIT 4";

$result = $conn->query($sql);
$popular = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $popular[] = $row;
    }
}

// 2. FAILSAFE: If no sales today, show random items (Prevents 'Loading...' forever)
if (empty($popular)) {
    $fallback = $conn->query("SELECT f.item_id, f.name, f.image_path as image, v.price 
                              FROM food_item f 
                              JOIN item_variant v ON f.item_id = v.item_id 
                              LIMIT 4");
    if ($fallback) {
        while ($row = $fallback->fetch_assoc()) {
            $row['total_sold'] = 0;
            $popular[] = $row;
        }
    }
}

echo json_encode($popular);
?>