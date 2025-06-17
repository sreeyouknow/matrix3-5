<?php
session_start();
include('config.php');

// Ensure the admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Join Requests</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
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

        .recentOrders {
            position:absolute;
            width: 72%;
            margin: 8% 0% 0% 15%;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead tr {
            background-color: rgba(255, 191, 0, 0.941);
            color: white;
        }

        th, td {
            text-align: center;
            font-size: 18px; 
            padding: 8px;    
            border: 1px solid #ddd;
        }

        td {
            text-align: center;
            font-size: 16px; 
            padding: 8px;
            font-weight: bold;    
            border: 1px solid #ddd;
        }

        /* Buttons */
        .actions button {
            display: inline; 
            width: 45%;    
            height: 40px;   
            font-size: 14px; 
            margin-bottom: 5px;
            border-radius: 6px;
        }

        .approve-btn {
            background-color: #28a745;
            color: white;
            border: none;
        }

        .reject-btn {
            background-color: #dc3545;
            color: white;
            border: none;
        }

        .approve-btn:hover {
            background-color: #218838;
        }

        .reject-btn:hover {
            background-color: #c82333;
        }

        /* Responsive styles */
        @media (max-width: 768px) {
            .recentOrders{
                width: 100%;
            }
            .main{
                width: 100%;
            }
            th, td {
                font-size: 14px;
                padding: 6px;
            }

            .actions button {
                width: 120px;
                height: 25px;
                font-size: 13px;
                border-radius: 4px;
            }
        }

        @media (max-width: 480px) {
            .recentOrders {
                padding: 10px;
            }

            th, td {
                font-size: 12px;
                padding: 5px;
            }

            .actions button {
                width: 100%;
                height: 25px;
                font-size: 12px;
                border-radius: 4px;
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
                <div class="user">
                    <img src="assets/imgs/customer01.png" alt="">
                </div>
            </div>
       

        <div class="recentOrders">
        <div class="cardHeader">
            <h2>Pending Join Requests</h2>
        </div>
        <table>
            <thead>
                <tr>
                    <th>S.No</th>
                    <th>Username</th>
                    <th>Phone</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="requestTable">
                <!-- Data will be loaded dynamically via JavaScript -->
            </tbody>
        </table>
        </div>
    </div>
    </div>
    <script>
        // Load pending requests dynamically
        function loadPendingRequests() {
            $.ajax({
                url: "fetch_pending_requests.php",
                method: "GET",
                success: function(data) {
                    $('#requestTable').html(data);
                    addSerialNumbers(); // Call to add serial numbers after loading data
                }
            });
        }

        // Add Serial Numbers to each row
        function addSerialNumbers() {
            var rows = $('#requestTable tr');
            rows.each(function(index) {
                $(this).find('td').eq(0).text(index + 1); // Set S.No as the index of the row (1-based index)
            });
        }

// Handle approval or rejection
function handleRequest(action, userId) {
    Swal.fire({
        title: `Are you sure you want to ${action} this request?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes",
        cancelButtonText: "No"
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "handle_request.php",
                method: "POST",
                data: { action: action, user_id: userId },
                success: function(response) {
                    // Parse response to handle icons properly
                    const parsedResponse = typeof response === "string" ? JSON.parse(response) : response;
                    
                    if (parsedResponse.status) {
                        Swal.fire({
                            title: parsedResponse.message,
                            iconHtml: '<img src="assets/imgs/success_icon.png" style="width:50px; height:50px;" alt="Success">', // Replace with your success icon path
                            customClass: {
                                popup: 'swal2-success-popup'
                            }
                        });
                    } else {
                        Swal.fire({
                            title: parsedResponse.message,
                            iconHtml: '<img src="assets/imgs/error_icon.png" style="width:50px; height:50px;" alt="Error">', // Replace with your error icon path
                            customClass: {
                                popup: 'swal2-error-popup'
                            }
                        });
                    }
                    loadPendingRequests(); // Reload the requests after action
                },
                error: function() {
                    Swal.fire({
                        title: "Something went wrong!",
                        iconHtml: '<img src="error_icon.png" style="width:50px; height:50px;" alt="Error">', // Replace with your error icon path
                        customClass: {
                            popup: 'swal2-error-popup'
                        }
                    });
                }
            });
        }
    });
}

// Load requests when the page loads
$(document).ready(function() {
    loadPendingRequests();
});

    </script>
    <!-- =========== Scripts =========  -->
<script src="assets/js/main.js"></script>
<!-- ====== ionicons ======= -->
<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>
</html>
