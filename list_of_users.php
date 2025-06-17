<?php
session_start();
include('config.php');
// Ensure the admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$admin_id = $_SESSION['user_id'];

// Handle search query
$search_query = '';
$search_phone = '';
if (isset($_POST['search_phone'])) {
    $search_phone = $_POST['search_phone'];
    $search_query = " WHERE phone LIKE ?";
    $search_phone = "%$search_phone%";
}

// Fetch all users or users by phone number if searching
$user_query = "SELECT * FROM users Where role='user'" . $search_query;
$stmt = $conn->prepare($user_query);
if ($search_phone) {
    $stmt->bind_param("s", $search_phone);
}
$stmt->execute();
$users_result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

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
            width: 70px;

        }

        .navigation ul 
        {
            position: absolute;
            top: 0;
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
            left: 90px;
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
/*table.. */
.container-13 {
    top: 10%;
    left: 6%;
    position: absolute;
    max-width: 100%;
    margin: 20px auto;
    padding: 10px 60px 0px 60px;
    background-color: #ffcd38;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
    overflow-x: auto; /* Enable horizontal scrolling */
    white-space: nowrap; /* Prevent content from wrapping */
}


        h1 {
            text-align: center;
            color: white;
        }
        .card {
            margin: 20px 0;
            padding: 30px 100px 10px 100px;
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 10px;
            width: 301%;
        }
        .card h2{
    color: #ffcd38;
    font-size: 36px;
    margin-bottom: 1%;
        
        }
        table {
    border-collapse: collapse;
    width: max-content; /* Adjust table width to fit content */
}
        table, th, td {
            border: 1px solid #ddd;
            font-size:20px;
            font-weight: 600;
        }
        th, td {
            padding: 25px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
/* General styles for mobile devices */
@media (max-width: 1024px) {
    .main {
        width: calc(100% - 100px);
        left: 70px;
    }

    .topbar {
        height: 60px;
        padding: 0 10px;
    }

    .search {
        width: 100%;
        margin: 10px 0;
    }

    .toggle {
        font-size: 2rem;
    }

    .container-13 {
        padding: 15px;
    }

    .card {
        width: 150%; /* Adjust width for smaller devices */
        padding: 15px;
    }

    th, td {
        font-size: 16px;
        padding: 15px;
    }
}

/* For tablets and medium devices */
@media (max-width: 768px) {
    .navigation {
        width: 80px;
    }

    .main {
        width: calc(100% - 80px);
        left: 80px;
    }

    .search {
        width: 90%;
    }

    .container-13 {
        padding: 10px;
    }

    .card {
        width: 120%; /* Adjust to fit smaller screens */
    }

    th, td {
        font-size: 14px;
        padding: 10px;
    }
}

/* For small mobile devices */
@media (max-width: 480px) {
    .navigation {
        width: 60px;
    }

    .main {
        width: calc(100% - 60px);
        left: 60px;
    }

    .topbar {
        margin: 0;
        padding: 0 5px;
    }

    .search {
        width: 100%;
        margin: 5px 0;
    }

    .container-13 {
        padding: 5px;
    }

    .card {
        width: 589%;
        padding: 10px;
    }

    table {
        width: 100%;
        font-size: 12px;
    }

    th, td {
        font-size: 12px;
        padding: 5px;
    }

    .card h2 {
        font-size: 24px;
        margin-bottom: 1%;
    }
}

/* For very small devices (extra small phones) */
@media (max-width: 320px) {
    .navigation {
        width: 50px;
    }

    .main {
        width: calc(100% - 50px);
        left: 50px;
    }

    .search {
        width: 100%;
        font-size: 14px;
    }

    .card h2 {
        font-size: 20px;
    }

    table, th, td {
        font-size: 10px;
        padding: 3px;
    }

    .container-13 {
        padding: 5px;
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

        <!-- ========================= Main ==================== -->
        <div class="main">
            <div class="topbar">
                <div class="toggle">
                    <ion-icon name="menu-outline"></ion-icon>
                </div>

                <div class="search">
                    <label>
                    <form method="POST" action="">
                    <input type="text" name="search_phone" placeholder="Search by phone number" value="<?php echo isset($_POST['search_phone']) ? htmlspecialchars($_POST['search_phone']) : ''; ?>">
                        <ion-icon name="search-outline"></ion-icon>
                        </form>
                    </label>
                </div>
                

                <div class="user">
                    <img src="assets/imgs/customer01.png" alt="">
                </div>

            </div>
     

<div class="container-13">
        <h1>Welcome Admin</h1>

        <!-- User Details Table -->
        <div class="card">
            <h2>User Details</h2>
           <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Stage</th>
                        <th>Level</th>
                        <th>Members Referred</th>
                        <th>Earnings</th>
                        <th>Bank Name</th>
                        <th>Account Holder Name</th>
                        <th>Account Number</th>
                        <th>IFSC Code</th>
                        <th>Mobile number (bank)</th>
                        <th>Refferal Code</th>
                    </tr>
                </thead>
                <tbody>
                <?php 
                    $serial_number = 1; 
                    while ($user = $users_result->fetch_assoc()): ?>
                    <tr>
                            <td><?php echo $serial_number++; ?></td> 
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['phone']); ?></td>
                            <td><?php echo htmlspecialchars($user['address']); ?></td>
                            <td><?php echo htmlspecialchars($user['current_stage']); ?></td>
                            <td><?php echo htmlspecialchars($user['current_level']); ?></td>
                            <td><?php echo htmlspecialchars($user['members_referred']); ?></td>
                            <td><?php echo htmlspecialchars($user['total_earned']); ?></td>
                            <td><?php echo htmlspecialchars($user['bank_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['account_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['account_number']); ?></td>
                            <td><?php echo htmlspecialchars($user['ifsc_code']); ?></td>
                            <td><?php echo htmlspecialchars($user['mobile_number']); ?></td>
                            <td><?php echo htmlspecialchars($user['referral_code']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

   </div>
</div>
<!-- =========== Scripts =========  -->
<script src="assets/js/main.js"></script>
<!-- ====== ionicons ======= -->
<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

</body>
</html>