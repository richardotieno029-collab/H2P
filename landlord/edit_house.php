<?php
require_once "auth_landlord.php";

$areaOptions = [
    'Bagik',
    'Gakwegori',
    'Spring Valley',
    'Kamiu',
    'Kangaru',
    'Kayole',
    'Njukiri',
    'Leaders',
    'Perez',
    'Iveche',
    'Koimongu',
    'Town',
    'Other',
];

if (!isset($_GET['id'])) {
     $_SESSION['toast'] = [
    'type' => 'error',
    'message' => 'Identity mismatch.'
];
    header("Location: landlord_dashboard.php");
    exit;
}

$house_id = intval($_GET['id']);
$landlord_id = $_SESSION['user_id'];

$sql = "SELECT * FROM houses WHERE house_id = ? AND landlord_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $house_id, $landlord_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
     $_SESSION['toast'] = [
    'type' => 'error',
    'message' => 'House not found.'
];
    header("Location: landlord_dashboard.php");
    exit;
}

$house = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit House</title>
    <link rel="stylesheet" href="../includes/assets/css/styles.css">
</head>
<body>
    <?php include "../includes/toast.php"; ?>

<div class="form-wrapper">
    <a href="javascript:history.back()" class="back-btn" title="Go back">
    ←
</a>
    <div class="logo-container">
    <img src="../images/logo.jpeg" alt="H2P Logo" class="logo-img">
    <h1 class="logo-text">H2P</h1>
    <p class="logo-tagline">FIND. RENT. SETTLE.</p>
</div>
<h2>Edit House</h2>

<form action="update_house.php" method="POST" enctype="multipart/form-data" onsubmit="return handleSubmit(this, 'Updating listing...')">

<input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
    <input type="hidden" name="house_id" value="<?php echo $house['house_id']; ?>">

    <label>House Name</label>
    <input type="text" name="house_name" value="<?php echo htmlspecialchars($house['house_name']); ?>" required>

    <label>Area</label>
    <select name="area" required>
        <option value="">Select area</option>
        <?php foreach ($areaOptions as $areaOption): ?>
            <option value="<?= htmlspecialchars($areaOption) ?>" <?= ($house['area'] === $areaOption) ? 'selected' : '' ?>>
                <?= htmlspecialchars($areaOption) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Room Type</label>
    <select name="room_type" value="<?php echo htmlspecialchars($house['room_type']); ?>" required>
        <option>Single</option>
        <option>Bedsitter</option>
        <option>One Bedroom</option>
        <option>Two Bedroom</option>
        <option>Hostel</option>
        </select>

    <label>Rent per Month</label>
    <input type="number" name="price" value="<?php echo $house['price']; ?>" required>

    <label>Refundable deposit</label>
    <input type="number" name="deposit" value="<?php echo $house['deposit']; ?>" required>

    <p>Cover Image:</p>
    <img src="<?php echo $house['image_path']; ?>" width="150">

    <label>Change Image (optional)</label>
    <input type="file" name="house_image" accept="image/*" data-max-size="5242880">

    <label>Add other Images</label>
        <input type="file" name="gallery_images[]" accept="image/*" data-max-files="5" data-max-size="5242880" multiple >


    <button type="button" class="toggle-btn" onclick="toggleDetails()">
    Utilities
</button>

<div id="moreDetails" class="hidden-section">

    <label class="custom-checkbox">
 <input type="checkbox" name="electricity_available"
<?php if($house['electricity_available'] == 1) echo "checked"; ?>>
    <span class="checkmark"></span>
  Electricity Available
</label>

<label class="custom-checkbox">
  <input type="checkbox" name="electricity_covered"
<?php if($house['electricity_covered'] == 1) echo "checked"; ?>>
  <span class="checkmark"></span>
  Electricity Covered in Rent
</label>

<label class="custom-checkbox">
  <input type="checkbox" name="token_meter"
<?php if($house['token_meter'] == 1) echo "checked"; ?>>
  <span class="checkmark"></span>
  Token Meter Available
</label>

<label class="custom-checkbox">
  <input type="checkbox" name="water_available"
<?php if($house['water_available'] == 1) echo "checked"; ?>>
  <span class="checkmark"></span>
  Water Available
</label>

<label class="custom-checkbox">
  <input type="checkbox" name="water_covered"
<?php if($house['water_covered'] == 1) echo "checked"; ?>>
  <span class="checkmark"></span>
  Water Covered in Rent
</label>
<label class="custom-checkbox">
  <input type="checkbox" name="wifi_available"
<?php if($house['wifi_available'] == 1) echo "checked"; ?>>
  <span class="checkmark"></span>
  WiFi Available
</label>

<label class="custom-checkbox">
  <input type="checkbox" name="wifi_extra_payment"
<?php if($house['wifi_extra_payment'] == 1) echo "checked"; ?>>
  <span class="checkmark"></span>
  WiFi Requires Extra Payment
</label>
<label class="custom-checkbox">
  <input type="checkbox" name="hot_shower"
<?php if($house['hot_shower'] == 1) echo "checked"; ?>>
  <span class="checkmark"></span>
  Hot Shower Available
</label>

<label class="custom-checkbox">
  <input type="checkbox" name="shared_toilet"
<?php if($house['shared_toilet'] == 1) echo "checked"; ?>>
  <span class="checkmark"></span>
  Shared Toilet Available
</label>
<label class="custom-checkbox">
  <input type="checkbox" name="shared_water_point"
<?php if($house['shared_water_point'] == 1) echo "checked"; ?>>
  <span class="checkmark"></span>
  Shared Water Point Available
</label>
<label>Other Descriptions</label>
        <input type="text" name="description" value="<?php echo htmlspecialchars($house['description']); ?>" required>
        
         <button type="submit">Save Changes</button>


</div>
<script>
    function toggleDetails() {
    const section = document.getElementById("moreDetails");

    if (section.style.display === "none") {
        section.style.display = "block";
    } else {
        section.style.display = "none";
    }
}
</script>

</form>

</div>
</body>
</html>