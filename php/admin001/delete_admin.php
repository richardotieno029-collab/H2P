<?php
require_once 'admin001_guard.php';
require_once '../includes/config/db_connect.php';
require_once '../includes/toast.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$password = $_POST['password'] ?? null;

// Support return from verification step via session stored pending action.
if (isset($_GET['execute']) && $_GET['execute'] === '1' && isset($_SESSION['admin001_pending_action'])) {
    $pending = $_SESSION['admin001_pending_action'];
    if (($pending['handler'] ?? '') === 'delete_admin') {
        $id = $pending['id'] ?? $id;
        $password = $_SESSION['admin_password_hash'] ?? '';
    }
}

if (!$id) {
    set_toast('Invalid request.', 'error');
    header('Location: manage_admins.php');
    exit;
}

// Protect the super admin from being deleted.
$stmt = $conn->prepare('SELECT email FROM admins WHERE admin_id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$email = $stmt->get_result()->fetch_assoc()['email'] ?? '';
$stmt->close();

if (strtolower($email) === 'admin@h2p.co.ke') {
    set_toast('Cannot delete the super admin account.', 'error');
    header('Location: manage_admins.php');
    exit;
}

// If password isn't provided, redirect to password verification.
if (!$password) {
    $_SESSION['admin001_pending_action'] = [
        'handler' => 'delete_admin',
        'id' => $id,
    ];
    header('Location: verify.php?return=delete_admin.php');
    exit;
}

// Validate password
$hash = $_SESSION['admin_password_hash'] ?? '';
if (!$hash && isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare('SELECT password FROM admins WHERE admin_id = ?');
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $hash = $stmt->get_result()->fetch_assoc()['password'] ?? '';
    $stmt->close();
}

if (!password_verify($password, $hash)) {
    set_toast('Incorrect password. Action cancelled.', 'error');
    unset($_SESSION['admin001_pending_action']);
    header('Location: manage_admins.php');
    exit;
}

unset($_SESSION['admin001_pending_action']);

$stmt = $conn->prepare('DELETE FROM admins WHERE admin_id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->close();

set_toast('Admin account deleted.', 'success');
header('Location: manage_admins.php');
exit;
