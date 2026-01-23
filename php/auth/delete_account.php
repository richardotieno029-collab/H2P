<?php
require_once '../session.php';
require_once '../db_connect.php';

$user_id = $_SESSION['user_id'];
$role = $_SESSION['user_role'];

$table = ($role === 'landlord') ? 'landlords' : 'students';
$id_col = ($role === 'landlord') ? 'landlord_id' : 'student_id';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT password FROM $table WHERE $id_col = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user || !password_verify($password, $user['password'])) {
        $message = "❌ Password incorrect.";
    } else {
        $del = $conn->prepare("DELETE FROM $table WHERE $id_col = ?");
        $del->bind_param("s", $user_id);
        $del->execute();

        session_unset();
        session_destroy();

        header("Location: ../index/index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Delete Account</title>
    <link rel="stylesheet" href="../auth_styles.css">
</head>
<body class="auth-page">    
    <div class="auth-container danger">

<h2>☠️ Delete Account</h2>

<p class="warning">
This action is <strong>PERMANENT</strong>.<br>
All your data will be lost.
</p>

<?php if ($message): ?>
<p class="alert"><?= $message ?></p>
<?php endif; ?>

<form method="POST">
    <input type="password" name="password" placeholder="Confirm your password" required>
    <button class="danger-btn" type="submit">Delete My Account</button>
</form>

 <a href="javascript:history.back()" class="back-btn" title="Go back">
    ← cancel
</a>
</div>
</body>
</html>