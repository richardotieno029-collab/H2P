<?php
session_start();
include "../toast.php";
require_once "../db_connect.php";

if (!isset($_GET['token'])) {
die("Invalid verification link.");
}

$token = $_GET['token'];

$stmt = $conn->prepare("
SELECT id FROM students
WHERE verification_token=?
AND email_verified=0
AND token_expires_at > NOW()
");

$stmt->bind_param("s",$token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    toast('error', 'Invalid or expired verification link.');
    header('Location: verify_notice.php');
    exit;
}

$student = $result->fetch_assoc();
$student_id = $student['id'];

$update = $conn->prepare("
UPDATE students
SET email_verified = 1,
verification_token = NULL,
token_expires_at = NULL
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