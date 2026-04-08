<?php
require_once __DIR__ . '/../includes/config/session.php';
require_once __DIR__ . '/../includes/config/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['toast'] = [
        'type' => 'error',
        'message' => 'You must be logged in as an admin to access this area.'
    ];
    header('Location: ../student/login_form.php');
    exit;
}

// Only the highest-level admin is allowed here.
// This is identified by name and email.
$superName  = 'H2P_ADMIN_1';
$superEmail = 'admin@h2p.co.ke';

$stmt = $conn->prepare('SELECT * FROM admins WHERE admin_id = ?');
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();

if (!$admin || strtolower($admin['email']) !== strtolower($superEmail) || trim($admin['name']) !== $superName) {
    $_SESSION['toast'] = [
        'type' => 'error',
        'message' => 'Access denied. Super admin privileges required.'
    ];
    header('Location: ../admin/dashboard.php');
    exit;
}

// Ensure admin status is active
if (isset($admin['status']) && $admin['status'] === 'suspended') {
    session_destroy();
    session_start();
    $_SESSION['toast'] = [
        'type' => 'error',
        'message' => 'Your admin account is suspended.'
    ];
header('Location: ../student/login_form.php');
    exit;
}

function requireAdmin001Password() {
    if (!isset($_SESSION['admin001_verified']) || $_SESSION['admin001_verified'] !== true) {
        header('Location: verify.php?return=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }

    // Expire after 10 minutes
    if (isset($_SESSION['admin001_verified_at']) && (time() - $_SESSION['admin001_verified_at']) > 600) {
        unset($_SESSION['admin001_verified'], $_SESSION['admin001_verified_at']);
        header('Location: verify.php?return=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}
