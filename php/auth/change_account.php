<?php
require_once '../session.php';

// Destroy session
session_unset();
session_destroy();

// Redirect to index
header("Location: ../index/index.php");
exit;