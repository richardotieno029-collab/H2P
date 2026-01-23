<?php
session_start();
require_once '../db_connect.php';

$student_id = $_POST['student_id'];
$name = $_POST['full_name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

// Check duplicate
$check = $conn->prepare("SELECT student_id FROM students WHERE student_id = ? OR email = ?");
$check->bind_param("ss", $student_id, $email);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    $_SESSION['error'] = "Student already exists.";
    header("Location: signup_form.php");
    exit();
}

$stmt = $conn->prepare("
    INSERT INTO students (student_id, full_name, email, phone, password)
    VALUES (?, ?, ?, ?, ?)
");
$stmt->bind_param("sssss", $student_id, $name, $email, $phone, $password);

if ($stmt->execute()) {
    $_SESSION['success'] = "Signup successful. Please login.";
    header("Location: login_form.php");
} else {
    $_SESSION['error'] = "Signup failed.";
    header("Location: signup_form.php");
}