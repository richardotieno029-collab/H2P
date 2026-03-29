<?php
require_once "auth_student.php";

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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="../includes/assets/css/styles.css">
</head>
<body>
    <?php include "../includes/toast.php"; ?>

<div class="auth-page">
      <div class="auth-container">
         <a href="dashboard.php" class="back-btn" title="Go back">
    ←
</a>
    <h2>Edit Profile</h2>

   

    <form method="POST" action="edit_profile.php" enctype="multipart/form-data" onsubmit="return handleSubmit(this, 'Saving profile...')">

    <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
        <label>Name</label>
        <input type="text" name="full_name" 
               value="<?= htmlspecialchars($student['full_name']) ?>" required>
        <label>Email</label>
        <input type="email" name="email" 
               value="<?= htmlspecialchars($student['email']) ?>" pattern="^[0-9]{5}@student\.embuni\.ac\.ke$" title="Use your student email (e.g. 12345@student.embuni.ac.ke)" required>

        <label>Phone</label>
        <input type="tel" name="phone" 
               value="<?= htmlspecialchars($student['phone']) ?>" pattern="(07|01)[0-9]{8}|(2547|2541)[0-9]{8}" title="Valid formats: 0712345678, 0112345678, 254712345678, 254112345678" required>

                <img 
        src="<?= $student['profile_image'] ?: '../assets/avatar.png' ?>" 
        class="profile-pic"
    >
        <label>Profile Picture (optional)</label>
        <input type="file" name="profile_image" accept="image/*" data-max-size="5242880">

        <button type="submit" class="btn btn-success" onclick="return confirm
        ('Edit profile? You can only edit once in 15 days.')">
            Save Changes
        </button>
    </form>
    </div>
</div>


</body>
</html>