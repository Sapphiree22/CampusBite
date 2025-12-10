<?php
// fix_database.php
require 'db.php';
header('Content-Type: text/plain');

echo "--- STARTING AUTO-FIX ---\n";

// 1. Fix Columns
$conn->query("ALTER TABLE item_variant ADD COLUMN IF NOT EXISTS stock INT DEFAULT 50");
$conn->query("ALTER TABLE item_variant ADD COLUMN IF NOT EXISTS is_popular TINYINT(1) DEFAULT 0");
$conn->query("ALTER TABLE orders ADD COLUMN IF NOT EXISTS status VARCHAR(50) DEFAULT 'Pending'");
$conn->query("ALTER TABLE orders ADD COLUMN IF NOT EXISTS total_amount DECIMAL(10,2) DEFAULT 0.00");
$conn->query("ALTER TABLE orders ADD COLUMN IF NOT EXISTS order_date_time DATETIME DEFAULT CURRENT_TIMESTAMP");
echo "1. Columns Checked/Fixed.\n";

// 2. Ensure System is OPEN
$conn->query("CREATE TABLE IF NOT EXISTS system_settings (setting_id INT AUTO_INCREMENT PRIMARY KEY, setting_key VARCHAR(50) UNIQUE, setting_value VARCHAR(50))");
$conn->query("INSERT INTO system_settings (setting_key, setting_value) VALUES ('SYSTEM_STATUS', 'OPEN') ON DUPLICATE KEY UPDATE setting_value='OPEN'");
echo "2. System set to OPEN.\n";

// 3. Refill Stock
$conn->query("UPDATE item_variant SET stock = 50 WHERE stock <= 0");
echo "3. Stock Refilled to 50 for all items.\n";

// 4. Create Dummy Sales (For 'Popular Today')
$check = $conn->query("SELECT COUNT(*) FROM orders WHERE DATE(order_date_time) = CURDATE()");
if ($check->fetch_row()[0] == 0) {
    // Insert a fake order for today
    $conn->query("INSERT INTO orders (user_id, total_amount, status, order_date_time) VALUES (1, 500.00, 'Completed', NOW())");
    $oid = $conn->insert_id;
    // Link it to the first item variant
    $vid_res = $conn->query("SELECT variant_id FROM item_variant LIMIT 1");
    if ($vid_res && $row = $vid_res->fetch_assoc()) {
        $vid = $row['variant_id'];
        $conn->query("INSERT INTO order_item (order_id, variant_id, quantity, subtotal) VALUES ($oid, $vid, 5, 500.00)");
        echo "4. Created dummy sales data for today.\n";
    }
} else {
    echo "4. Sales data already exists for today.\n";
}

echo "\n--- SUCCESS! Refresh your Dashboard now. ---";
?>