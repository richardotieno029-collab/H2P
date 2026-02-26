<?php
require_once "../session.php";
require_once "../db_connect.php";

if ($_SESSION['user_role'] !== 'student') {
    $_SESSION['toast'] = [
    'type' => 'error',
    'message' => 'You are not authorized to access this page.'
];
    header("Location: ../index/index.php");
    exit;
}
// Fetch unread notifications count
$notifStmt = $conn->prepare("
    SELECT COUNT(*) AS total 
    FROM notifications 
    WHERE user_id = ? AND is_read = 0
");
$notifStmt->bind_param("s", $_SESSION['user_id']);
$notifStmt->execute();
$notif = $notifStmt->get_result()->fetch_assoc();

// fetch recently added houses (optional preview)
$recentQuery = "
    SELECT h.house_id, h.house_name, h.area, h.image_path
    FROM houses h
    ORDER BY h.created_at DESC
    LIMIT 3
";
$recentResult = mysqli_query($conn, $recentQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard | H2P</title>
    <link rel="stylesheet" href="../styles.css">
    <script src="../scripts.js"></script>

</head>

<?php include "dashboard_header.php"; ?>
<?php include "../toast.php"; ?>
<body>
<div class="dashboard-container">

    <h2 class="dashboard-title">
        Welcome back, <?= htmlspecialchars($_SESSION['user_name']); ?>
    </h2>

    <!-- ACTION CARDS -->
    <div class="dashboard-actions">

        <a href="browse_houses.php" class="dash-card">
            <h3>🔍 Browse Houses</h3>
            <p>Explore all available houses and rooms</p>
            <?php if ($notif['total'] > 0): ?>
        <span class="notify-dot"></span>
    <?php endif; ?>
        </a>

        <a href="../favourites/view_favourites.php" class="dash-card">
            <h3>❤️ My Favourites</h3>
            <p>Rooms you’ve saved</p>
        </a>

        <a href="#" class="dash-card">
            <h3>🆕 Room mate Quest</h3>
            <p>Coming Soon</p>
        </a>

        <a href="#" class="dash-card">
            <h3>Furnished Rooms</h3>
            <p>coming soon...</p>
        </a>

    </div>

    <!-- RECENT PREVIEW -->
    <div class="recent-section">
        <h3>Recently Added Houses</h3>

        <div class="house-grid">
            <?php if ($recentResult && mysqli_num_rows($recentResult) > 0): ?>
                <?php while ($house = mysqli_fetch_assoc($recentResult)): ?>
                    <div class="house-card">
                        <img src="<?= htmlspecialchars($house['image_path']); ?>" alt="House image">
                        <h4><?= htmlspecialchars($house['house_name']); ?></h4>
                        <a href="view_room.php?house_id=<?= $house['house_id']; ?>" class="btn">
                            View Details
                        </a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No houses added yet.</p>
            <?php endif; ?>
        </div>
    </div>

</div>

</body>
</html>