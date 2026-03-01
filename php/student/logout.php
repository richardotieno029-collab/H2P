<?php
session_start();
include "../toast.php";
session_destroy();
$_SESSION['toast'] = [
    'type' => 'success',
    'message' => 'Log out successfull.Login to continue browsing.'
];
header("Location: login_form.php");
?>