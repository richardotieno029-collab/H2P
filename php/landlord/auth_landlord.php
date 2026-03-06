<?php
require_once "../db_connect.php";
require_once "../auth/check_suspension.php";
require_once "../session.php";
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'landlord') {
    $_SESSION['toast'] = [
    'type' => 'error',
    'message' => 'You must be logged in to access this page.'
];
    header("Location: login_form.php");
    exit;
}
checkAccountStatus($conn);