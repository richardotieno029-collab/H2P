<?php
require_once "auth_landlord.php";

$id = $_GET['id'];

// Fetch room info
$stmt = $conn->prepare("SELECT * FROM rooms WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$room = $result->fetch_assoc();

// Fetch additional images
$stmt2 = $conn->prepare("SELECT * FROM room_images WHERE room_id = ?");
$stmt2->bind_param("i", $id);
$stmt2->execute();
$images = $stmt2->get_result();

// Fetch booking details (pending first, then approved)
$pendingBooking = null;
$approvedBooking = null;

$pendingStmt = $conn->prepare(
    "SELECT b.id AS booking_id, b.status AS booking_status, b.expires_at, " .
    "s.full_name, s.email, s.phone, s.profile_image " .
    "FROM bookings b " .
    "JOIN students s ON b.student_internal_id = s.id " .
    "WHERE b.room_id = ? AND b.status = 'pending' " .
    "ORDER BY b.id DESC " .
    "LIMIT 1"
);
$pendingStmt->bind_param("i", $id);
$pendingStmt->execute();
$pendingBooking = $pendingStmt->get_result()->fetch_assoc();

if (!$pendingBooking) {
    $approvedStmt = $conn->prepare(
        "SELECT b.id AS booking_id, b.status AS booking_status, b.approved_expires_at, " .
        "s.full_name, s.email, s.phone, s.profile_image " .
        "FROM bookings b " .
        "JOIN students s ON b.student_internal_id = s.id " .
        "WHERE b.room_id = ? AND b.status = 'approved' " .
        "ORDER BY b.id DESC " .
        "LIMIT 1"
    );
    $approvedStmt->bind_param("i", $id);
    $approvedStmt->execute();
    $approvedBooking = $approvedStmt->get_result()->fetch_assoc();
}

$booking = $pendingBooking ?: $approvedBooking;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../includes/assets/css/styles.css">
    <title>House Details</title>
</head>
<body>
<?php include "../includes/toast.php"; ?>
<div class="slider">

    <div class="slides">
        <!-- Main Thumbnail First -->
        <div class="slide">
            <img src="<?= $room['image_path']; ?>">
        </div>

        <!-- Additional Images -->
        <?php while($img = $images->fetch_assoc()): ?>
            <div class="slide">
                <img src="<?= $img['image_path']; ?>">
                <div class="actions">
                <a href="delete_room_image.php?id=<?= $img['id']; ?>&room_id=<?= $id; ?>">
                    Delete</a>
        </div>
            </div>
        <?php endwhile; ?>
    </div>

    <button class="prev" onclick="moveSlide(-1)">❮</button>
    <button class="next" onclick="moveSlide(1)">❯</button>

</div>

<div class="room-status-panel" style="margin: 20px 0; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
    <?php if ($booking && $booking['booking_status'] === 'pending'): ?>
        <?php
            $studentPhoneRaw = $booking['phone'] ?? '';
            $studentPhone = preg_replace('/[^0-9+]/', '', $studentPhoneRaw);
            $studentWhatsapp = ltrim($studentPhone, '+');
            $returnUrl = 'room_details.php?id=' . urlencode($id);
        ?>
        <h2>Pending Booking</h2>
        <div style="display:flex; gap:16px; align-items:center; flex-wrap:wrap;">
            <div style="flex: 0 0 auto;">
                <img src="../uploads/<?= htmlspecialchars($booking['profile_image'] ?? ''); ?>" alt="Student profile" style="width:120px;height:120px;object-fit:cover;border-radius:8px;border:1px solid #ddd;">
            </div>
            <div style="flex:1; min-width:220px;">
                <h3 style="margin-top:0;"><?= htmlspecialchars($booking['full_name'] ?? ''); ?></h3>
                <?php if (!empty($booking['email'])): ?>
                    <a href="mailto:<?= htmlspecialchars($booking['email']); ?>" class="btn">Email</a>
                <?php endif; ?>
                <?php if (!empty($studentPhone)): ?>
                    <a href="tel:<?= htmlspecialchars($studentPhone); ?>" class="btn">Call</a>
                    <a href="https://wa.me/<?= htmlspecialchars($studentWhatsapp); ?>" target="_blank" rel="noopener" class="btn">WhatsApp</a>
                <?php endif; ?>
            </div>
        </div>
        <div style="margin-top: 18px; display:flex; gap:10px; flex-wrap:wrap;">
            <a href="approve_booking.php?id=<?= $booking['booking_id']; ?>&return=<?= urlencode($returnUrl); ?>" class="btn" onclick="showLoading('Approving booking...')">Accept</a>
            <a href="reject_booking.php?id=<?= $booking['booking_id']; ?>&return=<?= urlencode($returnUrl); ?>" class="btn btn-danger" onclick="if (!confirm('Reject this booking?')) return false; showLoading('Rejecting booking...');">Reject</a>
        </div>
    <?php elseif ($booking && $booking['booking_status'] === 'approved'): ?>
        <?php
            $studentPhoneRaw = $booking['phone'] ?? '';
            $studentPhone = preg_replace('/[^0-9+]/', '', $studentPhoneRaw);
            $studentWhatsapp = ltrim($studentPhone, '+');
        ?>
        <h2>Occupied</h2>
        <div style="display:flex; gap:16px; align-items:center; flex-wrap:wrap;">
            <div style="flex: 0 0 auto;">
                <img src="../uploads/<?= htmlspecialchars($booking['profile_image'] ?? ''); ?>" alt="Student profile" style="width:120px;height:120px;object-fit:cover;border-radius:8px;border:1px solid #ddd;">
            </div>
            <div style="flex:1; min-width:220px;">
                <h3 style="margin-top:0;"><?= htmlspecialchars($booking['full_name'] ?? ''); ?></h3>
                <?php if (!empty($booking['email'])): ?>
                    <a href="mailto:<?= htmlspecialchars($booking['email']); ?>" class="btn">Email</a>
                <?php endif; ?>
                <?php if (!empty($studentPhone)): ?>
                    <a href="tel:<?= htmlspecialchars($studentPhone); ?>" class="btn">Call</a>
                    <a href="https://wa.me/<?= htmlspecialchars($studentWhatsapp); ?>" target="_blank" rel="noopener" class="btn">WhatsApp</a>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <h2>Vacant</h2>
        <p>This room is currently vacant.</p>
    <?php endif; ?>
</div>

<!-- utilities 
<div class="utilities">
    <h3>Shared Utilities</h3>

    <?php if($house['electricity_available']): ?>
        <p>⚡ Electricity 
        <?php
            if($house['electricity_covered']) echo "- Covered in Rent";
            elseif($house['token_meter']) echo "- Token Meter";
        ?>
        </p>
    <?php endif; ?>

    <?php if($house['water_available']): ?>
        <p>💧 Water 
        <?= $house['water_covered'] ? "- Covered in Rent" : ""; ?>
        </p>
    <?php endif; ?>

    <?php if($house['wifi_available']): ?>
        <p>📶 WiFi 
        <?= $house['wifi_extra_payment'] ? "- Extra Payment" : ""; ?>
        </p>
    <?php endif; ?>

    <?php if($house['hot_shower']): ?>
        <p>🚿 Hot Shower Available</p>
    <?php endif; ?>

    <?php if($house['shared_toilet']): ?>
        <p>🚽 Shared Toilet</p>
    <?php endif; ?>

    <?php if($house['shared_water_point']): ?>
        <p>🚰 Shared Water Point</p>
    <?php endif; ?>

</div> -->
<script>
    let currentSlide = 0;

function moveSlide(direction) {
    const slides = document.querySelector('.slides');
    const totalSlides = document.querySelectorAll('.slide').length;

    currentSlide += direction;

    if (currentSlide < 0) {
        currentSlide = totalSlides - 1;
    }

    if (currentSlide >= totalSlides) {
        currentSlide = 0;
    }

    slides.style.transform = `translateX(-${currentSlide * 100}%)`;
}
</script>
</body>
</html>

