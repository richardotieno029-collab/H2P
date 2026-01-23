<?php
// redirect.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Redirect user to the correct dashboard based on role
 */
function redirectToDashboard()
{
    if (!isset($_SESSION['user_role'])) {
        header("Location: ../index/index.php");
        exit;
    }

    if ($_SESSION['user_role'] === 'landlord') {
        header("Location: ../landlord/landlord_dashboard.php");
        exit;
    }

    if ($_SESSION['user_role'] === 'student') {
        header("Location: ../student/dashboard.php");
        exit;
    }

    // fallback (safety net)
    header("Location: ../index/index.php");
    exit;
}