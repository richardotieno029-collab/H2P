<?php
$host = "localhost";
$user = "your_db_user";
$pass = "your_db_password";
$db   = "your_db_name";
ini_set('display_errors', 0);
ini_set('log_errors', 1);
date_default_timezone_set('Africa/Nairobi');

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    error_log("$conn->connect_error");
    die("Database connection failed. Please try again later.");
       
}