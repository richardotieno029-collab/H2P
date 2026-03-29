<?php
session_start();
include 'includes/toast.php';

if (isset($_SESSION['user_id'], $_SESSION['user_role'])) {
    if ($_SESSION['user_role'] === 'student') {
        header("Location: student/dashboard.php");
        exit;
    }
}

if (isset($_SESSION['user_id'], $_SESSION['user_role'])) {
    if ($_SESSION['user_role'] === 'landlord') {
        header("Location: landlord/landlord_dashboard.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>H2P | Find or List Houses</title>
    <link rel="stylesheet" href="includes/assets/css/styles.css">
</head>
<body class="landing-page">
    <header class="landing-header">
        <nav>
            <a href="about.php">👤 About</a>
            <a href="contact.php">☎ Contact</a>
        </nav>
    </header>

    <main class="landing-main">
        <section class="logo-container">
            <img src="images/logo.jpeg" alt="H2P Logo" class="logo-img">
            <h1 class="logo-text">H2P</h1>
            <p class="logo-tagline">FIND. RENT. SETTLE.</p>
        </section>

        <section class="role-container">
            <h1>Welcome to H2P</h1>
            <p>Choose how you want to use the platform</p>

            <div class="roles">
        <!-- Student -->
        <div class="role-card">
            <h3>🎓 Student</h3>
            <p>See full house details, bookings and find roommates.</p>
            <a href="student/login_form.php" class="student-btn">
                Continue as Student
            </a>
        </div>

        <!-- Landlord -->
        <div class="role-card">
            <h3>🏠 Landlord</h3>
            <p>Add houses, manage rooms, and track availability.</p>
            <a href="landlord/login_form.php" class="landlord-btn">
                Continue as Landlord
            </a>
        </div>

        <!-- Guest -->
        <div class="role-card">
            <h3>👀 Guest</h3>
            <p>Have a quick overview of houses' and rooms' details.</p>
            <a href="guest/browse_houses.php" class="guest-btn">
                Continue as Guest
            </a>
        </div>

    </div>

    <div class="footer-note">
        You can always create an account later to unlock full features.
    </div>
        </section>
    </main>

    <footer>
      <p>&copy; 2026 H2P. All Rights Reserved.</p>
      <p><a href="terms.php">Terms & Conditions</a> | <a href="privacy.php">Privacy Policy</a></p>
    </footer>
</body>
</html>