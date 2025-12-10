<?php
$password = "2025cBite"; 
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "<h1>Copy this Hash:</h1>";
echo "<h3>" . $hash . "</h3>";
?>