<?php
require_once _DIR_ . '/../session.php';

if (!isLoggedIn() || userRole() !== 'student') {
    header("Location: /frontend/login.html");
    exit();
}