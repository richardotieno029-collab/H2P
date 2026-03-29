<?php
require_once '../includes/config/db_connect.php';
$redirect = $_GET['return'] ?? 'index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['admin_password'] ?? '';

    // verify current admin password
    $stmt = $conn->prepare('SELECT password FROM admins WHERE admin_id = ?');
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $hash = $stmt->get_result()->fetch_assoc()['password'] ?? '';

    if (password_verify($password, $hash)) {
        $_SESSION['admin001_verified'] = true;
        $_SESSION['admin001_verified_at'] = time();

        // If there is a pending action, redirect to execute it.
        if (isset($_SESSION['admin001_pending_action'])) {
            $pending = $_SESSION['admin001_pending_action'];
            $handler = $pending['handler'] ?? '';
            $allowed = [
                'booking_action' => 'booking_action.php?execute=1',
                'toggle_admin' => 'toggle_admin.php?execute=1',
                'delete_admin' => 'delete_admin.php?execute=1',
                'roommate_request_action' => 'roommate_request_action.php?execute=1',
            ];

            if (isset($allowed[$handler])) {
                header('Location: ' . $allowed[$handler]);
                exit;
            }
        }

        header('Location: ' . $redirect);
        exit;
    }

    $_SESSION['toast'] = [
        'type' => 'error',
        'message' => 'Wrong password. Please try again.'
    ];
    header('Location: verify.php?return=' . urlencode($redirect));
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin001 Verification</title>
    <link rel="stylesheet" href="../admin/admin_styles.css">
</head>
<body>
<?php include '../toast.php'; ?>

<div class="admin-wrapper">
    <h2>Admin001 Authentication Required</h2>
    <p>Please enter your password to continue.</p>

    <form method="POST" action="verify.php?return=<?= htmlspecialchars($redirect) ?>" onsubmit="return handleSubmit(this, 'Verifying...')">
        <input type="password" name="admin_password" placeholder="Password" required>
        <button type="submit">Verify</button>
    </form>
</div>

</body>
</html>
