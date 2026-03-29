<?php
require_once 'admin_guard.php';
include '../includes/toast.php';
// Determine if current admin is super admin (admin001)
$superAdmin = false;
$adminId = $_SESSION['user_id'] ?? null;
if ($adminId) {
    $stmt = $conn->prepare('SELECT name, email FROM admins WHERE admin_id = ?');
    $stmt->bind_param('i', $adminId);
    $stmt->execute();
    $adminRow = $stmt->get_result()->fetch_assoc();
    if ($adminRow && $adminRow['name'] === 'H2P_ADMIN_1' && strtolower($adminRow['email']) === 'admin@h2p.co.ke') {
        $superAdmin = true;
    }
}

/* Quick stats */
$totalReports = $conn->query("SELECT COUNT(*) as total FROM reports")->fetch_assoc()['total'];
$newReports = $conn->query("SELECT COUNT(*) as total FROM reports WHERE status='new'")->fetch_assoc()['total'];

$totalLandlords = $conn->query("SELECT COUNT(*) as total FROM landlords")->fetch_assoc()['total'];
$totalStudents = $conn->query("SELECT COUNT(*) as total FROM students")->fetch_assoc()['total'];
$totalFlags = $conn->query("SELECT COUNT(*) as total FROM spam_flags")->fetch_assoc()['total'];
$totallogs = $conn->query("SELECT COUNT(*) as total FROM activity_logs")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - H2P</title>
    <link rel="stylesheet" href="admin_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
     <a href="../index.php" class="back-btn" title="Go back">
    Home
</a>

<div class="admin-wrapper">

<h1><i class="fa-solid fa-user-shield"></i> Admin Dashboard</h1>

<div class="cards">

<div class="card">
<i class="fa-solid fa-flag"></i>
<h3><?= $totalReports ?></h3>
<p>Total Reports</p>
<a href="admin_reports.php">View Reports</a>
</div>

<div class="card">
<i class="fa-solid fa-triangle-exclamation"></i>
<h3><?= $newReports ?></h3>
<p>New Reports</p>
<a href="admin_reports.php">Check Now</a>
</div>

<div class="card">
<i class="fa-solid fa-building"></i>
<h3><?= $totalLandlords ?></h3>
<p>Landlords</p>
<a href="users.php?type=landlord">Manage</a>
</div>

<div class="card">
<i class="fa-solid fa-user-graduate"></i>
<h3><?= $totalStudents ?></h3>
<p>Students</p>
<a href="users.php?type=student">Manage</a>
</div>

<div class="card">
<i class="fa-solid fa-triangle-exclamation"></i>
<h3><?= $totalFlags ?></h3>
<p>Total Spam Flags</p>
<a href="spam.php">View Spam Flags</a>
</div>

<div class="card">
<i class="fa-solid fa-book"></i>
<h3><?= $totallogs ?></h3>
<p>Total Logs</p>
<a href="logs.php">View logs</a>
</div>

<?php if ($superAdmin): ?>
<div class="card">
    <i class="fa-solid fa-shield-halved"></i>
    <h3>Admin001</h3>
    <p>Super Admin Tools</p>
    <a href="../admin001/index.php">Open</a>
</div>
<?php endif; ?>

</div>

</div>

<button id="scrollTopBtn" class="scrollTopBtn" title="Go to top">↑</button>
<script src="../includes/assets/js/scroll_top.js"></script>
<script>
initScrollTop();
</script>


</body>
</html>