<?php
session_start();
include 'config.php'; // Include database connection

if (isset($_GET['referral_code']) && !empty($_GET['referral_code'])) {
    $referral_code = $_GET['referral_code'];

    // Prepare the SQL query to check referral code
    $query = "SELECT user_id, members_referred FROM users WHERE referral_code = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $referral_code);
    $stmt->execute();
    $stmt->store_result();

    // Check if the referral code exists in the database
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($referrer_id, $members_referred);
        $stmt->fetch();

        // If referral code exists, check if the max referrals condition is met
        if ($members_referred >= 5) {
            echo "max_referrals";  // The referrer has already referred 5 members
        } else {
            echo "valid";  // Referral code is valid
        }
    } else {
        // Referral code is not present in the database
        echo "invalid";  
    }
    $stmt->close();
} else {
    // Referral code is not provided or is empty
    echo "invalid";  
}
?>
