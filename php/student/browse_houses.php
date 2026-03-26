<?php
require_once "auth_student.php";

// Mark notifications as read
$clear = "UPDATE notifications SET is_read = 1 
          WHERE user_id = ?";
$stmt = $conn->prepare($clear);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();

/* --------------------------
   FILTER LOGIC
---------------------------*/
$sql = "
SELECT 
    h.*,
    l.full_name AS landlord_name,

    /* TOTAL ROOMS */
    (SELECT COUNT(*) 
     FROM rooms r 
     WHERE r.house_id = h.house_id) AS total_rooms,

    /* AVAILABLE ROOMS */
    (SELECT COUNT(*) 
     FROM rooms r 
     WHERE r.house_id = h.house_id 
     AND r.status = 'vacant') AS available_rooms,

    /* VIEWS */
    (SELECT COUNT(*) 
     FROM house_views v 
     WHERE v.house_id = h.house_id) AS views,

    /* FAVOURITES */
    (SELECT COUNT(*) 
     FROM favourites f 
     WHERE f.house_id = h.house_id) AS favourites,

    /* PENDING BOOKINGS */
    CASE 
        WHEN EXISTS (
            SELECT 1
            FROM bookings b
            JOIN rooms r2 ON b.room_id = r2.id
            WHERE r2.house_id = h.house_id
              AND b.student_internal_id = ?
              AND b.status = 'pending'
        ) THEN 1 ELSE 0
    END AS has_pending

FROM houses h
JOIN landlords l ON h.landlord_id = l.id

WHERE h.status = 'active'
";
$types = "";

$params[] = $_SESSION['user_id'];
$types .= "i";


// Area filter
if (!empty($_GET['area'])) {
    $sql .= " AND h.area = ?";
    $params[] = $_GET['area'];
    $types .= "s";
}

// Room type filter
if (!empty($_GET['room_type'])) {
    $sql .= " AND h.room_type = ?";
    $params[] = $_GET['room_type'];
    $types .= "s";
}

// Price range filter
if (!empty($_GET['price_range'])) {
    [$minPrice, $maxPrice] = explode('-', $_GET['price_range']);

    if (is_numeric($minPrice)) {
        $sql .= " AND h.price >= ?";
        $params[] = (int) $minPrice;
        $types .= "i";
    }

    if (is_numeric($maxPrice)) {
        $sql .= " AND h.price <= ?";
        $params[] = (int) $maxPrice;
        $types .= "i";
    }
}

// Only available houses
if (!empty($_GET['vacant'])) {
    $sql .= " AND (
        SELECT COUNT(*) 
        FROM rooms r 
        WHERE r.house_id = h.house_id 
        AND r.status = 'vacant'
    ) > 0";
}

$sql .= " ORDER BY has_pending DESC, h.created_at DESC";


$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}


$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Houses</title>
    <link rel="stylesheet" href="../includes/assets/css/styles.css">
</head>
<body>
<?php include "../includes/toast.php"; ?>
<a href="dashboard.php" class="back-btn" title="Go back">
    ←
</a>
<h2>Browse Houses</h2>

<!-- --------------------------
     FILTERS
--------------------------- -->
<form method="GET" class="filters" onsubmit="return handleSubmit(this, 'Applying filters...')">
    <select name="area">
        <option value="">All Areas</option>
        <option value="Bagik" <?= (isset($_GET['area']) && $_GET['area'] === 'Bagik') ? 'selected' : '' ?>>Bagik</option>
        <option value="Gakwegori" <?= (isset($_GET['area']) && $_GET['area'] === 'Gakwegori') ? 'selected' : '' ?>>Gakwegori</option>
        <option value="Spring Valley" <?= (isset($_GET['area']) && $_GET['area'] === 'Spring Valley') ? 'selected' : '' ?>>Spring Valley</option>
        <option value="Kamiu" <?= (isset($_GET['area']) && $_GET['area'] === 'Kamiu') ? 'selected' : '' ?>>Kamiu</option>
        <option value="Kangaru" <?= (isset($_GET['area']) && $_GET['area'] === 'Kangaru') ? 'selected' : '' ?>>Kangaru</option>
        <option value="Kayole" <?= (isset($_GET['area']) && $_GET['area'] === 'Kayole') ? 'selected' : '' ?>>Kayole</option>
        <option value="Njukiri" <?= (isset($_GET['area']) && $_GET['area'] === 'Njukiri') ? 'selected' : '' ?>>Njukiri</option>
        <option value="Leaders" <?= (isset($_GET['area']) && $_GET['area'] === 'Leaders') ? 'selected' : '' ?>>Leaders</option>
        <option value="Perez" <?= (isset($_GET['area']) && $_GET['area'] === 'Perez') ? 'selected' : '' ?>>Perez</option>
        <option value="Town" <?= (isset($_GET['area']) && $_GET['area'] === 'Town') ? 'selected' : '' ?>>Town</option>
        <option value="Other" <?= (isset($_GET['area']) && $_GET['area'] === 'Other') ? 'selected' : '' ?>>Other</option>
    </select>

    <select name="room_type">
        <option value="">All Room Types</option>
        <option value="Single" <?= (isset($_GET['room_type']) && $_GET['room_type'] === 'Single') ? 'selected' : '' ?>>Single</option>
        <option value="Bedsitter" <?= (isset($_GET['room_type']) && $_GET['room_type'] === 'Bedsitter') ? 'selected' : '' ?>>Bedsitter</option>
        <option value="One Bedroom" <?= (isset($_GET['room_type']) && $_GET['room_type'] === 'One Bedroom') ? 'selected' : '' ?>>1 Bedroom</option>
        <option value="Hostel" <?= (isset($_GET['room_type']) && $_GET['room_type'] === 'Hostel') ? 'selected' : '' ?>>Hostel</option>
    </select>

    <select name="price_range">
        <option value="">Any Price</option>
        <option value="0-2000" <?= (isset($_GET['price_range']) && $_GET['price_range'] === '0-2000') ? 'selected' : '' ?>>0 - 2,000</option>
        <option value="2000-4000" <?= (isset($_GET['price_range']) && $_GET['price_range'] === '2000-4000') ? 'selected' : '' ?>>2,000 - 4,000</option>
        <option value="4000-6000" <?= (isset($_GET['price_range']) && $_GET['price_range'] === '4000-6000') ? 'selected' : '' ?>>4,000 - 6,000</option>
        <option value="6000-8000" <?= (isset($_GET['price_range']) && $_GET['price_range'] === '6000-8000') ? 'selected' : '' ?>>6,000 - 8,000</option>
        <option value="8000-10000" <?= (isset($_GET['price_range']) && $_GET['price_range'] === '8000-10000') ? 'selected' : '' ?>>8,000 - 10,000</option>
        <option value="10000-12000" <?= (isset($_GET['price_range']) && $_GET['price_range'] === '10000-12000') ? 'selected' : '' ?>>10,000 - 12,000</option>
        <option value="12000-14000" <?= (isset($_GET['price_range']) && $_GET['price_range'] === '12000-14000') ? 'selected' : '' ?>>12,000 - 14,000</option>
        <option value="14000-16000" <?= (isset($_GET['price_range']) && $_GET['price_range'] === '14000-16000') ? 'selected' : '' ?>>14,000 - 16,000</option>
        <option value="16000-18000" <?= (isset($_GET['price_range']) && $_GET['price_range'] === '16000-18000') ? 'selected' : '' ?>>16,000 - 18,000</option>
        <option value="18000-20000" <?= (isset($_GET['price_range']) && $_GET['price_range'] === '18000-20000') ? 'selected' : '' ?>>18,000 - 20,000</option>
    </select>

    <label>
        <input type="checkbox" name="vacant" value="1"
            <?php if (!empty($_GET['vacant'])) echo "checked"; ?>>
        With Vacancies Only
    </label>

<a href="browse_houses.php" class="clear-filters">Clear Filters</a>
</form>
<!-- --------------------------
     HOUSES LIST
--------------------------- -->
<div class="houses-container " id="housesContainer">
    <div id="housesContainer">
<!--Output cards here by ajax-->
    </div>
</div>

<button id="scrollTopBtn" class="scrollTopBtn" title="Go to top">↑</button>

<script>

const form = document.querySelector('.filters');
const container = document.getElementById('housesContainer');

/* LOAD INITIAL DATA */
window.addEventListener('DOMContentLoaded', loadHouses);

/* AUTO LOAD ON FILTER CHANGE */
form.addEventListener('change', loadHouses);

function loadHouses(){

    const formData = new FormData(form);
    const params = new URLSearchParams(formData).toString();

    container.innerHTML = `
<div class="loading">
    <div class="spinner"></div>
    <p>Loading houses...</p>
</div>
`;

    fetch("fetch_houses.php?" + params)
    .then(res => res.text())
    .then(data => {
        container.innerHTML = data;

        attachFavEvents(); // Re-attach listeners after loading new content
    });
}

//auto refresh every 60 seconds when user is idle on the page
let autoRefresh = true;

// pause when user interacts
document.addEventListener('scroll', () => {
    autoRefresh = false;
    setTimeout(() => autoRefresh = true, 60000); // resume after 30s
});

setInterval(() => {
    if(autoRefresh){
        loadHouses();
    }
}, 30000);


        /* RE-ATTACH FAVOURITE BUTTON LISTENERS */
        function attachFavEvents(){

document.querySelectorAll('.fav-btn').forEach(button => {

button.addEventListener('click', function(e){

e.preventDefault();
e.stopPropagation();

const houseId = this.dataset.houseId;
const btn = this;

fetch('../favourites/toggle_favourite.php', {
method: 'POST',
headers: {'Content-Type': 'application/x-www-form-urlencoded'},
body: 'house_id=' + houseId
})
.then(res => res.json())
.then(data => {

if(data.status === 'added'){
btn.classList.add('active');
}else{
btn.classList.remove('active');
}

});

});

});

}

</script>

<script src="../includes/assets/js/scroll_top.js"></script>
<script>
initScrollTop();
</script>

</body>
</html>