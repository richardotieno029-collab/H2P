<?php
session_start();
include "../toast.php";
require_once '../session.php';
require_once '../db_connect.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    die("Unauthorized access.");
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

// Determine correct table + ID column
$table = ($user_role === 'landlord') ? 'landlords' : 'students';
$id_col = ($user_role === 'landlord') ? 'id' : 'id';

$message = "";

// Generate CSRF token if not set
if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF Protection
    if (
        !isset($_POST['token']) ||
        !hash_equals($_SESSION['token'], $_POST['token'])
    ) {
        die("Invalid request.");
    }

    $old = trim($_POST['old_password']);
    $new = trim($_POST['new_password']);
    $confirm = trim($_POST['confirm_password']);

    // 1️⃣ Check password match
    if ($new !== $confirm) {
        $_SESSION['toast'] = [
            'type' => 'error',
            'message' => 'New passwords do not match.'
        ];
    }

    // 2️⃣ Strong password validation
    elseif (
        strlen($new) < 8 ||
        !preg_match('/[A-Za-z]/', $new) ||
        !preg_match('/[0-9]/', $new)
    ) {
        $_SESSION['toast'] = [
            'type' => 'error',
            'message' => 'Password must be at least 8 characters and contain letters and numbers.'
        ];
    }

    else {
        // Fetch current password
        $stmt = $conn->prepare("SELECT password FROM $table WHERE $id_col = ?");
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if (!$result || !password_verify($old, $result['password'])) {

            $_SESSION['toast'] = [
                'type' => 'error',
                'message' => 'Old password is incorrect.'
            ];

        } 
        // 3️⃣ Prevent reusing old password
        elseif (password_verify($new, $result['password'])) {

            $_SESSION['toast'] = [
                'type' => 'error',
                'message' => 'You cannot reuse your old password.'
            ];

        } 
        else {
            // Hash new password
            $hashed = password_hash($new, PASSWORD_DEFAULT);

            $update = $conn->prepare("UPDATE $table SET password = ? WHERE $id_col = ?");
            $update->bind_param("ss", $hashed, $user_id);
            $update->execute();

            // Regenerate token
            $_SESSION['token'] = bin2hex(random_bytes(32));

            $_SESSION['toast'] = [
                'type' => 'success',
                'message' => 'Password updated successfully.'
            ];

            require_once 'redirect.php';
            redirectToDashboard();
            exit;
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
<?php include "../toast.php"; ?>

<div class="auth-container">

<h2>⚠️ Change Password</h2>
<p class="warning">This action affects your account security.</p>

<form method="POST">
    <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">

    <input type="password" name="old_password" placeholder="Old Password" required>

    <input type="password" name="new_password" 
           placeholder="New Password (min 8 chars, letters & numbers)" required>

    <input type="password" name="confirm_password" 
           placeholder="Confirm New Password" required>

    <button type="submit">Update Password</button>
</form>

<a href="javascript:history.back()" class="back-btn">
    ← Back
</a>

</div>
</body>
</html>