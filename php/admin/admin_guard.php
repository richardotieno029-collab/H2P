<?php
require_once "../session.php";
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin'){
    $_SESSION['toast'] = [
    'type' => 'error',
    'message' => 'For that you need to be logged in as an admin.'
];
    header("Location: login_form.php");
    exit;

}