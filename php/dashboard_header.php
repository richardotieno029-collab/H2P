<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$name = $_SESSION['user_name'] 
       ?? $_SESSION['user_name'] 
     ?? 'Account';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../styles.css">
</head>


<body>
<header class="dashboard-header">
    <a href="#" class="logo-link">
        <div class="logo-container">
            <img src="../../images/logo.jpeg" alt="H2P Logo" class="logo-img">
        </div>
    </a>

    <div class="account-wrapper">
        <button id="accountBtn" class="account-btn" aria-haspopup="true">
            👤 <span><?= htmlspecialchars($name) ?></span> ▾
        </button>

        <div id="accountDropdown" class="account-dropdown">
            <a href="#">Profile</a>
            <a href="../auth/change_password.php">Change Password</a>
            <hr>
            <a href="../auth/logout.php" class="danger">Logout</a>
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