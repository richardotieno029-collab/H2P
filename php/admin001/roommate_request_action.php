<?php
require_once 'admin001_guard.php';
require_once '../includes/config/db_connect.php';
require_once '../includes/toast.php';

$action = $_POST['action'] ?? null;
$requestId = isset($_POST['request_id']) ? (int)$_POST['request_id'] : 0;
$password = $_POST['admin_password'] ?? null;

// Support return from verification step via session stored pending action.
if (isset($_GET['execute']) && $_GET['execute'] === '1' && isset($_SESSION['admin001_pending_action'])) {
    $pending = $_SESSION['admin001_pending_action'];
    if (($pending['handler'] ?? '') === 'roommate_request_action') {
        $action = $pending['action'] ?? $action;
        $requestId = $pending['request_id'] ?? $requestId;
        $password = $_SESSION['admin_password_hash'] ?? '';
    }
}

if (!$requestId || !in_array($action, ['approve', 'reject'], true)) {
    set_toast('Invalid request.', 'error');
    header('Location: roommate_requests.php');
    exit;
}

// If password isn't provided, require verification
if (!$password) {
    $_SESSION['admin001_pending_action'] = [
        'handler' => 'roommate_request_action',
        'request_id' => $requestId,
        'action' => $action,
    ];
    header('Location: verify.php?return=roommate_request_action.php');
    exit;
}

// Validate password (fallback to DB lookup)
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
    header('Location: roommate_requests.php');
    exit;
}

unset($_SESSION['admin001_pending_action']);

if ($action === 'approve') {
    // Approve this request
    $stmt = $conn->prepare('UPDATE roommate_requests SET status = ? WHERE request_id = ?');
    $status = 'APPROVED';
    $stmt->bind_param('si', $status, $requestId);
    $stmt->execute();
    $stmt->close();

    // Reject other requests for this host
    $stmt = $conn->prepare('SELECT host_id FROM roommate_requests WHERE request_id = ?');
    $stmt->bind_param('i', $requestId);
    $stmt->execute();
    $hostId = $stmt->get_result()->fetch_assoc()['host_id'] ?? null;
    $stmt->close();

    if ($hostId) {
        $stmt = $conn->prepare('UPDATE roommate_requests SET status = ? WHERE host_id = ? AND request_id != ?');
        $rej = 'REJECTED';
        $stmt->bind_param('sii', $rej, $hostId, $requestId);
        $stmt->execute();
        $stmt->close();

        // Close host listing
        $stmt = $conn->prepare('UPDATE roommate_hosts SET status = ? WHERE host_id = ?');
        $closed = 'CLOSED';
        $stmt->bind_param('si', $closed, $hostId);
        $stmt->execute();
        $stmt->close();
    }

    set_toast('Request approved.', 'success');
} else {
    // Reject request
    $stmt = $conn->prepare('UPDATE roommate_requests SET status = ? WHERE request_id = ?');
    $status = 'REJECTED';
    $stmt->bind_param('si', $status, $requestId);
    $stmt->execute();
    $stmt->close();

    set_toast('Request rejected.', 'success');
}

header('Location: roommate_requests.php');
exit;
