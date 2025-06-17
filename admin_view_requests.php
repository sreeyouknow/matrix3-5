<?php
// Include your database connection
include('config.php');

// Queries for dashboard data
$sql = "SELECT COUNT(*) AS total_users FROM users";
$result = $conn->query($sql);
$total_users = $result->fetch_assoc()['total_users'];

$sql_week = "SELECT COUNT(*) AS users_this_week FROM users WHERE WEEK(created_at) = WEEK(CURRENT_DATE) AND YEAR(created_at) = YEAR(CURRENT_DATE)";
$result_week = $conn->query($sql_week);
$users_this_week = $result_week->fetch_assoc()['users_this_week'];

$sql_pending_requests = "SELECT COUNT(*) AS pending_requests FROM contact_requests WHERE status = 'pending'";
$result_pending = $conn->query($sql_pending_requests);
$pending_requests = $result_pending->fetch_assoc()['pending_requests'];

$sql_graph = "SELECT DATE(created_at) AS join_date, COUNT(*) AS users_joined FROM users GROUP BY DATE(created_at) ORDER BY join_date";
$result_graph = $conn->query($sql_graph);
$labels = [];
$data = [];
while ($row = $result_graph->fetch_assoc()) {
    $labels[] = $row['join_date'];
    $data[] = $row['users_joined'];
}

$sql_referrals = "SELECT username, phone, members_referred FROM users WHERE members_referred < 5";
$result_referrals = $conn->query($sql_referrals);
$referral_users = [];
while ($row = $result_referrals->fetch_assoc()) {
    $referral_users[] = $row;
} 
// Assuming $conn is your database connection
$sql_graph = "SELECT DATE(created_at) AS join_date, COUNT(*) AS users_joined FROM users GROUP BY DATE(created_at) ORDER BY join_date";
$result_graph = $conn->query($sql_graph);

// Prepare the labels and data for the graph
$labels = [];
$data = [];

while ($row = $result_graph->fetch_assoc()) {
    $labels[] = $row['join_date'];
    $data[] = $row['users_joined'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        /* Card Box */
        .cardBox {
            display: flex;
            gap: 20px;
            margin: 20px;
        }
        .card {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            flex: 1;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .iconBx {
            font-size: 36px;
            color: #007bff;
        }
        .numbers {
            font-size: 32px;
            font-weight: bold;
        }
        .cardName {
            font-size: 14px;
            color: #6c757d;
        }
        /* Modern Table Styling */
        table { 
    position: fixed;
    width: 30%;
    left: 70%;
    top: 0%;
    border-collapse: collapse;
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

table th,
table td {
    padding: 16px;
    text-align: center;
    font-size: 15px;
    color: #333;
    border-bottom: 1px solid #e4e7eb;
}

table th {
    background-color: #f4f7fc;
    font-weight: 600;
    color: #555;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

table tr:nth-child(even) {
    background-color: #f9fafb;
}

table tr:hover {
    background-color: rgba(255, 191, 0, 0.941);
    color: black;
    transition: background-color 0.3s;
}

table td:first-child,
table th:first-child {
    padding-left: 20px;
}

table td:last-child,
table th:last-child {
    padding-right: 20px;
}

table td {
    font-weight: 400;
}

table td a {
    text-decoration: none;
    color: #007bff;
    font-weight: 500;
    transition: color 0.3s;
}

table td a:hover {
    color: #0056b3;
}

table .action-btns {
    display: flex;
    gap: 10px;
    justify-content: flex-start;
}

table .action-btns a {
    padding: 8px 12px;
    background-color: #4caf50;
    color: white;
    border-radius: 5px;
    font-size: 14px;
    text-align: center;
    transition: background-color 0.3s, transform 0.2s;
}

table .action-btns a:hover {
    background-color: #45a049;
    transform: translateY(-2px);
}

table .action-btns a.delete {
    background-color: #f44336;
}

table .action-btns a.delete:hover { 
    background-color: #e53935;
}

.pagination {
    text-align: center;
    margin-top: 200px;
    margin-left: 60%;
}

.pagination a {
    text-decoration: none;
    padding: 8px 12px;
    margin: 0 5px;
    background-color: #f4f7fc;
    color: #007bff;
    border-radius: 5px;
}

.pagination a:hover {
    background-color: #007bff;
    color: white;
}

    h3{ 
    margin-top:-37%;
    margin-left:13%;
    font-size: 24px;
    text-align: center;
}

/* Chart Container */
.chart-container {
    width: 60%;
    margin-left:6%;
    margin-top: 30px;
    background-color: #f9f9f9;  
    border-radius: 10px;
    box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
}

/* Make Canvas responsive */
canvas {
    margin-left:-8%;
    width: 100%; !important;
    height: 400px; !important;
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
                    <a href="matrix.php">
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

        <!-- ========================= Main ==================== -->
        <div class="main">
            <div class="topbar">
                <div class="toggle">
                    <ion-icon name="menu-outline"></ion-icon>
                </div>

                <div class="search">
                    <label>
                        <input type="text" placeholder="Search here">
                        <ion-icon name="search-outline"></ion-icon>
                    </label>
                </div>

                <div class="user">
                    <img src="assets/imgs/customer01.png" alt="">
                </div>
            </div>

            <!-- Dashboard Content -->
            <div class="cardBox">
                <div class="card">
                    <div>
                        <div class="numbers"><?php echo $total_users; ?></div>
                        <div class="cardName">Total Users</div>
                    </div>
                    <div class="iconBx">
                        <ion-icon name="person-outline"></ion-icon>
                    </div>
                </div>
                <div class="card">
                    <div>
                        <div class="numbers"><?php echo $users_this_week; ?></div>
                        <div class="cardName">Users This Week</div>
                    </div>
                    <div class="iconBx">
                        <ion-icon name="calendar-outline"></ion-icon>
                    </div>
                </div>
                <div class="card">
                    <div>
                        <div class="numbers"><?php echo $pending_requests; ?></div>
                        <div class="cardName">Pending Requests</div>
                    </div>
                    <div class="iconBx">
                        <ion-icon name="mail-outline"></ion-icon>
                    </div>
                </div>
            </div>

            <!-- Graph container -->
             <div class="chart-container">
                <div style="width: 80%; margin: 0 auto;">
                    <canvas id="usersGraph" style="height: 300px;"></canvas>
                </div>
            </div>
            <!-- Referral Users Table -->
            <h3>Referral Users</h3>
<table id="referralTable">
    <thead>
        <tr>
            <th><input type="checkbox" id="selectAll" onclick="toggleSelectAll()"> Select All</th>
            <th>Username</th>
            <th>Phone</th>
            <th>Members Referred</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $per_page = 5; // Number of items per page
        $page = isset($_GET['page']) ? $_GET['page'] : 1; // Current page, default to 1
        $start_from = ($page - 1) * $per_page;

        // Fetch users with pagination logic
        $sql = "SELECT * FROM users  WHERE members_referred < 5 LIMIT $start_from, $per_page";
        $result = $conn->query($sql);
        
        // Fetch all users for displaying
        $referral_users = $result->fetch_all(MYSQLI_ASSOC);

        foreach ($referral_users as $user): 
        ?>
            <tr>
                <td><input type="checkbox" class="selectUser" onclick="strikeThrough(this)"></td>
                <td><?php echo $user['username']; ?></td>
                <td><?php echo $user['phone']; ?></td>
                <td><?php echo $user['members_referred']; ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Pagination Links -->
<div class="pagination">
    <?php
    $sql_total = "SELECT COUNT(*) FROM users"; // Total users count
    $result_total = $conn->query($sql_total);
    $total_users = $result_total->fetch_row()[0];
    $total_pages = ceil($total_users / $per_page);

    for ($i = 1; $i <= $total_pages; $i++) {
        echo "<a href='?page=$i'>$i</a> ";
    }
    ?>
</div>

        </div>
    </div>
    
    <!-- =========== Scripts =========  -->
    <script src="assets/js/main.js"></script>
 <script>
        var ctx = document.getElementById('usersGraph').getContext('2d');
        var usersGraph = new Chart(ctx, {
            type: 'line', // Line chart
            data: {
                labels: <?php echo json_encode($labels); ?>, // Labels (dates)
                datasets: [{
                    label: 'Users Joined',
                    data: <?php echo json_encode($data); ?>, // User counts
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)', // Light fill color
                    borderWidth: 3,
                    pointBackgroundColor: 'rgba(75, 192, 192, 1)', // Point color
                    pointRadius: 5, // Larger points
                    pointHoverRadius: 7, // Bigger points on hover
                    fill: true, // Fill the area under the line
                    tension: 0.4, // Smooth curve for the line
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            color: '#333',
                            font: {
                                size: 14
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)', // Dark background for tooltips
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1,
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.raw + ' users joined'; // Custom tooltip format
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Date',
                            color: '#333',
                            font: {
                                size: 16
                            }
                        },
                        grid: {
                            display: true,
                            color: 'rgba(200, 200, 200, 0.2)', // Lighter grid lines
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Number of Users',
                            color: '#333',
                            font: {
                                size: 16
                            }
                        },
                        grid: {
                            display: true,
                            color: 'rgba(200, 200, 200, 0.2)', // Lighter grid lines
                        },
                        beginAtZero: true
                    }
                },
                elements: {
                    line: {
                        borderWidth: 3, // Thicker line
                    },
                    point: {
                        radius: 5, // Larger points
                        hoverRadius: 7, // Hovering effect
                    }
                }
            }
        });



        // Function to strike-through the text in the row when checkbox is checked
function strikeThrough(checkbox) {
    var row = checkbox.parentElement.parentElement; // Get the parent <tr> of the checkbox
    if (checkbox.checked) {
        row.style.textDecoration = "line-through";
    } else {
        row.style.textDecoration = "none";
    }
}

// Function to toggle select all checkboxes
function toggleSelectAll() {
    var selectAllCheckbox = document.getElementById('selectAll');
    var checkboxes = document.querySelectorAll('.selectUser');
    checkboxes.forEach(function(checkbox) {
        checkbox.checked = selectAllCheckbox.checked;
        strikeThrough(checkbox); // Apply strike-through to all selected rows
    });
}
    </script>
    <!-- ====== ionicons ======= -->
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

</body>
</html>
