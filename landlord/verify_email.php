<?php
require_once "../includes/config/db_connect.php";

if (!isset($_GET['token'])) {
    die("Invalid verification link.");
}

$token = $_GET['token'];

$stmt = $conn->prepare("
SELECT id FROM landlords
WHERE verification_token=?
AND email_verified=0
AND token_expires > NOW()
");

$stmt->bind_param("s",$token);
$stmt->execute();
$result = $stmt->get_result();

    if ($result->num_rows === 0) {
    toast('error', 'Invalid or expired verification link.');
    header('Location: verify_notice.php');
    exit;
}

$landlord = $result->fetch_assoc();
$landlord_id = $landlord['id'];

$update = $conn->prepare("
UPDATE landlords
SET email_verified = 1,
verification_token = NULL,
token_expires = NULL
WHERE id = ?
");

$update->bind_param("i", $student_id);
$update->execute();

        $_SESSION['toast'] = [
            'type' => 'success',
            'message' => 'Email verification successful. Please log in'
        ];

        header("Location: login_form.php");
        exit;