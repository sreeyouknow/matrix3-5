<?php
include 'config.php'; // Include your database connection file

header('Content-Type: application/json'); // Set the content type to JSON

if (isset($_GET['referral_code'])) {
    $referral_code = $_GET['referral_code'];

    // Query to check if the referral code exists and get the associated username
    $query = "SELECT user_id, username FROM users WHERE referral_code = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $referral_code);
    $stmt->execute();
    $stmt->bind_result($referrer_id, $username);
    $stmt->fetch();
    $stmt->close();

    // Check if the referral code exists
    if ($referrer_id) {
        // Query to check the count of users who have used this referral code
        $countQuery = "SELECT COUNT(*) FROM users WHERE referrer_id = ?";
        $countStmt = $conn->prepare($countQuery);
        $countStmt->bind_param("i", $referrer_id);
        $countStmt->execute();
        $countStmt->bind_result($usedCount);
        $countStmt->fetch();
        $countStmt->close();

        // Check if the referral code has been used by 5 or more users
        if ($usedCount >= 5) {
            echo json_encode(['status' => 'invalid']); // Referral code has already been used by 5 or more members
        } else {
            echo json_encode(['status' => 'valid', 'username' => $username]); // Referral code is valid, return username
        }
    } else {
        echo json_encode(['status' => 'invalid']); // Referral code does not exist
    }
}
?>