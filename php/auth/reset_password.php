<?php
session_start();
include "../toast.php";
require '../db_connect.php';
require_once "../includes/risk_engine.php";


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

//get user type and id by email
// Check landlord first
$stmt = $conn->prepare("SELECT id FROM landlords WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $user_id = $user['id'];
    $user_type = 'landlord';
} else {

    $stmt = $conn->prepare("SELECT id FROM students WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $user_id = $user['id'];
        $user_type = 'student';
    } else {
        die("User not found.");
    }
}

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

    //flag too many password resets
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total 
        FROM password_resets 
        WHERE email=? 
        AND created_at > NOW() - INTERVAL 1 HOUR
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $count = $stmt->get_result()->fetch_assoc()['total'];

    if ($count >= 3) {

                // risk score
    addRisk($conn, $user_type, $user_id, 15);

                    // Prevent duplicate flags within 10 mins
    $existing = $conn->prepare("
        SELECT id FROM spam_flags
        WHERE user_type=?
        AND user_id=?
        AND reason='Multiple password resets from same email'
        AND created_at > NOW() - INTERVAL 10 MINUTE
    ");
    $existing->bind_param("si", $user_type, $user_id);
    $existing->execute();

    if ($existing->get_result()->num_rows == 0) {
        // insert spam flag
        $flag = $conn->prepare("
            INSERT INTO spam_flags (user_type, user_id, reason, severity)
            VALUES (?, ?, 'Multiple password resets from same email', 'high')
        ");
        $flag->bind_param("si", $user_type, $user_id);
        $flag->execute();
    }
}

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
    <link rel="stylesheet" href="../styles.css">
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