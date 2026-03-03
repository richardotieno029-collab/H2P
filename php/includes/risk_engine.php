<?php
function addRisk($conn, $user_type, $user_id, $points) {

    $table = ($user_type == 'landlord') ? 'landlords' : 'students';

    // Step 1: Get current score and last update
    $stmt = $conn->prepare("
        SELECT risk_score, last_risk_update 
        FROM $table 
        WHERE id=?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    $currentScore = $result['risk_score'];
    $lastUpdate   = strtotime($result['last_risk_update']);
    $now          = time();

    // Step 2: Calculate decay (1 point per hour)
    $hoursPassed = floor(($now - $lastUpdate) / 3600);
    $decayedScore = max(0, $currentScore - $hoursPassed);

    // Step 3: Add new points
    $newScore = min(100, $decayedScore + $points);

    // Step 4: Update database
    $update = $conn->prepare("
        UPDATE $table 
        SET risk_score=?, last_risk_update=NOW()
        WHERE id=?
    ");
    $update->bind_param("ii", $newScore, $user_id);
    $update->execute();
}
//flag high risk
if ($newScore >= 15) {

    $flag = $conn->prepare("
        INSERT INTO spam_flags (user_type, user_id, reason, severity)
        VALUES (?, ?, 'High cumulative risk score', 'high')
    ");
    $flag->bind_param("si", $user_type, $user_id);
    $flag->execute();
}