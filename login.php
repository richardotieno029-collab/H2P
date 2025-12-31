<?php
session_start();
$conn = new mysqli("localhost", "root", "", "h2p");

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM students WHERE student_id = '$student_id'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $student = $result->fetch_assoc();

        if (password_verify($password, $student['password'])) {
            $_SESSION['student_id'] = $student['student_id'];
            $_SESSION['full_name'] = $student['full_name'];

            header("Location: rooms.php");
            exit;
        } else {
            $message = "Incorrect password.";
        }
    } else {
        $message = "Student ID not found.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Login</title>
</head>
<body>

<h2>Student Login</h2>

<p style="color:red;"><?php echo $message; ?></p>

<form method="POST">
    <label>Student ID:</label><br>
    <input type="text" name="student_id" required><br><br>

    <label>Password:</label><br>
    <input type="password" name="password" required><br><br>

    <button type="submit">Login</button>
</form>

</body>
</html>