<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "root", "fireandwater", "campusbite");

$data = json_decode(file_get_contents('php://input'), true);

$user_id = 1; // HARDCODED for testing (Kathleen Belda). In real app, use $_SESSION['user_id']
$items = $data['items'] ?? [];
$total_amount = $data['total'] ?? 0.0;

if (empty($items)) {
    echo json_encode(["success" => false, "message" => "Cart is empty"]);
    exit();
}

// 1. Create Order
$stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status, order_date_time) VALUES (?, ?, 'Confirmed', NOW())");
$stmt->bind_param("id", $user_id, $total_amount);

if ($stmt->execute()) {
    $order_id = $stmt->insert_id;

    // 2. Add Items & Subtract Stock
    foreach ($items as $item) {
        $vid = $item['variant_id'];
        $qty = $item['qty'];
        $subtotal = $item['price'] * $qty;

        // Insert Item
        $conn->query("INSERT INTO order_item (order_id, variant_id, quantity, subtotal) VALUES ($order_id, $vid, $qty, $subtotal)");

        // SUBTRACT STOCK
        $conn->query("UPDATE item_variant SET stock = stock - $qty WHERE variant_id = $vid");
    }

    echo json_encode(["success" => true, "order_id" => $order_id]);
} else {
    echo json_encode(["success" => false, "message" => "Order failed"]);
}
$conn->close();
?>