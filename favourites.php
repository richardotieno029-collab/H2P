<?php
session_start();
$conn = new mysqli("localhost", "root", "", "h2p");

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}

$student_id = $_SESSION['student_id'];

$query = "
SELECT rooms.* 
FROM favourites 
JOIN rooms ON favourites.room_id = rooms.room_id
WHERE favourites.student_id='$student_id'
";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
<title>My Favourites</title>
</head>
<body>

<h2>My Favourite Houses</h2>

<?php while ($row = $result->fetch_assoc()): ?>
    <div style="padding:10px; border:1px solid #ccc; margin-bottom:10px;">
        <img src="uploads/<?php echo $row['image_path']; ?>" width="200"><br>
        <strong><?php echo $row['description']; ?></strong><br>
        Ksh <?php echo $row['price']; ?><br>
        Area: <?php echo $row['area_id']; ?><br>

        <a href="add_favourite.php?room_id=<?php echo $row['room_id']; ?>">
            ❤ Remove Favourite
        </a>
    </div>
<?php endwhile; ?>

</body>
</html>