<?php
require_once "auth_student.php";
require_once "../db_connect.php";

$student_internal_id = $_SESSION['user_id'];

/* Validate room_id */
if (!isset($_GET['room_id']) || !is_numeric($_GET['room_id'])) {
    die("Invalid room.");
}

$room_id = (int) $_GET['room_id'];

//Contact landlord limit

$conn->begin_transaction();

// lock the booking row
$stmt = $conn->prepare("
    SELECT id
    FROM bookings
    WHERE room_id = ?
      AND student_internal_id = ?
      AND status = 'approved'
      AND approved_expires_at > NOW()
    FOR UPDATE
");
$stmt->bind_param("is", $room_id, $student_internal_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) {
    $conn->rollback();
    die("You are all caught up.");
}

/* Ensure booking is APPROVED for this student */
$stmt = $conn->prepare("
    SELECT 
        b.status,
        r.room_number,
        h.house_name,
        l.full_name,
        l.phone,
        l.email
    FROM bookings b
    JOIN rooms r ON b.room_id = r.id
    JOIN houses h ON r.house_id = h.house_id
    JOIN landlords l ON h.landlord_id = l.id
    WHERE b.room_id = ?
      AND b.student_internal_id = ?
      AND b.status IN ('approved', 'occupied')
    LIMIT 1
");

$stmt->bind_param("is", $room_id, $student_internal_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("You are not authorized to contact the landlord for this room.");
}

$data = $result->fetch_assoc();
// confirm occupation if user clicks the button below
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_occupy'])) {

    $conn->begin_transaction();

    $updateBooking = $conn->prepare("
        UPDATE bookings
        SET status = 'occupied'
        WHERE id = ?
          AND status = 'approved'
          AND approved_expires_at > NOW()
    ");

    $updateBooking->bind_param("i", $booking['id']);
    $updateBooking->execute();

    $updateRoom = $conn->prepare("
        UPDATE rooms
        SET status = 'occupied'
        WHERE id = ?
    ");

    $updateRoom->bind_param("i", $room_id);
    $updateRoom->execute();

    $conn->commit();

$_SESSION['toast'] = [
            'type' => 'success',
            'message' => 'Room status changed successfully.'
        ];
    header("Location: browse_houses.php?&success=occupied");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Landlord</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>

<a href="javascript:history.back()" class="back-btn">←</a>

<div class="page-container">

    <h1 class="page-title">Contact Landlord</h1>
    <p class="page-subtitle">
        Your reservation has been approved. You can now contact the landlord within 12 hours span.
    </p>

    <div class="card">
        <h2 class="section-title"><?= htmlspecialchars($data['house_name']) ?></h2>

        <p class="text">
            <strong>Room:</strong> <?= htmlspecialchars($data['room_number']) ?>
        </p>

        <hr style="margin: 15px 0;">

        <p class="text">
            <strong>Landlord:</strong> <?= htmlspecialchars($data['full_name']) ?>
        </p>

        <p class="text">
            <strong>Phone:</strong>
            <a href="tel:<?= htmlspecialchars($data['phone']) ?>" class="contact-link">
                <?= htmlspecialchars($data['phone']) ?>
            </a>
        </p>

        <p class="text">
            <strong>Email:</strong>
            <a href="mailto:<?= htmlspecialchars($data['email']) ?>" class="contact-link">
                <?= htmlspecialchars($data['email']) ?>
            </a>
        </p>

        <p > Refrain from sharing personal this contact with third parties as it violates our privacy policy and can lead to account suspension.
        Please confirm below that you have secured this room to prevent it from being re-allocated.    <form method="POST">
    <input type="hidden" name="confirm_occupy" value="1">
    <button type="submit" class="confirm-btn">
        ✅ I Have Secured This Room
    </button>
</form> </p>

    </div>

</div>

</body>
</html>