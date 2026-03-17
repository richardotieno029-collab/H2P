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
    'Town',
    'Other',
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add House | H2P</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
<?php include "../toast.php"; ?>
<div class="form-wrapper">
    <a href="javascript:history.back()" class="back-btn" title="Go back">
    ←
</a>
    <div class="logo-container">
    <img src="../../images/logo.jpeg" alt="H2P Logo" class="logo-img">
    <h1 class="logo-text">H2P</h1>
    <p class="logo-tagline">FIND. RENT. SETTLE.</p>
</div>
    <h2>Add a New House</h2>

<form action="add_house.php" method="POST" enctype="multipart/form-data" onsubmit="return handleSubmit(this, 'Saving house...')">

<input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
        <label>House Name</label>
        <input type="text" name="house_name" required>

        <label>Area</label>
        <select name="area" required>
            <option value="">Select area</option>
            <?php foreach ($areaOptions as $areaOption): ?>
                <option value="<?= htmlspecialchars($areaOption) ?>"><?= htmlspecialchars($areaOption) ?></option>
            <?php endforeach; ?>
        </select>

        <label>House Type</label>
        <select name="room_type" required>
            <option>Single</option>
            <option>Bedsitter</option>
            <option>One Bedroom</option>
            <option>Two Bedroom</option>
            <option>Hostel</option>
        </select>

        <label>Price (KES)</label>
        <input type="number" name="price" required>

        <label>Upload cover Image</label>
        <input type="file" name="house_image" accept="image/*" data-max-size="5242880" required>

        <label>Upload other Images</label>
        <input type="file" name="gallery_images[]" accept="image/*" data-max-files="5" data-max-size="5242880" multiple >


        <button type="button" class="toggle-btn" onclick="toggleDetails()">
    Utilities
</button>

<div id="moreDetails" class="hidden-section">

    <label class="custom-checkbox">
  <input type="checkbox" name="electricity_available" value="1">
    <span class="checkmark"></span>
  Electricity Available
</label>

<label class="custom-checkbox">
  <input type="checkbox" name="electricity_covered" value="1">
  <span class="checkmark"></span>
  Electricity Covered in Rent
</label>

<label class="custom-checkbox">
  <input type="checkbox" name="token_meter" value="1">
  <span class="checkmark"></span>
  Token Meter Available
</label>

<label class="custom-checkbox">
  <input type="checkbox" name="water_available" value="1">
  <span class="checkmark"></span>
  Water Available
</label>

<label class="custom-checkbox">
  <input type="checkbox" name="water_covered" value="1">
  <span class="checkmark"></span>
  Water Covered in Rent
</label>
<label class="custom-checkbox">
  <input type="checkbox" name="internet_available" value="1">
  <span class="checkmark"></span>
  Internet Available
</label>

<label class="custom-checkbox">
  <input type="checkbox" name="wifi_extra_payment" value="1">
  <span class="checkmark"></span>
  Internet Requires Extra Payment
</label>
<label class="custom-checkbox">
  <input type="checkbox" name="hot_shower" value="1">
  <span class="checkmark"></span>
  Hot Shower Available
</label>

<label class="custom-checkbox">
  <input type="checkbox" name="shared_toilet" value="1">
  <span class="checkmark"></span>
  Shared Toilet Available
</label>
<label class="custom-checkbox">
  <input type="checkbox" name="shared_water_point" value="1">
  <span class="checkmark"></span>
  Shared Water Point Available
</label>
<label>Other Descriptions</label>
        <input type="text" name="description" required>
         <button type="submit">Save House</button>


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