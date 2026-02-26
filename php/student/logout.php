<?php
session_start();
include "../toast.php";
session_destroy();
$_SESSION['toast'] = [
    'type' => 'success',
    'message' => 'You have been logged out successfully.'
];
header("Location: login_form.php");
?>