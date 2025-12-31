<?php
require_once "../db_connect.php";

$query = "SELECT houses.*, full_name AS landlord_name 
          FROM houses 
          JOIN landlords ON houses.landlord_id = landlords.landlord_id
          ORDER BY houses.house_id DESC";

$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Housing Finder</title>
    <link rel="stylesheet" href="../../public/assets/styles.css">
</head>

<body>
    <header>
        <h1>Campus Housing Finder</h1>
              </header>
<nav>
<ul>
<li><a href="index.html">🏠 Home</a></li>
  <li><div class="dropdown">
    <input type="checkbox" id="areas-toggle">
    <label for="areas-toggle" class="dropbtn">📍 Areas ▾</label>

    <div class="dropdown-content">
        <a href="areas/bagik.html">Bagik</a>
        <a href="areas/njukiri.html">Njukiri</a>
        <a href="areas/kangaru.html">Kangaru</a>
        <a href="areas/kamiu.html">Kamiu</a>
        <a href="areas/gakwegori.html">Gakwegori</a>
        <a href="areas/kayole.html">Kayole</a>
        <a href="areas/leaders.html">Leaders</a>
        <a href="areas/springvalley.html">spring valley</a>
    </div></li>
    <li><a href="about.html">👤 About</a></li>
    <li><a href="contact.html">☎ Contact</a></li>
    <li><a href="../landlord/landlord_dashboard.php">My Houses</a></li>
    <li><a href="favorites.html">❤ Favourites</a></li>
    </ul>
</nav>
    <!-- HERO SECTION -->
    <section class="hero">
        <h2>Find the best rooms around your university</h2>
        <p>Search, compare, and book rooms with ease.</p>
    </section>

    <!-- SEARCH + FILTERS -->
    <section class="search-area">
        <input type="text" id="searchInput" placeholder="Search by area or house name...">
        
        <select id="filterPrice">
            <option value="">Price Range</option>
            <option value="0-3000">0 - 3000</option>
            <option value="3000-5000">3000 - 5000</option>
            <option value="5000-8000">5000 - 8000</option>
        </select>

        <select id="filterType">
            <option value="">Room Type</option>
            <option value="single">Single</option>
            <option value="bedsitter">Bedsitter</option>
            <option value="onebed">1 Bedroom</option>
<option value="twobed">2 Bedroom</option>
        </select>
    </section>
<h2>Available Houses</h2>

<div class="houses-container">
<?php while ($house = $result->fetch_assoc()): ?>
    <div class="house-card">
        <img src="<?php echo $house['image_path']; ?>" alt="House">
        
        <h3><?php echo htmlspecialchars($house['house_name']); ?></h3>

        <p><strong>Area:</strong> <?php echo $house['area']; ?></p>
        <p><strong>Type:</strong> <?php echo $house['room_type']; ?></p>
        <p><strong>Price:</strong> KES <?php echo $house['price']; ?></p>

        <p><strong>Landlord:</strong> <?php echo htmlspecialchars($house['landlord_name']); ?></p>

        <a href="../landlord/rooms.php?house_id=<?php echo $house['house_id']; ?>" class="btn">
            View Details
        </a>
    </div>
<?php endwhile; ?>
</div>
    <script src="assets/script.js"></script>
</body>
</html>