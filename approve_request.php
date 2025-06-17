<?php
session_start();
include 'config.php'; // Include your database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];

    // Update the user's status to 'approved'
    $query = "UPDATE Users SET status = 'approved' WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    // Redirect back to the admin view requests page
    header("Location: admin_dashboard.php");
    exit();
}
?>
