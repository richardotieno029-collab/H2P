<?php
require_once "../session.php";
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'landlord') {
    $_SESSION['toast'] = [
    'type' => 'error',
    'message' => 'You must be logged in to book a room.'
];
    header("Location: ../login_form.php");
    exit;
}