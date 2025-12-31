<?php
require_once _DIR_ . '/../session.php';

if (!isLoggedIn() || userRole() !== 'landlord') {
    header("Location: /frontend/login.html");
    exit();
}