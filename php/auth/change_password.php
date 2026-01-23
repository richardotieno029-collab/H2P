<?php
require_once '../session.php';
require_once '../db_connect.php';

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

$table = ($user_role === 'landlord') ? 'landlords' : 'students';
$id_col = ($user_role === 'landlord') ? 'landlord_id' : 'student_id';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = $_POST['old_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if ($new !== $confirm) {
        $message = "❌ New passwords do not match.";
    } else {
        $stmt = $conn->prepare("SELECT password FROM $table WHERE $id_col = ?");
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if (!$result || !password_verify($old, $result['password'])) {
            $message = "❌ Old password is incorrect.";
        } else {
            $hashed = password_hash($new, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE $table SET password = ? WHERE $id_col = ?");
            $update->bind_param("ss", $hashed, $user_id);
            $update->execute();

            $message = "✅ Password changed successfully.";
            require_once '../auth/redirect.php';
            redirectToDashboard();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Password</title>
    <link rel="stylesheet" href="../auth_styles.css">
</head>

<body class="auth-page">
    <div class="auth-container">

<h2>⚠️ Change Password</h2>
<p class="warning">This action affects your account security.</p>

<?php if ($message): ?>
<p class="alert"><?= $message ?></p>
<?php endif; ?>

<form method="POST">
    <input type="password" name="old_password" placeholder="Old Password" required>
    <input type="password" name="new_password" placeholder="New Password" required>
    <input type="password" name="confirm_password" placeholder="Confirm New Password" required>

    <button type="submit">Update Password</button>
</form>

 <a href="javascript:history.back()" class="back-btn" title="Go back">
    ← Back
</a>
    </div>

</body>
</html>