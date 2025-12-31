<?php
require_once __DIR__. "/../session.php";
require_once __DIR__. "/../db_connect.php";

if (!isset($_POST['email'], $_POST['password'])) {
    header("Location: ../../public/landlord/landlord_login.html");
    exit;
}

$email = trim($_POST['email']);
$password = trim($_POST['password']);

$stmt = $conn->prepare(
    "SELECT landlord_id, password FROM landlords WHERE email = ?"
);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $landlord = $result->fetch_assoc();

    if (password_verify($password, $landlord['password'])) {
        $_SESSION['landlord_id'] = $landlord['landlord_id'];

        // 🔑 VERY IMPORTANT
        header("Location: landlord_dashboard.php");
        exit;
    }
}

// login failed
header("Location: ../../public/landlord/landlord_login.html?error=1");
exit;