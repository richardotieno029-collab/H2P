<?php
session_start();
require_once __DIR__ . "/../db_connect.php"; // your existing DB connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
    $phone = trim($_POST["phone"]);
    $email = trim($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    // Check if email already exists
    $check = $conn->prepare("SELECT landlord_id FROM landlords WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo "Email already registered.";
        exit;
    }

    // Insert landlord
    $stmt = $conn->prepare(
        "INSERT INTO landlords (name, phone, email, password) VALUES (?, ?, ?, ?)"
    );
    $stmt->bind_param("ssss", $name, $phone, $email, $password);

    if ($stmt->execute()) {
        $_SESSION["landlord_id"] = $stmt->insert_id;
        $_SESSION["landlord_name"] = $name;

        header("Location: landlord_dashboard.php");
        exit;
    } else {
        echo "Registration failed.";
    }
}
?>