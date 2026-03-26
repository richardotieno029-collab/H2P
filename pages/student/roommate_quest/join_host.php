<?php
require_once "auth_student.php";
require_once "../../includes/risk_engine.php";
include "../../includes/toast.php";

$student_id = $_SESSION['user_id'];

$host_id = intval($_GET['host_id']);

/* Get host details */

// Ensure the host listing is still active (not expired)
$stmt = $conn->prepare("
SELECT h.*, s.phone, s.email
FROM roommate_hosts h
JOIN students s ON h.student_id = s.id
WHERE h.host_id = ?
  AND h.status = 'OPEN'
  AND h.created_at > NOW() - INTERVAL 2 WEEK
");

$stmt->bind_param("i",$host_id);
$stmt->execute();

$host = $stmt->get_result()->fetch_assoc();

if (!$host) {
    echo "<p style='text-align:center;'>This listing is no longer available.</p>";
    exit();
}

/* HANDLE JOIN REQUEST */

if($_SERVER['REQUEST_METHOD'] == "POST"){

$gender = $_POST['gender'];
$age = $_POST['age'];
$year = $_POST['year'];
$religion = $_POST['religion'];
$allergies = $_POST['allergies'];
$disability = $_POST['disability'];
$likes = $_POST['likes'];
$dislikes = $_POST['dislikes'];


/* Cleanup stale requests older than 2 days */
$conn->query("UPDATE roommate_requests SET status='CANCELLED' WHERE status='PENDING' AND created_at < NOW() - INTERVAL 2 DAY");

/* check if already requested */

$check = $conn->prepare("
SELECT request_id 
FROM roommate_requests
WHERE host_id=? AND student_id=?
  AND status='PENDING'
");

$check->bind_param("ii",$host_id,$student_id);
$check->execute();

if($check->get_result()->num_rows > 0){

echo "<p style='color:red;text-align:center;'>You already sent a request.</p>";

}else{

$stmt = $conn->prepare("
INSERT INTO roommate_requests
(host_id,student_id,name,gender,age,year_of_study,religion,allergies,disability,likes,dislikes,profile_photo)
SELECT ?, id, full_name, ?, ?, ?, ?, ?, ?, ?, ?, profile_image
FROM students
WHERE id = ?
");

$stmt->bind_param(
"isssssssss",
$host_id,
$gender,
$age,
$year,
$religion,
$allergies,
$disability,
$likes,
$dislikes,
$student_id
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

        $action = 'SEND_ROOMMATE_REQUEST';
        $log->bind_param("siss", $user_type, $user_id, $action, $ip);
        $log->execute();

        // Spam check - rapid join/cancel requests
        $check = $conn->prepare("
            SELECT COUNT(*) as total
            FROM activity_logs
            WHERE user_type='student'
              AND user_id=?
              AND action IN ('SEND_ROOMMATE_REQUEST','CANCEL_ROOMMATE_REQUEST')
              AND created_at > NOW() - INTERVAL 2 MINUTE
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
                  AND reason='Suspicious rapid roommate request activity'
                  AND created_at > NOW() - INTERVAL 10 MINUTE
            ");
            $existing->bind_param("i", $user_id);
            $existing->execute();

            if ($existing->get_result()->num_rows == 0) {
                $flag = $conn->prepare("
                    INSERT INTO spam_flags (user_type, user_id, reason, severity)
                    VALUES ('student', ?, 'Suspicious rapid roommate request activity', 'medium')
                ");
                $flag->bind_param("i", $user_id);
                $flag->execute();
            }
        }

        // Notify host by email (non-blocking)
        require_once "../../includes/mailer.php";
        if (!empty($host['email'])) {
            $subject = "New roommate request for your listing";
            $body = "Hi {$host['name']},<br><br>" .
                "A student has sent a request to join your roommate listing. Please log in to review and respond.<br><br>" .
                "Thanks,<br>H2P Team";

            sendMailQuiet($host['email'], $host['name'], $subject, $body);
        }

        //display success message and redirect
$_SESSION['toast'] = [
'type' => 'success',
    'message' => 'Request sent. Pleae wait for the host to reply.'
];
header("Location: view_hosts.php");
exit();

}

}

?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Host</title>

<link rel="stylesheet" href="../../includes/assets/css/styles.css">
<link rel="stylesheet" href="roommate.css">

</head>

<body>

<a href="view_hosts.php" class="back-btn">←</a>

<div class="rq-form">

<h2>Join <?= htmlspecialchars($host['name']) ?></h2>

<p><strong>Area:</strong> <?= htmlspecialchars($host['area']) ?></p>
<p><strong>Room:</strong> <?= htmlspecialchars($host['room_type']) ?></p>
<p><strong>Rent:</strong> KES <?= number_format($host['rent']) ?></p>

<form method="POST">

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


<button class="rq-submit">
Send Join Request
</button>

</form>

</div>

</body>
</html>