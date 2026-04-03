<?php
require_once 'admin_guard.php';
include '../includes/toast.php';

$type = $_GET['type'];
$id = (int) $_GET['id'];
$action = $_GET['action'];

$table = $type === 'landlord' ? 'landlords' : 'students';

if ($action === 'suspend') {
    // Fetch user contact details first
    $userStmt = $conn->prepare("SELECT full_name, email FROM $table WHERE id = ?");
    $userStmt->bind_param('i', $id);
    $userStmt->execute();
    $user = $userStmt->get_result()->fetch_assoc();
    $userStmt->close();

    $stmt = $conn->prepare("UPDATE $table SET status='suspended', risk_score=100 WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    if (!empty($user['email'])) {
        require_once '../includes/mailer.php';
        $subject = "Your H2P account has been suspended";
        $body = "Hi {$user['full_name']},<br><br>" .
            "Your account has been suspended due to policy or safety concerns. " .
            "If you believe this is a mistake, please contact support.<br><br>" .
            "Thanks,<br>H2P Team";

        sendMailQuiet($user['email'], $user['full_name'], $subject, $body);
    }
} elseif ($action === 'activate') {
    $stmt = $conn->prepare("UPDATE $table SET status='active' WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
} elseif ($action === 'verify') {
    $userStmt = $conn->prepare("SELECT full_name, email FROM $table WHERE id = ?");
    $userStmt->bind_param('i', $id);
    $userStmt->execute();
    $user = $userStmt->get_result()->fetch_assoc();
    $userStmt->close();

    $stmt = $conn->prepare("UPDATE $table SET email_verified=1 WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    if (!empty($user['email'])) {
        require_once '../includes/mailer.php';
        $subject = "Your H2P account has been verified";
        $body = "Hi {$user['full_name']},<br><br>" .
            "Your account email has been verified by the admin. You can now log in to your account.<br><br>" .
            "Thanks,<br>H2P Team";

        sendMailQuiet($user['email'], $user['full_name'], $subject, $body);
    }
} else {
    // unknown action: do nothing
}

header("Location: users.php?type=$type");
exit;