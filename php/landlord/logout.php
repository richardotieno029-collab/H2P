<?php
session_start();
include "../toast.php";

// Unset all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

$_SESSION['toast'] = [
    'type' => 'info',
    'message' => 'Logged out successfully.'
];
header("Location: login_form.php");
exit;