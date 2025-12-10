<?php
session_start();

$success_url = "admin_dashboard.html";
$failure_url = "admin_login.html?error=invalid";

$conn = new mysqli('localhost', 'root', 'fireandwater', 'campusbite');

if ($conn->connect_error) {
    header("Location: admin_login.html?error=db");
    exit();
}

$adminUser = trim($_POST['adminUser'] ?? '');
$adminPass = trim($_POST['adminPass'] ?? '');

if (empty($adminUser) || empty($adminPass)) {
    header("Location: admin_login.html?error=required");
    exit();
}

$stmt = $conn->prepare("SELECT Admin_id, Password FROM Admin WHERE User_name = ?");

if (!$stmt) {
    header("Location: admin_login.html?error=stmt_failed");
    exit();
}

$stmt->bind_param("s", $adminUser);
$stmt->execute();
$result = $stmt->get_result();

// --- Verify User & Password ---
if ($result && $result->num_rows === 1) {
    $admin = $result->fetch_assoc();

    if (password_verify($adminPass, $admin['Password'])) {
        $_SESSION['admin_id'] = $admin['Admin_id'];
        $_SESSION['admin_user'] = $adminUser;

        $stmt->close();
        $conn->close();

        // Redirect to Dashboard
        header("Location: $success_url");
        exit();
    }
}

$stmt->close();
$conn->close();
header("Location: $failure_url");
exit();
?>