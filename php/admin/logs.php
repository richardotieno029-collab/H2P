<?php
require '../db_connect.php';
session_start();
require_once '../includes/loader.php';
require_once 'admin_guard.php';

$userTypeFilter = $_GET['user_type'] ?? '';
$searchName     = $_GET['search'] ?? '';

$sql = "
SELECT 
    activity_logs.*,
    students.full_name AS student_name,
    landlords.full_name AS landlord_name
FROM activity_logs
LEFT JOIN students 
    ON activity_logs.user_id = students.id 
    AND activity_logs.user_type = 'student'
LEFT JOIN landlords 
    ON activity_logs.user_id = landlords.id 
    AND activity_logs.user_type = 'landlord'
WHERE 1=1
";

$params = [];
$types  = "";

// Filter by type
if (!empty($userTypeFilter)) {
    $sql .= " AND activity_logs.user_type = ?";
    $params[] = $userTypeFilter;
    $types .= "s";
}

// Search by name
if (!empty($searchName)) {
    $sql .= " AND (
        students.full_name LIKE ? 
        OR landlords.full_name LIKE ?
    )";
    $searchTerm = "%$searchName%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "ss";
}

$sql .= " ORDER BY activity_logs.created_at DESC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Activity Logs</title>
    <link rel="stylesheet" href="admin_styles.css">
    <style>
        select { margin-bottom: 10px; }
    </style>
</head>
<body>
    <a href="dashboard.php" class="back-btn" title="Go back">
    Back to Dashboard
</a>

<h2>Activity Logs</h2>

<form method="GET" style="margin-bottom:15px;" onsubmit="return handleSubmit(this, 'Searching logs...')">

    <!-- Search by Name -->
    <input 
        type="text" 
        name="search" 
        placeholder="Search by name..."
        value="<?= htmlspecialchars($searchName ?? '') ?>"
        style="padding:6px;"
    >

    <!-- Filter by Type -->
    <select name="user_type" style="padding:6px;">
        <option value="">All Types</option>
        <option value="student" <?= ($userTypeFilter=='student')?'selected':'' ?>>Student</option>
        <option value="landlord" <?= ($userTypeFilter=='landlord')?'selected':'' ?>>Landlord</option>
    </select>

    <button type="submit" style="padding:6px 10px;">Search</button>

</form>
<a href="logs.php" style="margin-left:10px;">Reset</a>

<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>User Type</th>
        <th>User ID</th>
        <th>User Name</th>
        <th>Action</th>
        <th>IP Address</th>
        <th>Created At</th>
    </tr>
</thead>

<tbody>

    <?php while ($row = $result->fetch_assoc()):  

    if ($row['user_type'] === 'student') {
        $name = $row['student_name'];
    } else {
        $name = $row['landlord_name'];
    } 
        ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['user_type']) ?></td>
            <td><?= $row['user_id'] ?></td>
             <td><?= htmlspecialchars($name) ?></td>
            <td><?= htmlspecialchars($row['action']) ?></td>
            <td><?= htmlspecialchars($row['ip_address']) ?></td>
            <td><?= $row['created_at'] ?></td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>

</body>
</html>