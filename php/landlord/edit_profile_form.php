<?php
require_once "../session.php";
require_once "../db_connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'landlord'){
    $_SESSION['toast'] = [
    'type' => 'error',
    'message' => 'For that you need to be logged in.'
];
    header("Location: login_form.php");
    exit;

}

$landlord_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT full_name, email, phone, profile_image
    FROM landlords
    WHERE landlord_id = ?
");
$stmt->bind_param("s", $landlord_id);
$stmt->execute();
$landlord = $stmt->get_result()->fetch_assoc();
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
         <a href="view_profile.php" class="back-btn" title="Go back">
    ←
</a>
    <h2>Edit Profile</h2>

   

    <form method="POST" action="edit_profile.php" enctype="multipart/form-data">

    <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
    
        <label>Name</label>
        <input type="text" name="full_name" 
               value="<?= htmlspecialchars($landlord['full_name']) ?>" required>
        <label>Email</label>
        <input type="email" name="email" 
               value="<?= htmlspecialchars($landlord['email']) ?>" required>

        <label>Phone</label>
        <input type="text" name="phone" 
               value="<?= htmlspecialchars($landlord['phone']) ?>" required>

                <img 
        src="<?= $landlord['profile_image'] ?: '../uploads/profiles/default.png' ?>" 
        class="profile-pic"
    >
        <label>Profile Picture (optional)</label>
        <input type="file" name="profile_image" accept="image/*">

        <button type="submit" class="btn btn-success">
            Save Changes
        </button>
    </form>
    </div>
</div>


</body>
</html>