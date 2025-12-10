<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] <= 0) {
    // header("Location: index.html"); // Redirect to login page
    // exit();
}

$signed_in_user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>CampusBite | Dashboard</title>

    <script>
        // Set the global JavaScript variable using the PHP session data
        let CURRENT_USER_ID = <?php echo $signed_in_user_id; ?>;
        
        // Console check for debugging
        console.log("Dynamically set CURRENT_USER_ID:", CURRENT_USER_ID);
    </script>
    
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>