<?php
session_start();
include 'config.php'; // Include your database connection file

// Initialize message variable for feedback
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = $_POST['phone'];
    $password = $_POST['password'];

    // Sanitize user input
    $phone = htmlspecialchars($phone);
    $password = htmlspecialchars($password);

    // Check if the user exists and determine their role (user/admin) and status
    $query = "SELECT user_id, password, role, status FROM users WHERE phone = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Bind the result to variables
        $stmt->bind_result($user_id, $hashed_password, $role, $status);
        $stmt->fetch();

        // Check if the user's account status is approved
        if ($status === 'approved') {
            // Verify the password
            if (password_verify($password, $hashed_password)) {
                // Successful login, store user information in session
                $_SESSION['user_id'] = $user_id;
                $_SESSION['phone'] = $phone;
                $_SESSION['role'] = $role;

                // Redirect based on user role
                if ($role === 'admin') {
                    header("Location: admin_dashboard.php");
                    exit();
                } else {
                    header("Location: user_dashboard.php");
                    exit();
                }
            } else {
                // Incorrect password
                $_SESSION['message'] = "Incorrect password.";
            }
        } else if ($status === 'pending') {
            // Account is pending: redirect to a pending approval page
            header("Location: pending_page.php");
            exit();
        } else if ($status === 'rejected') {
            // Account is rejected
            $_SESSION['message'] = "Your account has been rejected. Please contact support.";
        }
    } else {
        // User not found
        $_SESSION['message'] = "No account found with that phone number.";
    }

    // Close the statement and connection after login check
    $stmt->close();
}


// Redirect back to login page to show message
header("Location: login.php");
exit();
?>
