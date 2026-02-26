<?php
require_once "../session.php";
require_once "../db_connect.php";

if ($_SESSION['user_role'] !== 'landlord') {
        $_SESSION['toast'] = [
    'type' => 'error',
    'message' => 'Access denied.'
    ];
    header("Location: ../index/index.php");
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link rel="stylesheet" href="../styles.css">
    
</head>
<body>
    <div class="profile-card">
       
        <h2>My Profile</h2>
    <img 
        src="<?= $landlord['profile_image'] ?: '../uploads/profiles/default.png' ?>" 
        class="profile-pic1"
    >

    <h3><?= htmlspecialchars($landlord['full_name']) ?></h3>
    <p>Email: <?= htmlspecialchars($landlord['email']) ?></p>
    <p>Phone: <?= htmlspecialchars($landlord['phone']) ?></p>

    <a href="edit_profile_form.php" class="btn">Edit Profile</a>
</div>
</body>
</html>
