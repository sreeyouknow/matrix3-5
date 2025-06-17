<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the required POST variables are set
    if (isset($_POST['phone']) && isset($_POST['password'])) {
        $phone = $_POST['phone'];
        $password = $_POST['password'];

        // Query to verify user credentials using phone and password
        $query = "SELECT user_id, role FROM users WHERE phone = ? AND password = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $phone, $password); // Correct binding
        $stmt->execute();
        $stmt->bind_result($user_id, $role);
        $stmt->fetch();
        $stmt->close();

        if ($user_id) {
            // Set session variables
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $user_id;
            $_SESSION['role'] = $role;

            // Redirect based on role
            if ($role === 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: user_dashboard.php");
            }
            exit();
        } else {
            // Invalid credentials
            $_SESSION['popup_message'] = "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Login Failed',
                    text: 'Invalid phone number or password.'
                });
            </script>";
        }
    } else {
        $_SESSION['popup_message'] = "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Please provide both phone number and password.'
            });
        </script>";
    }
}

$popup_message = ''; // Variable to store the popup message

if (isset($_SESSION['popup_message'])) {
    echo $_SESSION['popup_message'];
    unset($_SESSION['popup_message']); // Clear session message after it's displayed
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['request_id']) && isset($_POST['action'])) {
    $request_id = (int)$_POST['request_id'];  // Ensure it's an integer
    $action = $_POST['action'];

    // Ensure action is either 'approve' or 'reject'
    if ($action !== 'approve' && $action !== 'reject') {
        echo "Invalid action specified.";
        exit;
    }

    // Fetch the withdrawal request details
    $query = "SELECT user_id, amount, total_earned FROM withdrawal_requests WHERE request_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $stmt->bind_result($user_id, $amount, $total_earned);
    $stmt->fetch();
    $stmt->close();

    // Set the status and approval date based on the action
    $status = ($action === 'approve') ? 'approved' : 'rejected';
    $approved_at = ($action === 'approve') ? date('Y-m-d H:i:s') : NULL;

    // Begin transaction to ensure data consistency
    $conn->begin_transaction();

    try {
        if ($action === 'approve') {
            // Deduct the requested amount from the user's total earned
            $update_user_query = "UPDATE users SET total_earned = '0' WHERE user_id = ?";
            $stmt = $conn->prepare($update_user_query);
            $stmt->bind_param("i", $user_id);  // Correct binding: 'd' for amount (decimal) and 'i' for user_id (integer)

            if ($stmt->execute()) {
                // Update the withdrawal request status to "approved"
                $update_request_query = "UPDATE withdrawal_requests SET status = ?, approved_at = ? WHERE request_id = ?";
                $stmt_update = $conn->prepare($update_request_query);
                $stmt_update->bind_param("ssi", $status, $approved_at, $request_id);
                $stmt_update->execute();
                $stmt_update->close();

                // Commit the transaction
                $conn->commit();

                // Set success message in session
                $_SESSION['popup_message'] = "<script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Request Approved',
                        text: 'The withdrawal request has been successfully approved.',
                        allowOutsideClick: false
                    });
                </script>";
            } else {
                throw new Exception("Failed to update user earnings.");
            }
        } else {
            // Update the withdrawal request status to "rejected"
            $update_request_query = "UPDATE withdrawal_requests SET status = ? WHERE request_id = ?";
            $stmt = $conn->prepare($update_request_query);
            $stmt->bind_param("si", $status, $request_id);
            $stmt->execute();
            $stmt->close();

            // Add the withdrawal amount back to user's total earnings
            $update_user_query = "UPDATE users SET total_earned = total_earned + ? WHERE user_id = ?";
            $stmt = $conn->prepare($update_user_query);
            $stmt->bind_param("ii", $amount, $user_id); // 'ii' for two integers
            $stmt->execute();
            $stmt->close();

            // Commit the transaction
            $conn->commit();

            // Set rejection message in session
            $_SESSION['popup_message'] = "<script>
                Swal.fire({
                    icon: 'info',
                    title: 'Request Rejected',
                    text: 'The withdrawal request has been rejected due to insufficient earnings.',
                    allowOutsideClick: false
                });
            </script>";
        }

        // Redirect to avoid form resubmission on page refresh
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch (Exception $e) {
        // Rollback the transaction in case of any errors
        $conn->rollback();
        $_SESSION['popup_message'] = "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while processing the request: " . $e->getMessage() . "' 
            });
        </script>";

        // Redirect to avoid form resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Handle the search for a user by username or phone number
if (isset($_POST['search_term'])) {
    $search_term = "%" . $_POST['search_term'] . "%";  // Add wildcards for LIKE search on strings

    // Adjust query to use LIKE for the phone field and exact match for user_id
    $query = "
    SELECT user_id, username, phone, current_stage, current_level, total_earned
    FROM users 
    WHERE (user_id LIKE ? OR phone LIKE ?)";
    
    // Prepare the statement
    $stmt = $conn->prepare($query);
    
    // Bind parameters: Use 's' for both user_id and phone since we are treating them as strings for LIKE
    $stmt->bind_param("ss", $search_term, $search_term);
    
    // Execute the statement
    $stmt->execute();
    
    // Get the result
    $result = $stmt->get_result();
    
    // Fetch results into an array
    while ($row = $result->fetch_assoc()) {
        $search_results[] = $row;
    }
    
    // Close the statement
    $stmt->close();
}

// Fetch all pending withdrawal requests
$query = "SELECT * FROM withdrawal_requests WHERE status = 'pending'";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
            /* =========== Google Fonts ============ */
                @import url("https://fonts.googleapis.com/css2?family=Ubuntu:wght@300;400;500;700&display=swap");

                /* =============== Globals ============== */
                * 
                {
                    font-family: "Ubuntu", sans-serif;
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }

                :root 
                {
                    --black: #000000;
                    --white: #fff;
                    --gray: #f5f5f5;
                    --black1: #222;
                    --black2: #999;
                }

                body 
                {
                    min-height: 100vh;
                    overflow-x: hidden;
                }

                .container 
                {
                    position: relative;
                    width: 100%;
                }

                /* =============== Navigation ================ */
                .navigation 
                {
                    position: fixed;
                    width: 250px;
                    border-radius: 0px 10px;
                    height: 100%;
                    background: rgba(255, 191, 0, 0.941);
                    transition: 0.5s;
                    overflow: hidden;
                }

                .navigation.active 
                {
                    width: 80px;
                }

                .navigation ul 
                {
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                }

                .navigation ul li 
                {
                    position: relative;
                    width: 100%;
                    list-style: none;
                    border-top-left-radius: 30px;
                    border-bottom-left-radius: 30px;
                }

                .navigation ul li:hover,
                .navigation ul li.hovered 
                {
                    background-color: var(--white);
                }

                .navigation ul li:nth-child(1)
                {
                    margin-bottom: 40px;
                    pointer-events: none;
                }

                .navigation ul li a 
                {
                    position: relative;
                    display: block;
                    width: 100%;
                    display: flex;
                    text-decoration: none;
                    color: var(--white);
                }

                .navigation ul li:hover a,
                .navigation ul li.hovered a 
                {
                    color: var(--black);
                }

                .navigation ul li a .icon 
                {
                    position: relative;
                    display: block;
                    min-width: 60px;
                    height: 60px;
                    line-height: 75px;
                    text-align: center;
                }

                .navigation ul li a .icon ion-icon 
                {
                    font-size: 1.75rem;
                    text-align: center;
                }
                .log-out-outline
                {
                    font-size: 1.75rem;
                    line-height: 175px;
                }

                .navigation ul li a .title 
                {
                    position: relative;
                    display: block;
                    padding: 0 10px;
                    height: 60px;
                    line-height: 60px;
                    text-align: start;
                    white-space: nowrap;
                }

                /* --------- curve outside ---------- */
                .navigation ul li:hover a::before,
                .navigation ul li.hovered a::before 
                {
                    content: "";
                    position: absolute;
                    right: 0;
                    top: -50px;
                    width: 50px;
                    height: 50px;
                    background-color: transparent;
                    border-radius: 50%;
                    box-shadow: 35px 35px 0 10px var(--white);
                    pointer-events: none;
                }

                .navigation ul li:hover a::after,
                .navigation ul li.hovered a::after 
                {
                    content: "";
                    position: absolute;
                    right: 0;
                    bottom: -50px;
                    width: 50px;
                    height: 50px;
                    background-color: transparent;
                    border-radius: 50%;
                    box-shadow: 35px -35px 0 10px var(--white);
                    pointer-events: none;
                }

                /* ===================== Main ===================== */
                .main 
                {
                    position: absolute;
                    width: calc(100% - 300px);
                    left: 250px;
                    min-height: 100vh;
                    background: var(--white);
                    transition: 0.5s;
                }

                .main.active 
                {
                    width: calc(100% - 80px);
                    left: 60px;
                }

                .topbar 
                {
                    width: 100%;
                    height: 60px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 0 10px;
                }

                .toggle 
                {
                    position: relative;
                    width: 60px;
                    height: 60px;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    font-size: 2.5rem;
                    cursor: pointer;
                }

                .search 
                {
                    position: relative;
                    width: 400px;
                    margin: 0 10px;
                }

                .search label 
                {
                    position: relative;
                    width: 100%;
                }

                .search label input 
                {
                    width: 100%;
                    height: 40px;
                    border-radius: 40px;
                    padding: 5px 20px;
                    padding-left: 35px;
                    font-size: 18px;
                    outline: none;
                    border: 1px solid var(--black2);
                }

                .search label ion-icon 
                {
                    position: absolute;
                    top: 10px;
                    left: 10px;
                    font-size: 1.2rem;
                }

                .user 
                {
                    position: relative;
                    width: 40px;
                    height: 40px;
                    border-radius: 50%;
                    overflow: hidden;
                    cursor: pointer;
                }

                .user img 
                {
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                }  
                .admin-dashboard {
                    position: absolute;
                    margin: 7% 0% 0% 2%;
                    width: 100%;
                    background: white;
                    padding: 20px;
                    border-radius: 10px;
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                }
                .admin-dashboard h2 {
                    text-align: center;
                    margin-bottom: 20px;
                    color: #333;
                    font-size: 24px;
                }
                .request-table, .search-results-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 20px;
                    text-align: left;
                }
                .request-table thead, .search-results-table thead {
                    background-color: #ffcd38;
                    color: white;
                }
                .request-table th, .request-table td, .search-results-table th, .search-results-table td {
                    padding: 15px 0px;
                    border-bottom: 1px solid #ddd;
                }
                .request-table tr:nth-child(even), .search-results-table tr:nth-child(even) {
                    background-color: #f9f9f9;
                }
                .status-pending {
                    color: #ff9800;
                    font-weight: bold;
                }
                .status-approved {
                    color: #4CAF50;
                    font-weight: bold;
                }
                .status-rejected {
                    color: #f44336;
                    font-weight: bold;
                }
                .btn-approve, .btn-reject {
                    padding: 8px 16px;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                    font-size: 14px;
                }
                .btn-approve {
                    background-color: #4CAF50;
                    color: white;
                }
                .btn-reject {
                    background-color: #f44336;
                    color: white;
                }
                .btn-approve:hover {
                    background-color: #45a049;
                }
                .btn-reject:hover {
                    background-color: #e53935;
                }


                @media (max-width: 480px) {
                /* Navigation */
                .navigation {
                    width: 180px;
                }

                .navigation.active {
                    width: 60px;
                }

                /* Main Content */
                .main {
                    width: calc(100% - 190px);
                    left: 180px;
                }

                .main.active {
                    width: calc(100% - 60px);
                    left: 60px;
                }
                .topbar 
                {
                width: 190%;
                }

                /* Dashboard */
                .admin-dashboard {
                    margin: 5% auto;
                    width: 455%;
                    padding: 10px;
                }

                .admin-dashboard h2 {
                    font-size: 18px;
                }

                /* Tables */
                .request-table,
                .search-results-table {
                    font-size: 12px;
                }

                .request-table th,
                .request-table td,
                .search-results-table th,
                .search-results-table td {
                    padding: 8px;
                }

                .btn-approve,
                .btn-reject {
                    font-size: 10px;
                    padding: 5px 10px;
                }
                }
        </style>
</head>

<body>

    <!-- =============== Navigation ================ -->
    <div class="container">
        <div class="navigation">
            <ul>
                <li>
                    <a href="#">
                        <span class="icon">
                            <img src="assets/imgs/logo.jpg" alt="BRAND" style="width: 70px; margin:30% 0% 0% 120%; height: 70px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); align-items : center;">
                        </span>
                    </a>
                </li>

                <li>
                    <a href="admin_dashboard.php">
                        <span class="icon">
                            <ion-icon name="home-outline"></ion-icon>
                        </span>
                        <span class="title">Dashboard</span>
                    </a>
                </li>

                <li>
                    <a href="admin_user_approval.php">
                        <span class="icon">
                            <ion-icon name="person-add"></ion-icon>
                        </span>
                        <span class="title">Accept Request</span>
                    </a>
                </li>

                <li>
                    <a href="list_of_users.php">
                        <span class="icon">
                            <ion-icon name="people-outline"></ion-icon>
                        </span>
                        <span class="title">Customers</span>
                    </a>
                </li>

                <li>
                    <a href="admin_withdrawals.php">
                        <span class="icon">
                            <ion-icon name="cash-outline"></ion-icon>
                        </span>
                        <span class="title">Withdrawal</span>
                    </a>
                </li>

                <li>
                    <a href="admin_view_profile.php">
                        <span class="icon">
                            <ion-icon name="person-outline"></ion-icon>
                        </span>
                        <span class="title">Profile</span>
                    </a>
                </li>

                <li>
                    <a href="admin_requests.php">
                        <span class="icon">
                            <ion-icon name="chatbox"></ion-icon>
                        </span>
                        <span class="title">Issues and Requests</span>
                    </a>
                </li>

                <li>
                    <a href="logout.php">
                        <span class="icon">
                            <ion-icon name="log-out-outline"></ion-icon>
                        </span>
                        <span class="title">Sign Out</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main">
            <div class="topbar">
                <div class="toggle">
                    <ion-icon name="menu-outline"></ion-icon>
                </div>

                <div class="search">
                    <label>
                        <form method="POST">
                            <input type="text" name="search_term" placeholder="Search by username or phone number" required>
                        </form>
                    </label>
                </div>

                <div class="user">
                    <img src="assets/imgs/customer01.png" alt="">
                </div>
            </div>

            <?php
            // Display the success or error popup if set
            if (isset($_SESSION['popup_message'])) {
                echo $_SESSION['popup_message'];
                unset($_SESSION['popup_message']);
            }
            ?>

            <div class="admin-dashboard">
                <h2>Manage Requests</h2>
                <!-- Pending Requests Table -->
                <h3>Pending Requests</h3>
                <table class="request-table">
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>User ID</th>
                            <th>Bank Name</th>
                            <th>Account Number</th>
                            <th>Account Name</th>
                            <th>IFSC Code</th>
                            <th>Mobile Number</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['request_id']; ?></td>
                                    <td><?php echo $row['user_id']; ?></td>
                                    <td><?php echo $row['bank_name']; ?></td>
                                    <td><?php echo $row['account_number']; ?></td>
                                    <td><?php echo $row['account_name']; ?></td>
                                    <td><?php echo $row['ifsc_code']; ?></td>
                                    <td><?php echo $row['mobile_number']; ?></td>
                                    <td><?php echo $row['amount']; ?></td>
                                    <td class="status-<?php echo strtolower($row['status']); ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </td>
                                    <td>
                                        <?php if ($row['status'] == 'pending'): ?>
                                            <form method="POST" class="action-form">
                                                <button type="button" onclick="confirmAction('approve', <?php echo $row['request_id']; ?>)" class="btn-approve">Approve</button>
                                                <button type="button" onclick="confirmAction('reject', <?php echo $row['request_id']; ?>)" class="btn-reject">Reject</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10">No pending requests found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Search Results Table -->
                <?php if (!empty($search_results)): ?>
                    <h3>Search Results</h3>
                    <table class="search-results-table">
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Request ID</th>
                                <th>Amount</th>
                                <th >Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($search_results as $user): ?>
                                <tr>
                                    <td><?php echo $user['user_id']; ?></td>
                                    <td><?php echo $user['username']; ?></td>
                                    <td><?php echo $user['phone']; ?></td>
                                    <td><?php echo $user['current_stage']; ?></td>
                                    <td><?php echo $user['current_level']; ?></td>
                                    <td><?php echo $user['total_earned']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No results found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Other Scripts -->
    <script src="assets/js/main.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <script>
        function confirmAction(action, requestId) {
            const actionText = action === 'approve' ? 'approve' : 'reject';
            Swal.fire({
                title: `Are you sure you want to ${actionText} this request?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'No'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Create a hidden form to submit
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = `
                        <input type="hidden" name="request_id" value="${requestId}">
                        <input type="hidden" name="action" value="${action}">
                    `;
                    document.body.appendChild(form);
                    form.submit(); // Submit the form
                }
            });
        }
    </script>
</body>

</html>