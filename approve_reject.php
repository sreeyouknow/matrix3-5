<?php
session_start();
include('config.php');

// Check database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle the form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize user inputs
    $request_id = isset($_POST['request_id']) ? (int) $_POST['request_id'] : 0;  // Ensure it's an integer
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    // Check if action is either 'approve' or 'reject'
    if ($action !== 'approve' && $action !== 'reject') {
        echo "Invalid action specified.";
        exit;
    }

    // Set the status and approval date based on the action
    $status = ($action == 'approve') ? 'approved' : 'rejected';
    $approved_at = ($action == 'approve') ? date('Y-m-d H:i:s') : NULL;

    // Prepare SQL query to update the status and approval date
    $query = "UPDATE withdrawal_requests SET status=?, approved_at=? WHERE request_id=?";
    $stmt = $conn->prepare($query);

    // Bind parameters to the SQL query
    $stmt->bind_param('ssi', $status, $approved_at, $request_id);

    // Execute the query and check for success
    if ($stmt->execute()) {
        echo "Request $status successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the prepared statement and database connection
    $stmt->close();
    $conn->close();
}
?>
