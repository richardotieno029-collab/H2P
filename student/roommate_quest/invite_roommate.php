<?php
require_once "auth_student.php";
require_once "../../includes/risk_engine.php";
include "../../includes/toast.php";

$student_id = $_SESSION['user_id'];

/* fetch student info */

$stmt = $conn->prepare("
SELECT full_name,email,phone,profile_image
FROM students
WHERE id = ?
");

$stmt->bind_param("i",$student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();


/* HANDLE FORM SUBMISSION */

if($_SERVER["REQUEST_METHOD"] == "POST"){

$gender = $_POST['gender'];
$age = $_POST['age'];
$year = $_POST['year'];
$religion = $_POST['religion'];
$allergies = $_POST['allergies'];
$disability = $_POST['disability'];
$likes = $_POST['likes'];
$dislikes = $_POST['dislikes'];

$area = $_POST['area'];
$room_type = $_POST['room_type'];
$rent = $_POST['rent'];


/* ROOM PHOTO UPLOAD */

$room_photo = "";

if(!empty($_FILES['room_photo']['name'])){

$target_dir = "../../uploads/";
$filename = time() . "_" . basename($_FILES["room_photo"]["name"]);
$target_file = $target_dir . $filename;

move_uploaded_file($_FILES["room_photo"]["tmp_name"],$target_file);

$room_photo = $filename;

}
//delete old matches from roommate_matches for this host
$stmt = $conn->prepare("
DELETE FROM roommate_matches
WHERE host_id=? OR guest_id=?
");

$stmt->bind_param("ii",$student_id,$student_id);
$stmt->execute();



/* INSERT HOST LISTING */

$stmt = $conn->prepare("
INSERT INTO roommate_hosts
(student_id,name,gender,age,year_of_study,religion,allergies,disability,likes,dislikes,profile_photo,area,room_type,rent,room_photo)
VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
");

$stmt->bind_param(
"ississsssssssis",
$student_id,
$student['full_name'],
$gender,
$age,
$year,
$religion,
$allergies,
$disability,
$likes,
$dislikes,
$student['profile_image'],
$area,
$room_type,
$rent,
$room_photo
);

$stmt->execute();

// Log activity
$user_type = 'student';
$user_id   = $student_id;
$ip        = $_SERVER['REMOTE_ADDR'];

$log = $conn->prepare("
    INSERT INTO activity_logs (user_type, user_id, action, ip_address)
    VALUES (?, ?, ?, ?)
");

$action = 'CREATE_ROOMMATE_LISTING';
$log->bind_param("siss", $user_type, $user_id, $action, $ip);
$log->execute();

// Spam check - rapid create/delete listing
$check = $conn->prepare("
    SELECT COUNT(*) as total
    FROM activity_logs
    WHERE user_type='student'
      AND user_id=?
      AND action IN ('CREATE_ROOMMATE_LISTING','DELETE_ROOMMATE_LISTING')
      AND created_at > UTC_TIMESTAMP() - INTERVAL 2 MINUTE
");
$check->bind_param("i", $user_id);
$check->execute();
$count = $check->get_result()->fetch_assoc()['total'];

if ($count >= 3) {
    addRisk($conn, $user_type, $user_id, 15);

    $existing = $conn->prepare("
        SELECT id FROM spam_flags
        WHERE user_type='student'
          AND user_id=?
          AND reason='Suspicious rapid listing activity'
          AND created_at > UTC_TIMESTAMP() - INTERVAL 10 MINUTE
    ");
    $existing->bind_param("i", $user_id);
    $existing->execute();

    if ($existing->get_result()->num_rows == 0) {
        $flag = $conn->prepare("
            INSERT INTO spam_flags (user_type, user_id, reason, severity)
            VALUES ('student', ?, 'Suspicious rapid listing activity', 'medium')
        ");
        $flag->bind_param("i", $user_id);
        $flag->execute();
    }
}

$_SESSION['toast'] = [
    'type' => 'success',
    'message' => 'Listing successfully created.'
];

header("Location: roommate_quest.php");
exit();

}
?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invite Roommate</title>

<link rel="stylesheet" href="../../includes/assets/css/styles.css">
<link rel="stylesheet" href="roommate.css">

</head>

<body>

<a href="roommate_quest.php" class="back-btn">←</a>

<div class="rq-form">

<h2>Invite a Roommate</h2>

<form method="POST" enctype="multipart/form-data">

<h3>Your Details</h3>

<label>Gender</label>
<select name="gender" required>
<option value="">Select</option>
<option>Male</option>
<option>Female</option>
<option>Other</option>
</select>

<label>Age</label>
<input type="number" name="age" required>

<label>Year of Study</label>
<select name="year" required>
<option value="">Select</option>
<option>Year 1</option>
<option>Year 2</option>
<option>Year 3</option>
<option>Year 4</option>
<option>Graduated</option>
<option>Other</option>
</select>

<label>Religion</label>
<select name="religion" required>
<option value="">Select</option>
    <option>Christian</option>
    <option>Muslim</option>
    <option>Hindi</option>
    <option>Budhist</option>
    <option>Other</option>
</select>

<label>Allergies(if any)</label>
<input type="text" name="allergies" placeholder="e.g milk, perfumes, pollen...">

<label>Disability(if any)</label>
<input type="text" name="disability" placeholder="e.g short sighted, physically handicapped...">

<label>Likes(Optional)</label>
<textarea name="likes" placeholder="List your likes...e.g gaming, dancing"></textarea>

<label>Dislikes(Optional)</label>
<textarea name="dislikes" placeholder="List your dislikes...e.g drugs, loud music"></textarea>


<h3>Room Details</h3>

<label>Area</label>
<select name="area" required>
<option value="">Select</option>
<option>Bagik</option>
<option>Gakwegori</option>
<option>Spring Valley</option>
<option>Kamiu</option>
<option>Kangaru</option>
<option>Kayole</option>
<option>Njukiri</option>
<option>Leaders</option>
<option>Perez</option>
<option>Town</option>
<option>Other</option>
</select>


<label>Room Type</label>
<select name="room_type">
<option>Single</option>
<option>Bedsitter</option>
<option>One Bedroom</option>
<option>Two Bedroom</option>
<option>Hostel</option>
</select>

<label>Rent Per Month (KES)</label>
<input type="number" name="rent" required>

<label>Room Photo</label>
<input type="file" name="room_photo">

<button type="submit" class="rq-submit">
Create Listing
</button>

</form>

</div>

</body>
</html>