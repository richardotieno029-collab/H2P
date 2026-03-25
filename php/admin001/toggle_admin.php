<?php
require_once 'admin001_guard.php';
require_once '../includes/config/db_connect.php';
require_once '../toast.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = $_GET['action'] ?? null;
$password = $_POST['password'] ?? null;

// Support return from verification step via session stored pending action.
if (isset($_GET['execute']) && $_GET['execute'] === '1' && isset($_SESSION['admin001_pending_action'])) {
    $pending = $_SESSION['admin001_pending_action'];
    if (($pending['handler'] ?? '') === 'toggle_admin') {
        $id = $pending['id'] ?? $id;
        $action = $pending['action'] ?? $action;
        $password = $_SESSION['admin_password_hash'] ?? '';
    }
}

if (!$id || !in_array($action, ['suspend', 'activate'])) {
    set_toast('Invalid request.', 'error');
    header('Location: manage_admins.php');
    exit;
}

// Protect the super admin from being suspended.
$stmt = $conn->prepare('SELECT email FROM admins WHERE admin_id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$email = $stmt->get_result()->fetch_assoc()['email'] ?? '';
$stmt->close();

if (strtolower($email) === 'admin@h2p.co.ke') {
    set_toast('Cannot modify super admin.', 'error');
    header('Location: manage_admins.php');
    exit;
}

// If password isn't provided, redirect to password verification.
if (!$password) {
    $_SESSION['admin001_pending_action'] = [
        'handler' => 'toggle_admin',
        'id' => $id,
        'action' => $action,
    ];
    header('Location: verify.php?return=toggle_admin.php');
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

$newStatus = $action === 'suspend' ? 'suspended' : 'active';
$stmt = $conn->prepare('UPDATE admins SET status = ? WHERE admin_id = ?');
$stmt->bind_param('si', $newStatus, $id);
$stmt->execute();
$stmt->close();

set_toast('Admin status updated.', 'success');
header('Location: manage_admins.php');
exit;
