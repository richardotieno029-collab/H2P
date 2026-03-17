<?php
require_once '../session.php';
require_once '../db_connect.php';
include "../toast.php";


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['token']) || 
        !hash_equals($_SESSION['token'], $_POST['token'])) {
        die("Invalid request.");
    }

    $user_id = $_SESSION['user_id'] ?? null;
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $subject = htmlspecialchars($_POST['subject']);
    $message = htmlspecialchars($_POST['message']);

    $stmt = $conn->prepare("INSERT INTO reports (user_id, name, email, subject, message) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, $name, $email, $subject, $message);
    $stmt->execute();

    $success = "Your message has been sent successfully!";
    $_SESSION['toast'] = [
        'type' => 'info',
        'message' => 'Your message has been sent successfully. We will get back to you shortly.'
    ];
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - H2P</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
    body {
    background: rgb(48, 231, 130);
    font-family: Arial, sans-serif;
}
.back-btn {
    display: inline-flex;
    align-items: left;
    gap: 6px;
    font-size: 20px;
    color: #333;
    text-decoration: none;
    margin-bottom: 15px;
    cursor: pointer;
}

.contact-container {
    width: 90%;
    max-width: 600px;
    margin: 60px auto;
    background: #34d4e9;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

h2 {
    text-align: center;
    margin-bottom: 20px;
}

input, textarea {
    width: 100%;
    padding: 12px;
    margin-bottom: 15px;
    border: 1px solid #ddd;
    border-radius: 6px;
}

textarea {
    height: 120px;
    resize: none;
}

button {
    width: 100%;
    padding: 12px;
    background: #0a66c2;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}

button:hover {
    background: #004182;
}

.success {
    background: #d4edda;
    padding: 10px;
    border-radius: 6px;
    margin-bottom: 15px;
}

.social-section {
    text-align: center;
    margin-top: 30px;
}

.social-section a {
    display: inline-block;
    margin: 5px 10px;
    text-decoration: none;
    font-weight: bold;
}

.whatsapp {
    color: #25D366;
}
.social-section {
    text-align: center;
    margin-top: 30px;
}

.social {
    display: inline-block;
    margin: 8px;
    padding: 10px 15px;
    border-radius: 6px;
    text-decoration: none;
    color: white;
    font-weight: bold;
    transition: 0.3s ease;
}

.social i {
    margin-right: 8px;
    font-size: 18px;
}

/* Brand Colors */
.whatsapp { background: #25D366; }
.instagram { background: #E4405F; }
.tiktok { background: #000000; }
.facebook { background: #1877F2; }
.email { background: #444; }

.social:hover {
    transform: translateY(-3px);
    opacity: 0.85;
    .floating-whatsapp {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: #25D366;
    color: white;
    padding: 15px;
    border-radius: 50%;
    font-size: 22px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    z-index: 1000;
}
}
</style>
</head>
<body>

<div class="contact-container">
    <a href="javascript:history.back()" class="back-btn" title="Go back">
    ←
</a>

<h2>Contact & Support</h2>

<?php if (!empty($success)): ?>
<div class="success"><?= $success ?></div>
<?php endif; ?>

<form method="POST" onsubmit="return handleSubmit(this, 'Sending message...')">
<input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">

<input type="text" name="name" placeholder="Your Name" required>
<input type="email" name="email" placeholder="Your Email" required>
<input type="text" name="subject" placeholder="Subject" required>
<textarea name="message" placeholder="Describe your issue..." required></textarea>

<button type="submit">Send Message</button>
</form>

<div class="social-section">
    <h3>Follow Us</h3>

    <a href="https://wa.me/254107426493" target="_blank" class="social whatsapp">
        <i class="fa-brands fa-whatsapp"></i> WhatsApp
    </a>

    <a href="https://instagram.com/h2p_ke" target="_blank" class="social instagram">
        <i class="fa-brands fa-instagram"></i> Instagram
    </a>

    <a href="https://tiktok.com/@h2p_ke" target="_blank" class="social tiktok">
        <i class="fa-brands fa-tiktok"></i> TikTok
    </a>

    <a href="https://facebook.com/Htwop Kenya" target="_blank" class="social facebook">
        <i class="fa-brands fa-facebook"></i> Facebook
    </a>

    <a href="mailto:support@h2p.co.ke" class="social email">
        <i class="fa-solid fa-envelope"></i> Email
    </a>
    <a href="https://wa.me/254712345678" class="floating-whatsapp" target="_blank">
    <i class="fa-brands fa-whatsapp"></i>
</a>
</div>

</div>

</body>
</html>