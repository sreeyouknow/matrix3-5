<?php
include 'config.php'; // Include your database connection file
if (isset($_GET['phone'])) {
    $phone = $_GET['phone'];

    // Query to check if the phone number exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE phone = ?");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['status' => 'exists']); // Phone already registered
    } else {
        echo json_encode(['status' => 'available']); // Phone is available
    }
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
$conn->close();
?>

