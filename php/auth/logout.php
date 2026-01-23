<?php
session_start();

// Unset all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Optional: prevent back-button access
header("Location: ../index/index.php");
exit;