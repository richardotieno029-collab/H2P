<?php
$host = "localhost";
$user = "wjtvksyy_H2P_ADMIN_01";
$pass = "@TopE#12340";
$db   = "wjtvksyy_h2p";
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
date_default_timezone_set('Africa/Nairobi');

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    error_log("$conn->connect_error");
    die("Database connection failed. Please try again later.");
}