<?php
require_once "auth_landlord.php";

$id = $_GET['id'];

// Fetch house info
$stmt = $conn->prepare("SELECT * FROM houses WHERE house_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$house = $result->fetch_assoc();
$house_id = $house['house_id'];
// Fetch additional images
$stmt2 = $conn->prepare("SELECT * FROM house_images WHERE house_id = ?");
$stmt2->bind_param("i", $id);
$stmt2->execute();
$images = $stmt2->get_result();
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
            <img src="<?= $house['image_path']; ?>">
        </div>

        <!-- Additional Images -->
        <?php while($img = $images->fetch_assoc()): ?>
            <div class="slide">
                <img src="<?= $img['image_path']; ?>">
                <div class="actions">
                <a href="delete_house_image.php?id=<?= $img['id']; ?>&house_id=<?= $house_id; ?>">
                    Delete</a>
        </div>
            </div>
        <?php endwhile; ?>
    </div>

    <button class="prev" onclick="moveSlide(-1)">❮</button>
    <button class="next" onclick="moveSlide(1)">❯</button>

</div>
<!-- utilities -->
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

</div>
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

