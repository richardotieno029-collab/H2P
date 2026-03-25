<?php
require_once 'admin001_guard.php';
require_once '../includes/config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: houses.php');
    exit;
}

$house_id = intval($_POST['house_id'] ?? 0);
action:
$action = $_POST['action'] ?? '';

if (!$house_id) {
    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Invalid house ID.'];
    header('Location: houses.php');
    exit;
}

$password = $_POST['admin_password'] ?? '';
if (empty($password)) {
    // prompt for password
    ?>
<!DOCTYPE html>
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
    <p>Please enter your password to continue.</p>

    <form method="POST" action="house_action.php" onsubmit="return handleSubmit(this, 'Confirming...')">
        <input type="hidden" name="house_id" value="<?= $house_id ?>">
        <input type="hidden" name="action" value="<?= htmlspecialchars($action) ?>">
        <input type="password" name="admin_password" placeholder="Password" required>
        <button type="submit" class="btn">Confirm</button>
        <a href="houses.php" class="btn">Cancel</a>
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
    header('Location: houses.php');
    exit;
}

if ($action === 'suspend' || $action === 'activate') {
    $status = $action === 'suspend' ? 'suspended' : 'active';
    $update = $conn->prepare('UPDATE houses SET status = ? WHERE house_id = ?');
    $update->bind_param('si', $status, $house_id);
    $update->execute();

    $_SESSION['toast'] = ['type' => 'success', 'message' => "House $status."];
} elseif ($action === 'delete') {
    $delete = $conn->prepare('DELETE FROM houses WHERE house_id = ?');
    $delete->bind_param('i', $house_id);
    $delete->execute();

    $_SESSION['toast'] = ['type' => 'success', 'message' => 'House deleted.'];
} else {
    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Unknown action.'];
}

header('Location: houses.php');
exit;
