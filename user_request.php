<?php
session_start();
include('config.php');

// Ensure the user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle form submission for requesting join
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Update user's status to 'pending'
    $query = "UPDATE users SET status = 'pending' WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        $success_message = "Your request has been submitted and is pending approval.";
    } else {
        $error_message = "There was an error submitting your request.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Join Request</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <div class="container">
        <h1>Request to Join Dashboard</h1>

        <!-- Display success or error message -->
        <?php if (isset($success_message)): ?>
            <div class="message success"><?php echo $success_message; ?></div>
        <?php elseif (isset($error_message)): ?>
            <div class="message error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Request Form -->
        <form method="POST" action="">
            <button type="submit">Submit Request</button>
        </form>
    </div>

</body>
</html>
