<?php
session_start();
require 'config.php'; // Include your database connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id']; // Get the user_id from session
    $account_name = $_POST['account_name'];
    $account_number = $_POST['account_number'];
    $ifsc_code = $_POST['ifsc_code'];
    $mobile_number = $_POST['mobile_number'];
    $bank_name = $_POST['bank_name']; // Get the bank name from the form

    // Basic validation
    if (empty($account_name) || empty($account_number) || empty($ifsc_code) || empty($mobile_number) || empty($bank_name)) {
        $_SESSION['message'] = "All fields are required.";
        $_SESSION['alertType'] = "error";
        header("Location: withdrawal_form.php");
        exit();
    }

    // Check if the user already has account details (check if the user_id exists in the 'users' table)
    $query_check = "SELECT * FROM users WHERE user_id = ?";
    if ($stmt_check = $conn->prepare($query_check)) {
        $stmt_check->bind_param("i", $user_id);
        $stmt_check->execute();
        $result = $stmt_check->get_result();

        if ($result->num_rows > 0) {
            // If the record exists, update the existing user account details
            $query_update = "UPDATE users SET account_name = ?, account_number = ?, ifsc_code = ?, mobile_number = ?, bank_name = ? WHERE user_id = ?";
            if ($stmt_update = $conn->prepare($query_update)) {
                $stmt_update->bind_param("sssssi", $account_name, $account_number, $ifsc_code, $mobile_number, $bank_name, $user_id);
                if ($stmt_update->execute()) {
                    $_SESSION['message'] = "Account details updated successfully!";
                    $_SESSION['alertType'] = "success";
                } else {
                    $_SESSION['message'] = "Error updating account details: " . $stmt_update->error;
                    $_SESSION['alertType'] = "error";
                }
                $stmt_update->close();
            }
        } else {
            // If the record does not exist, insert a new record into the 'users' table
            $query_insert = "INSERT INTO users (user_id, account_name, account_number, ifsc_code, mobile_number, bank_name) 
                             VALUES (?, ?, ?, ?, ?, ?)";
            if ($stmt_insert = $conn->prepare($query_insert)) {
                $stmt_insert->bind_param("isssss", $user_id, $account_name, $account_number, $ifsc_code, $mobile_number, $bank_name);
                if ($stmt_insert->execute()) {
                    $_SESSION['message'] = "Account details submitted successfully!";
                    $_SESSION['alertType'] = "success";
                } else {
                    $_SESSION['message'] = "Error inserting account details: " . $stmt_insert->error;
                    $_SESSION['alertType'] = "error";
                }
                $stmt_insert->close();
            }
        }

        $stmt_check->close();
    } else {
        $_SESSION['message'] = "Error checking user details: " . $conn->error;
        $_SESSION['alertType'] = "error";
    }

    // Redirect after the operation
    header("Location:user_withdrawal.php");
    exit();
}
?>
