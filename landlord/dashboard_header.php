<?php
require_once "auth_landlord.php";

$id = $_SESSION['user_id'];

$stmt = $conn->prepare(
    "SELECT full_name, profile_image 
     FROM landlords 
     WHERE id = ?"
);
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

/* Fallback image */
$profilePic = !empty($user['profile_image'])
    ? "" . $user['profile_image']
    : "../uploads/profiles/default.png";

$name = $_SESSION['user_name'] 
       ?? $_SESSION['user_name'] 
     ?? 'Account';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
   <link rel="stylesheet" href="../includes/assets/css/styles.css">
</head>


<body>
<header class="dashboard-header">
    <a href="../about.php" class="logo-link">
        <div class="logo-container">
            <img src="../images/logo.jpeg" alt="H2P Logo" class="logo-img">
        </div>
    </a>

    <div class="account-wrapper">
        <button id="accountBtn" class="account-btn" aria-haspopup="true">
        <div class="header-user">
    <img src="<?= htmlspecialchars($profilePic) ?>" 
         alt="Profile"
         class="profile-pic">
</div></span> ▾
        </button>

        <div id="accountDropdown" class="account-dropdown">
            <a href="view_profile.php">Profile</a>
            <a href="../auth/change_password.php">Change Password</a>
            <hr>
            <a href="logout.php" onclick="return confirm('Logout?')"
            class="danger">Logout</a>
            <a href="../auth/delete_account.php" class="danger">Delete Account</a>
        </div>
    </div>
    <script>
document.addEventListener("DOMContentLoaded", () => {
    const btn = document.getElementById("accountBtn");
    const dropdown = document.getElementById("accountDropdown");

    if (!btn || !dropdown) return;

    btn.addEventListener("click", (e) => {
        e.stopPropagation();
        dropdown.classList.toggle("show");
    });

    document.addEventListener("click", () => {
        dropdown.classList.remove("show");
    });
});
</script>
</body>
</header>