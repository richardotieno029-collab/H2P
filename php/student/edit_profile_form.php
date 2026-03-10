<?php
require_once "auth_student.php";
require_once "../db_connect.php";

$id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT full_name, email, phone, profile_image
    FROM students
    WHERE id = ?
");
$stmt->bind_param("s", $id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <?php include "../toast.php"; ?>

<div class="auth-page">
      <div class="auth-container">
         <a href="dashboard.php" class="back-btn" title="Go back">
    ←
</a>
    <h2>Edit Profile</h2>

   

    <form method="POST" action="edit_profile.php" enctype="multipart/form-data">

    <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
        <label>Name</label>
        <input type="text" name="full_name" 
               value="<?= htmlspecialchars($student['full_name']) ?>" required>
        <label>Email</label>
        <input type="email" name="email" 
               value="<?= htmlspecialchars($student['email']) ?>" required>

        <label>Phone</label>
        <input type="text" name="phone" 
               value="<?= htmlspecialchars($student['phone']) ?>" required>

                <img 
        src="<?= $student['profile_image'] ?: '../assets/avatar.png' ?>" 
        class="profile-pic"
    >
        <label>Profile Picture (optional)</label>
        <input type="file" name="profile_image" accept="image/*">

        <button type="submit" class="btn btn-success" onclick="return confirm
        ('Edit profile? You can only edit once in 15 days.')">
            Save Changes
        </button>
    </form>
    </div>
</div>


</body>
</html>