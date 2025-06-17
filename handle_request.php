<?php
include('config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => false, 'message' => 'Only POST requests are allowed']);
    exit();
}

// Extract and validate input
$user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
$action = strtolower(trim($_POST['action'] ?? ''));

// Validate user_id and action
if (!$user_id || !in_array($action, ['approve', 'reject'], true)) {
    http_response_code(400);
    echo json_encode(['status' => false, 'message' => 'Invalid user ID or action']);
    exit();
}

if ($action === 'approve') {
    // Approve user
    $query = "UPDATE users SET status = 'approved' WHERE user_id = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        error_log("Database error: " . $conn->error);
        http_response_code(500);
        echo json_encode(['status' => false, 'message' => 'Internal server error']);
        exit();
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['status' => true, 'message' => 'Approval successful']);
    } else {
        echo json_encode(['status' => false, 'message' => 'Approval failed']);
    }

    $stmt->close();

} elseif ($action === 'reject') {
    // Reject user
    $conn->begin_transaction();

    try {
        // Fetch the referrer_id of the user
        $query = "SELECT referrer_id FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }

        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($referrer_id);
        $stmt->fetch();
        $stmt->close();

        // If the user has a referrer, decrement their members_referred count
        if ($referrer_id) {
            $update_referrer_query = "UPDATE users SET members_referred = members_referred - 1 WHERE user_id = ?";
            $stmt = $conn->prepare($update_referrer_query);

            if (!$stmt) {
                throw new Exception("Database error: " . $conn->error);
            }

            $stmt->bind_param("i", $referrer_id);
            $stmt->execute();
            $stmt->close();
        }

        // Delete the user's record
        $delete_query = "DELETE FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($delete_query);

        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }

        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $conn->commit();
            echo json_encode(['status' => true, 'message' => 'Rejection successful and user deleted']);
        } else {
            throw new Exception("User deletion failed");
        }

        $stmt->close();

    } catch (Exception $e) {
        $conn->rollback();
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => false, 'message' => 'Internal server error']);
    }
}

$conn->close();
?>
