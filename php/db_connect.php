<?php
$host = "localhost";
$user = "root"; 
$pass = ""; 
$db = "h2p"; 

$conn = mysqli_connect($host, $user, $pass, $db);
//auto expire on idle student
$conn->query("
    UPDATE rooms r
    JOIN bookings b ON r.id = b.room_id
    SET 
        b.status = 'expired',
        r.status = 'vacant'
    WHERE 
        b.status = 'approved'
        AND b.approved_expires_at < NOW()
");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>