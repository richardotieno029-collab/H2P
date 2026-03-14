<?php
session_start();
include "../toast.php";
session_destroy();
$_SESSION['toast'] = [
    'type' => 'info',
    'message' => 'You have been logged out. Please login to continue browsing.'
];
header("Location: login_form.php");
?>