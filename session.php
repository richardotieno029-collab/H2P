<?php
// Start session only once
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check user role
function userRole() {
    return $_SESSION['role'] ?? null;
}

// Force login
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: /frontend/login.html");
        exit();
    }
}
require_once "db_connect.php";

$sql = "SELECT * FROM houses WHERE landlord_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $landlord_id);
$stmt->execute();
$result = $stmt->get_result();
?>