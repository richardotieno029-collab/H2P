<?php
function addRisk($conn, $user_type, $user_id, $points) {

    $table = ($user_type == 'landlord') ? 'landlords' : 'students';

    // 1️⃣ Get current score
    $stmt = $conn->prepare("
        SELECT risk_score, last_risk_update, status
        FROM $table
        WHERE id=?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if (!$result) return;

    $currentScore = $result['risk_score'];
    $lastUpdate   = strtotime($result['last_risk_update']);
    $currentStatus = $result['status'];

    $now = time();

    // 2️⃣ Apply decay (1 point per hour)
    $hoursPassed = floor(($now - $lastUpdate) / 3600);
    $decayedScore = max(0, $currentScore - $hoursPassed);

    // 3️⃣ Add new points (CAP AT 100)
    $newScore = min(100, $decayedScore + $points);

    // 4️⃣ Update risk + timestamp
    $update = $conn->prepare("
        UPDATE $table
        SET risk_score=?, last_risk_update=UTC_TIMESTAMP()
        WHERE id=?
    ");
    $update->bind_param("ii", $newScore, $user_id);
    $update->execute();

    // 5️⃣ Flag if above 15
    if ($newScore >= 15) {
        $flag = $conn->prepare("
            INSERT INTO spam_flags (user_type, user_id, reason, severity)
            VALUES (?, ?, 'High cumulative risk score', 'high')
        ");
        $flag->bind_param("si", $user_type, $user_id);
        $flag->execute();
    }

    // 6️⃣ Auto Suspend at 50
    if ($newScore >= 50 && $currentStatus !== 'suspended') {

        $suspend = $conn->prepare("
            UPDATE $table
            SET status='suspended'
            WHERE id=?
        ");
        $suspend->bind_param("i", $user_id);
        $suspend->execute();

        $ip = $_SERVER['REMOTE_ADDR'];

        $log = $conn->prepare("
            INSERT INTO activity_logs (user_type, user_id, action, ip_address)
            VALUES (?, ?, 'AUTO_SUSPEND', ?)
        ");
        $log->bind_param("sis", $user_type, $user_id, $ip);
        $log->execute();
    }

    // 7️⃣ Auto Unsuspend if below 50
    if ($newScore < 50 && $currentStatus === 'suspended') {

        $restore = $conn->prepare("
            UPDATE $table
            SET status='active'
            WHERE id=?
        ");
        $restore->bind_param("i", $user_id);
        $restore->execute();

        $log = $conn->prepare("
            INSERT INTO activity_logs (user_type, user_id, action)
            VALUES (?, ?, 'AUTO_UNSUSPEND')
        ");
        $log->bind_param("si", $user_type, $user_id);
        $log->execute();
    }
}