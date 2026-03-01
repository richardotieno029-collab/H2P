<?php
session_start();
include "../toast.php";
require '../db_connect.php';

if (!isset($_GET['token'])) {
    die("Invalid request.");
}

$token = $_GET['token'];

$stmt = $conn->prepare("SELECT * FROM password_resets 
                        WHERE token=? 
                        AND expires_at > NOW() 
                        AND used=0");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Invalid or expired token.");
}

$row = $result->fetch_assoc();
$email = $row['email'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $newPassword = $_POST['password'];
    $hashed = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update students
    $stmt = $conn->prepare("UPDATE students SET password=? WHERE email=?");
    $stmt->bind_param("ss", $hashed, $email);
    $stmt->execute();

    if ($stmt->affected_rows == 0) {
        // Try landlords
        $stmt = $conn->prepare("UPDATE landlords SET password=? WHERE email=?");
        $stmt->bind_param("ss", $hashed, $email);
        $stmt->execute();
    }

    // Mark token used
    $stmt = $conn->prepare("UPDATE password_resets SET used=1 WHERE token=?");
    $stmt->bind_param("s", $token);
    $stmt->execute();

    $_SESSION['toast'] = [
        'type' => 'success',
        'message' => 'Password reset successful. You can now log in with your new password.'
    ];
    header("Location: ../index/index.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <link rel="stylesheet" href="../auth_styles.css">
</head>
<body class="auth-page">
<?php include "../toast.php"; ?>
<div class="auth-container">
    <h2>Reset Password</h2>
    <form method="POST">
        <input type="password" name="password" required placeholder="New Password">
        <button type="submit">Reset Password</button>
    </form>
</div>
</body>
</html>