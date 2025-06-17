<?php
session_start();
require 'config.php';   

// Handle actions: approve request (change status to 'solved')
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = [];
    $request_id = intval($_POST['request_id']);
    $action = $_POST['action'];

    if ($action === 'approve') {
        $sql = "UPDATE contact_requests SET status = 'solved' WHERE id = $request_id";
        if ($conn->query($sql) === TRUE) {
            $response['status'] = 'success';
            $response['message'] = 'Request marked as solved!';
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Error updating request. Please try again.';
        }
    }

    echo json_encode($response);
    exit;
}

// Fetch all pending requests from the database
$sql = "SELECT * FROM contact_requests WHERE status = 'pending' ORDER BY submitted_at ASC";
$result = $conn->query($sql);
$requests = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Contact Requests</title>
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

        .card-container {
            position:absolute;
            left:25%;
            flex-wrap: wrap;
            justify-content: space-around;
            
            
        }

        .card {
            height:100%;
            width: 500px;
            margin: 15px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            border:1px solid black;
        }
        

        .card-header {
            background-color:rgba(255, 191, 0, 0.941);;
            color: white;
            text-align: center;
            padding: 10px;
            font-weight: bold;
            font-size: 18px;
        }

        .card-body {
            padding: 15px;
            color: black;
            background-color:white;
            font-size:20px;
            text-align:center;
        }
        .isue {
            padding-bottom: 10px; /* Ensure some space at the bottom */
            word-wrap: break-word; /* To break long words and prevent overflow */
            white-space: normal; /* Allow wrapping within the container */
}
        .card-body p {
            margin: 10px 0;
        }

        .card-actions {
            display: flex;
            justify-content: center;
            padding: 10px 15px;
            background-color: white;
            
        }

        .action-btn {
            padding: 8px 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            color: #fff;
        }

        .approve-btn {
            background-color: #28a745;
        }

        .action-btn:hover {
            opacity: 0.9;
        }
        h1{
            text-align: center; 
            color: #e9b10a; 
            margin:20px 95px 0px 0px;
        }

           /* General adjustments for mobile layout */
@media (max-width: 768px) {
    h1{
        position:absolute;
            margin:0px 0px 0px 300px;
        }
        .card {
         
            margin: 15% 50% 0% 0%;
        }
        .card-container{
            top: 11%;
            left: 15%;
        }
        .main 
        {
         
            width:200%;
        }  
}
    </style>
</head>
<body>    <!-- =============== Navigation ================ -->
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

                

                <div class="user">
                    <img src="assets/imgs/customer01.png" alt="">
                </div>

            </div>
        

    <h1>Issue Section</h1>
    <div class="card-container" id="cardContainer">
        <?php foreach ($requests as $request): ?>
            <div class="card" data-id="<?= $request['id'] ?>">
                <div class="card-header">
                    <?= htmlspecialchars($request['name']) ?>
                </div>
                <div class="card-body">
                    <p><strong>Phone:</strong> <?= htmlspecialchars($request['phone']) ?></p>
                    <p class="isue"><strong>Issue:</strong> <?= htmlspecialchars($request['issue']) ?></p>
                    <p><strong>Status:</strong> <?= htmlspecialchars($request['status'] ?? 'Pending') ?></p>
                </div>
                <div class="card-actions">
                    <button class="action-btn approve-btn" onclick="handleAction(<?= $request['id'] ?>, 'approve')">Mark as Solved</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function handleAction(requestId, action) {
            const actionText = action === 'approve' ? 'mark this request as solved' : '';
            const actionConfirm = action === 'approve' ? 'Yes, mark as solved!' : '';
            const actionSuccess = action === 'approve' ? 'Request marked as solved!' : '';

            // Show confirmation popup
            Swal.fire({
                title: `Are you sure?`,
                text: `You are about to ${actionText}. This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: actionConfirm
            }).then((result) => {
                if (result.isConfirmed) {
                    // Send POST request to handle the action
                    fetch(window.location.href, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({ request_id: requestId, action: action })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: actionSuccess,
                                confirmButtonColor: '#3085d6',
                                timer: 2000
                            }).then(() => {
                                window.location.reload(); // Refresh the page to show updated data
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: data.message,
                                confirmButtonColor: '#d33',
                            });
                        }
                    })
                    .catch(() => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops!',
                            text: 'An unexpected error occurred. Please try again.',
                        });
                    });
                }
            });
        }

        // Prevent form resubmission on page reload
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
    </div>
    </div>
    <!-- =========== Scripts =========  -->
<script src="assets/js/main.js"></script>
<!-- ====== ionicons ======= -->
<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

</body>
</html>
