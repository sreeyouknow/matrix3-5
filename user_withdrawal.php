<?php
// Include database configuration
include('config.php');

// Start session
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login if session is not active
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";
$alertType = "";

// Fetch total earned amount
$query = "SELECT total_earned, bank_name, account_name, account_number, ifsc_code, mobile_number FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();

// Bind all the columns to variables
$stmt->bind_result($total_earned, $bank_name, $account_name, $account_number, $ifsc_code, $mobile_number);

// Fetch the result
$stmt->fetch();

// Close the statement
$stmt->close();

// Handle withdrawal submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = $total_earned; // Withdraw total earned amount

    if ($amount >= 10 && $amount <= 50000) {
        // Check for duplicate request
        if (!isset($_SESSION['last_request']) || $_SESSION['last_request'] != $amount) {
            
            // Check if total earnings are sufficient
            if ($amount > 0) {
                // Prepare the SQL query
                $query = "INSERT INTO withdrawal_requests (user_id, bank_name, account_name, account_number, ifsc_code, mobile_number, amount, status, request_date) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("isssssi", $user_id, $bank_name, $account_name, $account_number, $ifsc_code, $mobile_number, $amount);

                // Execute the query
                if ($stmt->execute()) {
                    // Mark the request as submitted successfully
                    $_SESSION['last_request'] = $amount;
                    $_SESSION['message'] = "Withdrawal request submitted successfully for ₹" . number_format($amount, 2) . "!";
                    $_SESSION['alertType'] = "success";

                    // Reset total earned after withdrawal
                    $updateQuery = "UPDATE users SET total_earned = total_earned - ? WHERE user_id = ?";
                    $updateStmt = $conn->prepare($updateQuery);
                    $updateStmt->bind_param("ii", $amount, $user_id);
                    $updateStmt->execute();
                    $updateStmt->close();
                } else {
                    $_SESSION['message'] = "Failed to submit withdrawal request. Please try again.";
                    $_SESSION['alertType'] = "error";
                }
                $stmt->close();
            } else {
                $_SESSION['message'] = "Insufficient balance. Unable to process withdrawal.";
                $_SESSION['alertType'] = "error";
            }
        } else {
            $_SESSION['message'] = "Duplicate request detected. Please avoid submitting the same request repeatedly.";
            $_SESSION['alertType'] = "warning";
        }
    } else {
        $_SESSION['message'] = "Invalid amount. Total earnings must be between ₹10 and ₹50,000.";
        $_SESSION['alertType'] = "error";
    }

    // Redirect to prevent form resubmission on refresh
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch withdrawal history
$query = "SELECT amount, status, request_date FROM withdrawal_requests WHERE user_id = ? ORDER BY request_date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$withdrawals = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Function to enable editing of input fields
        function enableEditing() {
            var inputs = document.querySelectorAll('.textbox.readonly');
            inputs.forEach(function(input) {
                input.removeAttribute('readonly');
            });
            document.getElementById('submitBtn').style.display = 'inline-block'; // Show the submit button
            document.getElementById('editBtn').style.display = 'none'; // Hide the edit button
        }
    </script>
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
                width: 69px;
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
                left: 80px;
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
                top: 0;
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

                .container-1 {
                position: absolute;


                background-color: #ffffff;
                margin: 2% 0% 0% 11%;
                border-radius: 10px;

                box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
                display: flex;
                gap: px;

                }
                h3 {
                position: absolute;
                font-size: 40px;
                font-weight: 400;
                color: #333;
                margin: -18% 0% 0% -7%;
                }
                .draw{
                width: 50%;
                padding: 10px;
                background: #fff;
                border-radius: 5px;
                border:#000000 1px solid;


                }

                .draw h2 {
                text-align: center;
                color: var(--black);
                margin-bottom: 20px;
                }

                .draw input[type="text"] {
                width: 100%;
                height: 40px;
                padding: 10px;
                font-size: 1rem;
                margin-bottom: 15px;
                border-radius: 5px;
                border: 1px solid #ddd;
                outline: none;
                }

                .draw h4 {
                text-align: center;
                font-size: 1rem;
                font-weight: 500;
                color: #333;
                margin-top: 20px;
                }

                .draw h4 span {
                font-weight: 600;
                color: red;
                }

                .table-container h4 {
                text-align: center;
                font-size: 1rem;
                font-weight: 400;
                color: #333;
                margin-top: 20px;
                }

                .table-container h4 span {
                font-weight: 600;
                color: green;
                }

                /* Table Styles */
                .table-container {
                padding: 10px;
                background: #fff;
                border-radius: 5px;
                width: 70%;
                border:#000000 1px solid;
                }

                table {
                width: 100%;

                margin-top: 20px;
                }

                table, th, td {
                border: 1px solid #ddd;
                }

                th, td {
                padding: 12px;
                text-align: center;
                }

                th {
                background-color: #f8f9fa;
                }

                .status {
                font-weight: 600;
                color: #e9b10a;
                }
                .status1 {
                font-weight: 600;
                color: green;
                }
                .status2 {
                font-weight: 600;
                color: red;
                }
                /* Styling the submit button */
                .btn-submit {
                width: 100%;
                padding: 12px;
                font-size: 1.1rem;
                font-weight: 500;
                color: #fff;
                background-color: #e9b10a;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                text-align: center;
                transition: background-color 0.3s ease;
                }

                /* Button hover effect */
                .btn-submit:hover {
                background-color: #c79a0d;
                }

                /* Focus effect for accessibility */
                .btn-submit:focus {
                outline: none;
                box-shadow: 0 0 5px rgba(233, 177, 10, 0.7);
                }

                .btn-submit1 {
                padding: 10px 20px;
                margin: 10px 0;
                background-color: rgba(255, 191, 0, 0.941);
                color: white;
                border: 1px solid rgba(255, 191, 0, 0.941);
                border-radius: 5px;
                cursor: pointer;
                font-size:12px;
                }
                .btn-submit1:hover {
                background-color: white;
                color:rgba(255, 191, 0, 0.941);
                }
                .readonly {
                background-color: #f0f0f0;
                border: none;
                }
                .btn-edit {
                padding: 10px 20px;
                margin: 10px 0;
                background-color:rgba(255, 191, 0, 0.941);
                color: white;
                border: 1px solid rgba(255, 191, 0, 0.941);
                border-radius: 5px;
                cursor: pointer;
                }
                .btn-edit:hover {
                background-color: white;
                color:rgba(255, 191, 0, 0.941);
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
                    <a href="user_dashboard.php">
                        <span class="icon">
                            <ion-icon name="home-outline"></ion-icon>
                        </span>
                        <span class="title">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="invited_user.php">
                        <span class="icon">
                            <ion-icon name="people-outline"></ion-icon>
                        </span>
                        <span class="title">Customers</span>
                    </a>
                </li>
                <li>
                    <a href="user_withdrawal.php">
                        <span class="icon">
                            <ion-icon name="cash-outline"></ion-icon>
                        </span>
                        <span class="title">Withdrawal</span>
                    </a>
                </li>
                <li>
                    <a href="profile_user.php">
                        <span class="icon">
                            <ion-icon name="person-outline"></ion-icon>
                        </span>
                        <span class="title">Profile</span>
                    </a>
                </li>
                <li>
                    <a href="help.php">
                        <span class="icon">
                            <ion-icon name="help-outline"></ion-icon>
                        </span>
                        <span class="title">Help</span>
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
                <div class="toggle"><ion-icon name="menu-outline"></ion-icon></div>
                <div class="user"><img src="assets/imgs/customer01.png" alt="User"></div>
            </div>

                 <!-- Withdrawal Section -->
                 <div class="container-1">
                <!-- Withdrawal Form -->
                <div class="draw">
                    <h2>Request Withdrawal</h2>
                    <h4><span>Note:</span> Please fill in the details to make a withdrawal request. Once you have filled in the details, please verify them before submitting or requesting.</h4><br>
                    <div class="Accountdetails">
                        <form action="process_account_submission.php" method="POST" onsubmit="return validateForm();">
                            <label for="account_name">Account Holder Name:</label>
                            <input type="text" id="account_name" name="account_name" value="<?php echo $account_name; ?>" class="textbox readonly" pattern="^[a-zA-Z\s]{1,30}$" readonly required><br>
                            
                            <label for="bank_name">Bank Name:</label>
                            <input type="text" id="bank_name" name="bank_name" value="<?php echo $bank_name; ?>" class="textbox readonly" readonly required><br>

                            <label for="account_number">Account Number:</label>
                            <input type="text" id="account_number" name="account_number" value="<?php echo $account_number; ?>" class="textbox readonly" readonly required><br>

                            <label for="ifsc_code">IFSC Code:</label>
                            <input type="text" id="ifsc_code" name="ifsc_code" value="<?php echo $ifsc_code; ?>" class="textbox readonly" readonly required pattern="^[A-Z]{4}0[A-Z0-9]{6}$"><br>

                            <label for="mobile_number">Mobile Number:</label>
                            <input type="text" id="mobile_number" name="mobile_number" value="<?php echo $mobile_number; ?>" class="textbox readonly" readonly required pattern="^[6-9]\d{9}$" maxlength="10"><br>

                            <!-- Edit Button -->
                            <button type="button" id="editBtn" class="btn-edit" onclick="enableEditing()">Edit</button>

                            <!-- Submit Button -->
                            <button type="submit" id="submitBtn" class="btn-submit1" style="display: none;">Submit</button>
                        </form>
                    </div>
                </div>

                <!-- Withdrawal History Table -->
                <div class="table-container">
                    <form action="" method="POST">
                        <input type="hidden" id="amount" name="amount" value="<?php echo $total_earned; ?>">
                        <input type="submit" value="Click here to Withdraw (₹<?php echo number_format($total_earned, 2); ?>)" class="btn-submit">
                    </form>
                    <h4><span>Note:</span> The amount will be sent to your bank account within 1-3 working days.</h4><br>
                    <table>
                        <thead>
                            <tr>
                                <th>Amount (₹)</th>
                                <th>Status</th>
                                <th>Request Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($withdrawals as $withdrawal): ?>
                                <tr>
                                    <td><?php echo number_format($withdrawal['amount'], 2); ?></td>
                                    <td><?php echo ucfirst($withdrawal['status']); ?></td>
                                    <td><?php echo date('d-m-Y H:i:s', strtotime($withdrawal['request_date'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- SweetAlert for Notifications -->
    <?php if (isset($_SESSION['message'])): ?>
        <script>
            Swal.fire({
                title: "<?php echo $_SESSION['message']; ?>",
                icon: "<?php echo $_SESSION['alertType']; ?>",
                confirmButtonText: 'OK'
            });
        </script>
        <?php unset($_SESSION['message']); unset($_SESSION['alertType']); ?>
    <?php endif; ?>
    <!-- Other Scripts -->
    <script src="assets/js/main.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>
</html>
