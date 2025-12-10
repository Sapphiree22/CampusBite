<?php
header('Content-Type: application/json');

$host = 'localhost';
$db   = 'campusbite';
$user = 'root';
$pass = 'fireandwater';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]);
    exit;
}

try {
    $stmt = $pdo->query("
        SELECT 
            timestamp AS log_date,
            user AS user_type,
            action,
            details AS description
        FROM logs
        ORDER BY timestamp DESC
    ");

    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'logs' => $logs
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Query failed: ' . $e->getMessage()
    ]);
}
?>
