<?php
header('Content-Type: application/json');

$pdo = new PDO("mysql:host=localhost;dbname=campusbite", "root", "fireandwater", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

try {
    $stmt = $pdo->query("SELECT Admin_id, User_name, role FROM Admin ORDER BY Admin_id ASC");
    $staff = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "staff" => $staff
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Query failed: " . $e->getMessage()
    ]);
}
?>
