<?php
session_start();
include 'config.php'; // Database connection

$user_id = $_SESSION['user_id'];

// Fetch current values for comparison and validation
$sql = "SELECT email FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Retrieve updated information from form
$email = $_POST['email'];
$phone = $_POST['phone'];
$address = $_POST['address'];
$username = $_POST['username'];
$password = $_POST['password'] ? password_hash($_POST['password'], PASSWORD_BCRYPT) : null;

// SQL to update fields (email, phone, address, password if provided)
$sql = "UPDATE users SET email = ?, phone = ?, address = ?";
$params = [$email, $phone, $address];
$types = "sss";

if ($password) {
    $sql .= ", password = ?";
    $types .= "s";
    $params[] = $password;
}

$sql .= " WHERE user_id = ?";
$types .= "i";
$params[] = $user_id;

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    echo "Profile updated successfully.";
} else {
    echo "Error updating profile: " . $conn->error;
}
?>
