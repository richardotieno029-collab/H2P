<?php

function checkAccountStatus($conn) {

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
        return;
    }

    $user_id   = $_SESSION['user_id'];
    $user_type = $_SESSION['user_role'];

    // determine table
    if ($user_type === 'landlord') {
        $table = 'landlords';
    } elseif ($user_type === 'student') {
        $table = 'students';
    } elseif ($user_type === 'admin') {
        $table = 'admins';
    } else {
        return;
    }

    $stmt = $conn->prepare("SELECT status FROM $table WHERE id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $status = $stmt->get_result()->fetch_assoc()['status'];

    if ($status === 'suspended') {

        session_destroy();

        session_start();
        $_SESSION['toast'] = [
            'type' => 'error',
            'message' => 'Your account has been suspended due to suspicious activity.'
        ];

        header("Location: ../index/index.php");
        exit;
    }
}