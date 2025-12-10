<?php
header('Content-Type: application/json');
require 'db.php';

// DEFAULTS (Solves "undefined")
$response = ["success" => true, "is_open" => 1, "open_time" => "08:00", "close_time" => "17:00"]; 

// 1. Admin Override
$res = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'SYSTEM_STATUS'");
if ($res && $row = $res->fetch_assoc()) {
    if (strtoupper($row['setting_value']) === 'CLOSED') {
        $response["is_open"] = 0;
    }
}

// 2. Schedule
$day = date("D");
$res = $conn->query("SELECT open_time, close_time, is_open FROM shop_hours WHERE day_of_week='$day'");
if ($res && $hours = $res->fetch_assoc()) {
    $response["open_time"] = date("H:i", strtotime($hours['open_time']));
    $response["close_time"] = date("H:i", strtotime($hours['close_time']));
}

echo json_encode($response);
?>