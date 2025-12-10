<?php
// db.php
error_reporting(0); 
header('Access-Control-Allow-Origin: *');

$host = "localhost";
$user = "root";
$pass = "fireandwater"; 
$name = "campusbite";

$conn = new mysqli($host, $user, $pass, $name);

if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "DB Error"]));
}
?>