<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "root", "fireandwater", "campusbite");
if($conn->connect_error){ echo json_encode(["success"=>false, "message"=>"DB Error"]); exit(); }

$Admin_id = intval($_GET['Admin_id'] ?? 0);
if($Admin_id === 0){ echo json_encode(["success"=>false,"message"=>"Invalid ID"]); exit(); }

if($conn->query("DELETE FROM Admin WHERE Admin_id=$Admin_id")){
    echo json_encode(["success"=>true]);
}else{
    echo json_encode(["success"=>false,"message"=>"Failed to delete staff"]);
}

$conn->close();
?>
