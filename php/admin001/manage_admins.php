<?php
require_once 'admin001_guard.php';
require_once '../includes/config/db_connect.php';
require_once '../toast.php';

// Create a new admin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($name) || empty($email) || empty($password)) {
        set_toast('Name, email, and password are required.', 'error');
        header('Location: manage_admins.php');
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_toast('Please provide a valid email address.', 'error');
        header('Location: manage_admins.php');
        exit;
    }

    // Prevent creating another super admin with same credentials
    $superEmail = 'admin@h2p.co.ke';
    $superName = 'H2P_ADMIN_1';
    if (strtolower($email) === strtolower($superEmail) && trim($name) === $superName) {
        set_toast('Cannot create another super admin account.', 'error');
        header('Location: manage_admins.php');
        exit;
    }

    $stmt = $conn->prepare('SELECT COUNT(*) AS total FROM admins WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $exists = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
    $stmt->close();

    if ($exists > 0) {
        set_toast('An admin account with that email already exists.', 'error');
        header('Location: manage_admins.php');
        exit;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare('INSERT INTO admins (name, email, password, status, created_at) VALUES (?, ?, ?, ?, NOW())');
    $status = 'active';
    $stmt->bind_param('ssss', $name, $email, $hash, $status);
    $stmt->execute();
    $stmt->close();

    set_toast('Admin account created successfully.', 'success');
    header('Location: manage_admins.php');
    exit;
}

$admins = $conn->query('SELECT * FROM admins ORDER BY admin_id DESC');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin001 - Manage Admins</title>
    <link rel="stylesheet" href="../admin/admin_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
<a href="index.php" class="back-btn" title="Go back">← Back to Admin001</a>

<?php include '../toast.php'; ?>

<div class="admin-wrapper">
    <h2><i class="fa-solid fa-user-shield"></i> Manage Admin Accounts</h2>

    <div class="card" style="max-width: 700px;">
        <h3>Create New Admin</h3>
        <form method="POST" onsubmit="return handleSubmit(this, 'Creating admin...')">
            <input type="text" name="name" placeholder="Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" class="btn">Create Admin</button>
        </form>
    </div>

    <h3 style="margin-top: 30px;">Existing Admins</h3>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $admins->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['admin_id'] ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><span class="status <?= htmlspecialchars($row['status']) ?>"><?= htmlspecialchars($row['status']) ?></span></td>
                    <td>
                        <?php if ($row['email'] !== 'admin@h2p.co.ke'): ?>
                            <?php if ($row['status'] === 'active'): ?>
                                <a href="toggle_admin.php?id=<?= $row['admin_id'] ?>&action=suspend" class="danger" onclick="return confirm('Suspend this admin?');">Suspend</a>
                            <?php else: ?>
                                <a href="toggle_admin.php?id=<?= $row['admin_id'] ?>&action=activate" class="success">Activate</a>
                            <?php endif; ?>
                            <a href="delete_admin.php?id=<?= $row['admin_id'] ?>" class="danger" onclick="return confirm('Permanently delete this admin?');">Delete</a>
                        <?php else: ?>
                            <em>Super Admin</em>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
