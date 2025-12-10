<?php
header('Content-Type: application/json');
require 'db.php'; // Ensure db.php exists with correct credentials

// Use COALESCE to handle nulls and CAST to ensure correct types
$sql = "SELECT f.item_id, f.name, f.description, f.category, f.image_path, 
               v.variant_id, v.variant_name, v.price, 
               CAST(COALESCE(v.stock, 0) AS UNSIGNED) as stock, 
               v.is_popular
        FROM food_item f
        JOIN item_variant v ON f.item_id = v.item_id
        ORDER BY f.category, f.name";

$result = $conn->query($sql);

if (!$result) {
    echo json_encode([]);
    exit();
}

$menu = [];
while ($row = $result->fetch_assoc()) {
    $id = $row['item_id'];
    
    if (!isset($menu[$id])) {
        $menu[$id] = [
            "item_id" => $row["item_id"],
            "name" => $row["name"],
            "description" => $row["description"],
            "category" => $row["category"],
            "image" => $row["image_path"],
            "variants" => []
        ];
    }

    $menu[$id]["variants"][] = [
        "variant_id" => $row["variant_id"],
        "variant_name" => $row["variant_name"],
        "price" => (float)$row["price"],
        "stock" => (int)$row["stock"],
        "is_popular" => $row["is_popular"]
    ];
}

echo json_encode(array_values($menu));
$conn->close();
?>