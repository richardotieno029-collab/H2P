<?php
session_start();
include "../includes/toast.php";
require_once '../includes/config/session.php';
require_once '../includes/config/db_connect.php';
include "../toast.php";

$user_id = $_SESSION['user_id'];
$role = $_SESSION['user_role'];

$table = ($role === 'landlord') ? 'landlords' : 'students';
$id_col = ($role === 'landlord') ? 'id' : 'id';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['token']) || 
        !isset($_SESSION['token']) || 
        !hash_equals($_SESSION['token'], $_POST['token'])) {

        die("Invalid request.");
    }
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT password FROM $table WHERE $id_col = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user || !password_verify($password, $user['password'])) {
         $_SESSION['toast'] = [
            'type' => 'error',
            'message' => 'Password is incorrect.'
        ];
    } else {
        $del = $conn->prepare("DELETE FROM $table WHERE $id_col = ?");
        $del->bind_param("s", $user_id);
        $del->execute();

        session_unset();
        session_destroy();
$_SESSION['token'] = bin2hex(random_bytes(32));
 $_SESSION['toast'] = [
            'type' => 'info',
            'message' => 'Account deleted successfully.'
        ];
        header("Location: ../index/index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Account</title>
    <link rel="stylesheet" href="../includes/assets/css/styles.css">
</head>
<body class="auth-page">  
    <?php include "../toast.php"; ?>
    <div class="auth-container danger">

<h2>☠️ Delete Account</h2>

<p class="warning">
This action is <strong>PERMANENT</strong>.<br>
All your data will be lost.
</p>

<?php if ($message): ?>
<p class="alert"><?= $message ?></p>
<?php endif; ?>

<form method="POST" onsubmit="return handleSubmit(this, 'Deleting account...')">
    <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
    <input type="password" name="password" placeholder="Confirm your password" required>
    <button class="danger-btn" type="submit">Delete My Account</button>
</form>

 <a href="javascript:history.back()" class="back-btn" title="Go back">
cancel
</a>
</div>
</body>
</html>