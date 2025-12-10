<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "root", "fireandwater", "campusbite");

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// --- LIST ITEMS ---
if ($action === '') { 
    $sql = "SELECT f.*, v.price, v.stock, v.variant_id FROM food_item f JOIN item_variant v ON f.item_id = v.item_id GROUP BY f.item_id"; 
    $res = $conn->query($sql);
    $items = [];
    while($r = $res->fetch_assoc()) {
        $r['variants'] = [['price' => $r['price'], 'stock' => $r['stock']]];
        $r['image'] = $r['image_path'];
        $items[] = $r;
    }
    echo json_encode($items);
    exit();
}

// --- ADD ITEM (Logs Fixed) ---
if ($action === 'add_item') {
    $name = $_POST['name'];
    $cat = $_POST['category'];
    $price = $_POST['price'];
    $stock = $_POST['stock']; 

    $imagePath = 'images/default.jpg';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        if (!is_dir('images')) mkdir('images');
        $fileName = time() . '_' . basename($_FILES['image']['name']);
        if (move_uploaded_file($_FILES['image']['tmp_name'], 'images/' . $fileName)) {
            $imagePath = 'images/' . $fileName;
        }
    }

    $stmt = $conn->prepare("INSERT INTO food_item (name, category, description, image_path) VALUES (?, ?, 'Desc', ?)");
    $stmt->bind_param("sss", $name, $cat, $imagePath);
    
    if ($stmt->execute()) {
        $item_id = $stmt->insert_id;
        $conn->query("INSERT INTO item_variant (item_id, variant_name, price, stock) VALUES ($item_id, 'Regular', $price, $stock)");
        
        // --- LOGGING ---
        $log_desc = "Added item: " . $name;
        $conn->query("INSERT INTO system_logs (user_type, action, description) VALUES ('Admin', 'ADD_MENU', '$log_desc')");
        
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => $conn->error]);
    }
    exit();
}

// --- DELETE ITEM (Logs Fixed) ---
if ($action === 'delete_item') {
    $id = $_GET['item_id'];
    $conn->query("DELETE FROM item_variant WHERE item_id=$id");
    $conn->query("DELETE FROM food_item WHERE item_id=$id");
    
    // --- LOGGING ---
    $conn->query("INSERT INTO system_logs (user_type, action, description) VALUES ('Admin', 'DELETE_MENU', 'Deleted Item ID: $id')");

    echo json_encode(["success" => true]);
    exit();
}
?>