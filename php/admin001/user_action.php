<?php
require_once 'admin001_guard.php';
require_once '../includes/config/db_connect.php';

// Password confirmation is required for any action.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: users.php');
    exit;
}

$action = $_POST['action'] ?? '';
$type = $_POST['type'] ?? 'student';
$id = intval($_POST['id'] ?? 0);

if (!$id || !in_array($type, ['student', 'landlord'], true)) {
    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Invalid request.'];
    header('Location: users.php?type=' . urlencode($type));
    exit;
}

// verify admin password
$password = $_POST['admin_password'] ?? '';

if (empty($password)) {
    // show password prompt for this action
    ?><!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Admin001 Action</title>
    <link rel="stylesheet" href="../admin/admin_styles.css">
</head>
<body>
<?php include '../toast.php'; ?>

<div class="admin-wrapper">
    <h2>Confirm Action</h2>
    <p>Please enter your password to confirm the requested action.</p>

    <form method="POST" action="user_action.php" onsubmit="return handleSubmit(this, 'Confirming...')">
        <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
        <input type="hidden" name="id" value="<?= $id ?>">
        <input type="hidden" name="action" value="<?= htmlspecialchars($action) ?>">
        <input type="password" name="admin_password" placeholder="Password" required>
        <button type="submit" class="btn">Confirm</button>
        <a href="users.php?type=<?= htmlspecialchars($type) ?>" class="btn">Cancel</a>
    </form>
</div>

</body>
</html>
<?php
    exit;
}

$stmt = $conn->prepare('SELECT password FROM admins WHERE admin_id = ?');
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$hash = $stmt->get_result()->fetch_assoc()['password'] ?? '';

if (!password_verify($password, $hash)) {
    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Password incorrect.'];
    header('Location: users.php?type=' . urlencode($type));
    exit;
}

// perform action
$table = $type === 'landlord' ? 'landlords' : 'students';

if ($action === 'suspend' || $action === 'activate') {
    $status = $action === 'suspend' ? 'suspended' : 'active';

    // Send suspension notice when suspending
    $userStmt = $conn->prepare("SELECT full_name, email FROM $table WHERE id = ?");
    $userStmt->bind_param('i', $id);
    $userStmt->execute();
    $user = $userStmt->get_result()->fetch_assoc();
    $userStmt->close();

    $update = $conn->prepare("UPDATE $table SET status = ? WHERE id = ?");
    $update->bind_param('si', $status, $id);
    $update->execute();

    if ($status === 'suspended' && !empty($user['email'])) {
        require_once '../includes/mailer.php';
        $subject = "Your H2P account has been suspended";
        $body = "Hi {$user['full_name']},<br><br>" .
            "Your account has been suspended. If you believe this was done in error, please contact support.<br><br>" .
            "Thanks,<br>H2P Team";

        sendMailQuiet($user['email'], $user['full_name'], $subject, $body);
    }

    $_SESSION['toast'] = ['type' => 'success', 'message' => ucfirst($type) . " $status."];
} elseif ($action === 'delete') {
    $delete = $conn->prepare("DELETE FROM $table WHERE id = ?");
    $delete->bind_param('i', $id);
    $delete->execute();

    $_SESSION['toast'] = ['type' => 'success', 'message' => ucfirst($type) . " deleted."];
} else {
    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Unknown action.'];
}

header('Location: users.php?type=' . urlencode($type));
exit;
