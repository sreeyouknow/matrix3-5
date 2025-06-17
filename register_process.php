<?php
session_start();
include 'config.php'; // Database connection file

// Enable detailed error reporting for debugging
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Registration process
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form values
    $username = trim($_POST['username']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $dob = trim($_POST['dob']);
    $gender = trim($_POST['gender']); 
    $address = trim($_POST['address']);
    $pincode = trim($_POST['pincode']); // Get the pincode from the form
    $password = trim($_POST['password']);
    $referral_code = trim($_POST['referral_code']);

    // Validate required fields
    if (empty($username) || empty($phone) || empty($dob) || empty($address) || empty($password) || empty($referral_code) || empty($gender) || empty($pincode)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required and Referral Code is needed. If you don’t have a Referral Code, contact +916379686824.']);
        exit();
    }

    // Concatenate address and pincode
    $full_address = $address . ', ' . $pincode; // Concatenate address with pincode

    // Check if phone number is already registered
    $check_phone_query = "SELECT * FROM users WHERE phone = ?";
    $stmt = $conn->prepare($check_phone_query);
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'This phone number is already registered.']);
        exit();
    }

    // Handle referral code validation
    $referrer_id = null; // Default to NULL
    if (!empty($referral_code)) {
        $referral_query = "SELECT user_id, members_referred FROM users WHERE referral_code = ?";
        $stmt = $conn->prepare($referral_query);
        $stmt->bind_param("s", $referral_code);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($referrer_id, $members_referred);
            $stmt->fetch();

            // Check if referrer has already referred 5 people
            if ($members_referred >= 5) {
                echo json_encode(['status' => 'error', 'message' => 'The referrer has already referred 5 members.']);
                exit();
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid referral code.']);
            exit();
        }
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Generate unique referral code for the new user
    $new_referral_code = generateReferralCode($conn);

    // Insert the new user into the database
    $insert_query = "INSERT INTO users (username, phone, email, dob, gender, address, password, referral_code, referrer_id, role) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'user')";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("ssssssssi", $username, $phone, $email, $dob, $gender, $full_address, $hashed_password, $new_referral_code, $referrer_id);

    if ($stmt->execute()) {
        // Increment the referrer's referral count if a referrer exists
        if ($referrer_id) {
            incrementReferrals($referrer_id, $conn);
        }

        // Send success response
        echo json_encode(['status' => 'success', 'message' => 'Registration successful!']);
        exit();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error during registration. Please try again.']);
        exit();
    }
}

// Function to generate a unique referral code
function generateReferralCode($conn) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $referral_code = '';

    do {
        $referral_code = '';
        for ($i = 0; $i < 6; $i++) {
            $referral_code .= $characters[rand(0, strlen($characters) - 1)];
        }

        // Check for uniqueness in the database
        $query = "SELECT * FROM users WHERE referral_code = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $referral_code);
        $stmt->execute();
        $stmt->store_result();
    } while ($stmt->num_rows > 0);

    return $referral_code;
}

// Function to increment the referrer's referral count
function incrementReferrals($referrer_id, $conn) {
    $update_query = "UPDATE users SET members_referred = members_referred + 1 WHERE user_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("i", $referrer_id);
    $stmt->execute();
}
?>