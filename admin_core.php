<?php
session_start();
header('Content-Type: application/json');
$conn = new mysqli("localhost", "root", "fireandwater", "campusbite");
if ($conn->connect_error) { echo json_encode(["success" => false, "message" => "DB Error"]); exit(); }

$action = $_GET['action'] ?? '';

// 1. DASHBOARD STATS
if ($action === 'get_dashboard_stats') {
    // REVENUE: Sum ALL orders ever (so you see the ₱5380.00)
    $rev = $conn->query("SELECT SUM(total_amount) FROM orders WHERE status IN ('Confirmed', 'Completed')")->fetch_row()[0] ?? 0;
    
    // RESERVATIONS: Count ALL upcoming/pending reservations
    $res = $conn->query("SELECT COUNT(*) FROM reservation WHERE status IN ('Pending', 'Confirmed')")->fetch_row()[0] ?? 0;
    
    // USERS: Count Total Users
    $users = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0] ?? 0;
    
    echo json_encode(["success" => true, "revenue" => $rev, "reservations" => $res, "users" => $users]);
    exit();
}

// 2. USERS & STAFF
if ($action === 'get_users') {
    $result = $conn->query("SELECT full_name, email, course FROM users LIMIT 20");
    $users = []; while($r = $result->fetch_assoc()) $users[] = $r;
    echo json_encode(["success" => true, "users" => $users]); exit();
}
if ($action === 'get_staff') {
    $result = $conn->query("SELECT User_name, role FROM Admin");
    $staff = []; while($r = $result->fetch_assoc()) $staff[] = $r;
    echo json_encode(["success" => true, "staff" => $staff]); exit();
}

// 3. LOGS
if ($action === 'get_logs') {
    $result = $conn->query("SELECT * FROM system_logs ORDER BY log_date DESC LIMIT 20");
    $logs = []; while($r = $result->fetch_assoc()) $logs[] = $r;
    echo json_encode(["success" => true, "logs" => $logs]); exit();
}

// 4. SALES CHART ANALYTICS (The New Logic)
if ($action === 'get_sales_chart') {
    $period = $_GET['period'] ?? 'week';
    $sql = "";

    switch ($period) {
        case 'today':
            $sql = "SELECT DATE_FORMAT(order_date_time, '%H:00') as label, SUM(total_amount) as total FROM orders WHERE DATE(order_date_time) = CURDATE() AND status='Completed' GROUP BY HOUR(order_date_time)";
            break;
        case 'week':
            $sql = "SELECT DATE_FORMAT(order_date_time, '%a') as label, SUM(total_amount) as total FROM orders WHERE YEARWEEK(order_date_time, 1) = YEARWEEK(CURDATE(), 1) AND status='Completed' GROUP BY DATE(order_date_time)";
            break;
        case 'month':
            $sql = "SELECT DATE_FORMAT(order_date_time, '%d') as label, SUM(total_amount) as total FROM orders WHERE MONTH(order_date_time) = MONTH(CURDATE()) AND status='Completed' GROUP BY DATE(order_date_time)";
            break;
        case 'year':
            $sql = "SELECT DATE_FORMAT(order_date_time, '%b') as label, SUM(total_amount) as total FROM orders WHERE YEAR(order_date_time) = YEAR(CURDATE()) AND status='Completed' GROUP BY MONTH(order_date_time)";
            break;
    }

    $result = $conn->query($sql);
    $labels = []; $data = [];
    while($row = $result->fetch_assoc()) { $labels[] = $row['label']; $data[] = $row['total']; }
    echo json_encode(["success" => true, "labels" => $labels, "data" => $data]);
    exit();
}
?>