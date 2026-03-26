<?php
session_start();

// Keep a toast message but clear all other session data
$_SESSION['toast'] = [
    'type' => 'info',
    'message' => 'Logged out successfully.'
];

$toast = $_SESSION['toast'];

session_unset();
session_regenerate_id(true);

$_SESSION['toast'] = $toast;

header("Location: login_form.php");
exit;