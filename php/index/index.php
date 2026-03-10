<?php
session_start();

if (isset($_SESSION['user_id'], $_SESSION['user_role'])) {
    if ($_SESSION['user_role'] === 'student') {
        header("Location: ../student/dashboard.php");
        exit;
    }
}

if (isset($_SESSION['user_id'], $_SESSION['user_role'])) {
    if ($_SESSION['user_role'] === 'landlord') {
        header("Location: ../landlord/landlord_dashboard.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>H2P | Find or List Houses</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        /* Landing page only styles */
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #88f591;
            font-family: Arial, sans-serif;
        }

        .role-container {
            background: #2089df;
            padding: 40px;
            border-radius: 12px;
            max-width: 900px;
            width: 100%;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            text-align: center;
        }

        .role-container h1 {
            margin-bottom: 10px;
        }

        .role-container p {
            color: #555;
            margin-bottom: 30px;
        }

        .roles {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .role-card {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 30px 20px;
            transition: 0.3s ease;
            background: #fafafa;
        }

        .role-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        }

        .role-card h3 {
            margin-bottom: 10px;
        }

        .role-card p {
            font-size: 14px;
            margin-bottom: 20px;
        }

        .role-card a {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            color: #fff;
            font-weight: bold;
        }

        .student-btn { background: #007bff; }
        .landlord-btn { background: #28a745; }
        .guest-btn { background: #6c757d; }

        .footer-note {
            margin-top: 30px;
            font-size: 13px;
            color: #777;
        }
            .logo-container {
    text-align: center;
    margin-top: 60px;
    margin-bottom: 40px;
}

.logo-img {
    width: 110px;
    height: auto;
    margin-bottom: 10px;
}

.logo-text {
    font-size: 36px;
    font-weight: bold;
    color: #2e7d32; /* green */
    margin: 0;
}

.logo-tagline {
    font-size: 14px;
    color: #555;
    margin-top: 5px;
}
        </style>

</head>
<body>
    <?php include "../toast.php"; ?>
<div class="role-container">
    <div class="top-left-links">
    <a href="about.php">About</a>
    <a href="contact.php">Contact</a>
</div>
    <!-- Logo section -->
<div class="logo-container">
    <img src="../../images/logo.jpeg" alt="H2P Logo" class="logo-img">
    <h1 class="logo-text">H2P</h1>
    <p class="logo-tagline">FIND. RENT. SETTLE.</p>
</div>
    <h1>Welcome to H2P</h1>
    <p>Choose how you want to use the platform</p>

    <div class="roles">

        <!-- Student -->
        <div class="role-card">
            <h3>🎓 Student</h3>
            <p>Browse houses, view rooms, and save favourites.</p>
            <a href="../student/login_form.php" class="student-btn">
                Continue as Student
            </a>
        </div>

        <!-- Landlord -->
        <div class="role-card">
            <h3>🏠 Landlord</h3>
            <p>Add houses, manage rooms, and track availability.</p>
            <a href="../landlord/login_form.php" class="landlord-btn">
                Continue as Landlord
            </a>
        </div>

        <!-- Guest -->
        <div class="role-card">
            <h3>👀 Guest</h3>
            <p>View houses and rooms without creating an account.</p>
            <a href="../guest/browse_houses.php" class="guest-btn">
                Continue as Guest
            </a>
        </div>

    </div>

    <div class="footer-note">
        You can always create an account later to unlock full features.
    </div>
</div>

</body>
</html>