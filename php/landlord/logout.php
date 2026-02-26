<?php
session_start();
include "../toast.php";

// Unset all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Optional: prevent back-button access
$_SESSION['toast'] = [
    'type' => 'info',
    'message' => 'Loogged out successfully.'
];
header("Location: login_form.php");
exit;