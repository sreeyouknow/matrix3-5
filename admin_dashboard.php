<?php
session_start();
include('config.php');
// Ensure the admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}
// Query to get the total number of users
$sql = "SELECT COUNT(*) AS total_users FROM users Where role='user'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$total_users = $row['total_users'];

// Query to get the number of users who joined this week
$sql_week = "SELECT COUNT(*) AS users_this_week FROM users WHERE WEEK(created_at) = WEEK(CURRENT_DATE) AND YEAR(created_at) = YEAR(CURRENT_DATE) AND role='user'";
$result_week = $conn->query($sql_week);
$row_week = $result_week->fetch_assoc();
$users_this_week = $row_week['users_this_week'];

// Query to get the number of pending contact requests
$sql_pending_requests = "SELECT COUNT(*) AS pending_requests FROM contact_requests WHERE status = 'pending'";
$result_pending = $conn->query($sql_pending_requests);
$row_pending = $result_pending->fetch_assoc();
$pending_requests = $row_pending['pending_requests'];

// Query to get the number of users joined over time (for the graph)
$sql_graph = "SELECT DATE(created_at) AS join_date, COUNT(*) AS users_joined FROM users WHERE role='user' GROUP BY DATE(created_at) ORDER BY join_date ASC";
$result_graph = $conn->query($sql_graph);

// Prepare the labels and data for the graph
$labels = [];
$data = [];

while ($row = $result_graph->fetch_assoc()) {
    $labels[] = $row['join_date'];
    $data[] = $row['users_joined'];
}
// Query to get users with fewer than 5 referrals
$sql_referrals = "SELECT  username, phone, members_referred FROM users WHERE members_referred < 5 AND role='user'";
$result_referrals = $conn->query($sql_referrals);
$referral_users = [];

while ($row = $result_referrals->fetch_assoc()) {
    $referral_users[] = $row;
}

// image php//
$imageData = null;  // To store the image data
$imagePath = null;  // To store the image file path
$message = "";  // To store any messages like success or error

// Handle the image upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['image'])) {
    // Get the image file details
    $imageName = $_FILES['image']['name'];
    $imageTmpName = $_FILES['image']['tmp_name'];
    $imageSize = $_FILES['image']['size'];
    $imageError = $_FILES['image']['error'];

    // Set the target directory to store the image
    $targetDir = "uploads/";  // Folder 'uploads' should exist in your project directory
    $targetFile = $targetDir . basename($imageName);
    
    // Check if there were any errors during the upload
    if ($imageError === 0) {
        // Move the uploaded file to the target directory
        if (move_uploaded_file($imageTmpName, $targetFile)) {
            // Save the file path in the database
            $query = "INSERT INTO images (image_name, image_path) VALUES (?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ss", $imageName, $targetFile);
            $stmt->execute();
            $message = "Image uploaded successfully!";
        } else {
            $message = "Failed to upload the image.";
        }
    } else {
        $message = "Error during file upload.";
    }
}

// Handle image deletion
if (isset($_GET['delete'])) {
    $imageId = $_GET['delete'];
    // Fetch the image file path from the database
    $query = "SELECT image_path FROM images WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $imageId);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($imagePath);
    $stmt->fetch();
    
    // Check if the file exists before deleting it
    if (file_exists($imagePath)) {
        // Try to delete the image from the uploads folder
        if (unlink($imagePath)) {
            // After deleting the image, remove the record from the database
            $deleteQuery = "DELETE FROM images WHERE id = ?";
            $deleteStmt = $conn->prepare($deleteQuery);
            $deleteStmt->bind_param("i", $imageId);
            $deleteStmt->execute();
            $message = "Image deleted successfully!";
        } else {
            $message = "Error deleting the image.";
        }
    } else {
        $message = "File does not exist.";
    }
}

// Fetch the latest uploaded image from the database
$query = "SELECT id, image_name, image_path FROM images ORDER BY id DESC LIMIT 1";
$result = $conn->query($query);

$image = null;
if ($result->num_rows > 0) {
    $image = $result->fetch_assoc();
    $imageData = $image['image_name'];
    $imagePath = $image['image_path'];
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
    min-height: 100%;
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
    width: 60px;
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
/* ======================= Cards ====================== */
.cardBox {
 position: relative;
 width: 100%;
 padding: 20px;
 justify-content:space-between;
 display: grid;
 grid-template-columns: repeat(4, 1fr);
 grid-gap: 30px;
}

.cardBox .card {
 position: relative;
 background: var(--white);
 padding: 30px;
 border-radius: 20px;
 display: flex;
 justify-content:space-between;
 cursor: pointer;
 box-shadow: 0 7px 25px rgba(0, 0, 0, 0.08);
}

.cardBox .card .numbers {
 position: relative;
 font-weight: 500;
 font-size: 2.5rem;
 color: var(--black);
}

.cardBox .card .cardName {
 color: var(--black2);
 font-size: 1.1rem;
 margin-top: 5px;
}

.cardBox .card .iconBx {
 font-size: 3.5rem;
 color: var(--black2);
}

.cardBox .card:hover {
 background: var(--black);
}
.cardBox .card:hover .numbers,
.cardBox .card:hover .cardName,
.cardBox .card:hover .iconBx {
 color: var(--white);
}
table { 
    display: fixed;
    width: 60%;
    margin-left: 25%; /* To make the table appear from the right */
    top: 100%; /* To position it at the top of the page */
    border-collapse: collapse;
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    margin-bottom:10%; 
    
}
h1{
    margin-left: 45%; 
    margin-bottom:2%; 
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



/* Chart Container */
.chart-container {
    display:flex;
    width: 60%;
    margin-left:25%;
    margin-top: 38px;
   
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
/* upload Image */
        .con{
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
           
            margin: 0% 0% 0% 9%;
        }

        .upload-container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 140%;
            max-width: 725px;
        }

        .upload-container h2 {
            margin-bottom: 20px;
        }
        /* Hide the default file input */

        .upload-container form {
            display: flex;
            flex-direction: column;
        }

        .upload-container input[type="file"] {
            margin-bottom: 10px;
        }

        .upload-container button {
            padding: 10px;
            background-color:  rgba(255, 191, 0, 0.941);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }.upload-container button:hover {
       
            background-color:  white;
            color:  rgba(255, 191, 0, 0.941);
            border: 1px solid  rgba(255, 191, 0, 0.941);
            
        }
        #deleteButton{
            padding: 10px;
            background-color:red;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;

        }
        #deleteButton:hover{
            background-color: white;
            color: red;
            border: 1px solid red;
        }
        .image-preview {
            margin-top: 20px;
        }

        .image-preview img {
            max-width: 100%;
            max-height: 300px;
            margin-bottom: 10px;
        }

        .delete-button {
            padding: 5px 10px;
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .delete-button:hover {
            background-color: #c82333;
        }

        /* Modal Popup Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 400px;
            text-align: center;
        }

        .modal-content button {
            padding: 10px;
            margin: 5px;
        }

        .cancel-button {
            padding: 5px 10px;
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .cancel-button:hover {
            background-color: #c82333;
        }

        .message {
            padding: 10px;
            background-color: #f1f1f1;
            margin-top: 20px;
            border-radius: 5px;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
        }

/* ================= Media Queries for Responsiveness ================= */
@media (max-width: 1200px) {
    
    .cardBox {
        grid-template-columns: repeat(3, 1fr); /* Adjust card layout */
        grid-gap: 20px;
        width: 500%;
    }
    .topbar {
        width: 500%; /* Full width search bar */
        margin: 0;
    }
  
        .con{
            width: 500%;
            margin-top:-100%;
            margin-bottom:-175%;
        }
       
    
    .chart-container {
        width: 80%;
        left:-30%;

    }
}

@media (max-width: 992px) {


    .cardBox {
        grid-template-columns: repeat(2, 1fr); /* Reduce card columns */
    }
}

@media (max-width: 768px) {
    .cardBox {
        grid-template-columns: 1fr; /* Single-column layout for cards */
    }

    table{
        margin-left:40%;
        margin-bottom:30%;
    }
    h1 {
        margin-left:100%;
        width:500%;
    }

    .chart-container {
        width: 500%;
        margin-left: 0; /* Full width for charts */
    }

    .main.active{
        height: 100%;
        width:20%;
    }
}

@media (max-width: 576px) {


    .cardBox {
        grid-template-columns: 1fr; /* One card per row */
    }

    table {
        font-size: 12px; /* Further reduce table font size */

    }

    .toggle {
        font-size: 2rem; /* Resize toggle icon */
    }

    .chart-container {
        height: auto; /* Allow dynamic height for small screens */
        margin-left:30%;
    }
    .upload-container {
        margin-left: 5%;
    }

    canvas {
        height: 250px; /* Shrink chart for mobile */
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
                        <input type="text" placeholder="Search here">
                        <ion-icon name="search-outline"></ion-icon>
                    </label>
                </div>

                <div class="user">
                    <img src="assets/imgs/customer01.png" alt="">
                </div>
            </div>
        
            <!-- ======================= Cards ================== -->
            <div class="cardBox" >
                <div class="card">
                    <div>
                    
                    <div class="numbers"><?php echo $total_users; ?></div>
                    <div class="cardName">Total users</div>
                    </div>

                    <div class="iconBx">
                    <ion-icon name="people-outline"></ion-icon>
                    </div>
                </div>

                <div class="card">
                    <div>
                    <div class="numbers"><?php echo $users_this_week; ?></div>
                    <div class="cardName">users Joined This Week</div>
                    </div>

                    <div class="iconBx">
                    <ion-icon name="calendar-outline"></ion-icon>
                    </div>
                </div>

                <div class="card">
                    <div>
                    <div class="numbers"><?php echo $pending_requests; ?></div>
                    <div class="cardName">Message Requests</div>
                    </div>

                    <div class="iconBx">
                    <ion-icon name="mail-outline"></ion-icon>
                    </div>
                </div>

                <div class="card" id="referralusersCard">
                    <div>
                    <div class="numbers"><?php echo count($referral_users); ?></div>
                        <div class="cardName">Incomplite Referrals</div>
                    </div>

                    <div class="iconBx">
                    <ion-icon name="people-outline"></ion-icon>
                    </div>
                </div>
            </div>
            <!-- Graph container -->
            <div class="chart-container">
                <div style="width: 80%; margin: 0 auto;">
                    <canvas id="usersGraph" style="height: 300px;"></canvas>
                </div>
            </div>
            <div class="con">
            
            <div class="upload-container">
        <h2>Upload Image</h2>
        
        <!-- Display messages like success or error -->
        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, 'success') !== false ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Image Upload Form -->
        <form action="upload_image.php" method="POST" enctype="multipart/form-data">
            <label for="image">Select image:</label>
            <input type="file" name="image" id="image" required>
            <button type="submit" name="upload">Upload</button>
        </form>

        <!-- Display Uploaded Image -->
        <?php if ($image): ?>
            <div class="image-preview">
                <h4>Uploaded Image</h4>
                <img src="<?php echo $image['image_path']; ?>" alt="Uploaded Image">
                <br>
                <button id="deleteButton" class="delete-button" onclick="confirmDelete(<?php echo $image['id']; ?>)">Delete Image</button>
            </div>
        <?php endif; ?>
    </div>
    </div> <br> <br>
            <!-- Referral users Table -->
            
            <table>
                <thead>
                <h1>Incomplite users</h1>
                    <tr>
                    
                        <th>Username</th>
                        <th>Phone</th>
                        <th>Members Referred</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($referral_users as $user): ?>
                        <tr>
                            <td><?php echo $user['username']; ?></td>
                            <td><?php echo $user['phone']; ?></td>
                            <td><?php echo $user['members_referred']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
    </div>

    
    

    <!-- Modal for Delete Confirmation -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <p>Are you sure you want to delete this image?</p>
            <button id="confirmDeleteButton" class="delete-button">Yes, Delete</button>
            <button class="cancel-button" onclick="closeModal()">Cancel</button>
        </div>
    </div>
   
    <!-- =========== Scripts =========  -->
    <script src="assets/js/main.js"></script>
    <script>
        function confirmDelete(imageId) {
            // Show the modal for confirmation
            document.getElementById("deleteModal").style.display = "block";
            
            // Set the delete button to redirect with the delete image ID
            document.getElementById("confirmDeleteButton").onclick = function() {
                window.location.href = "upload_image.php?delete=" + imageId;
            };
        }

        function closeModal() {
            document.getElementById("deleteModal").style.display = "none";
        }

        // Close the modal if the user clicks anywhere outside of it
        window.onclick = function(event) {
            if (event.target == document.getElementById("deleteModal")) {
                closeModal();
            }
        };
    </script>
    <script>
        var ctx = document.getElementById('usersGraph').getContext('2d');
        var usersGraph = new Chart(ctx, {
            type: 'line', // Line chart
            data: {
                labels: <?php echo json_encode($labels); ?>, // Labels (dates)
                datasets: [{
                    label: 'users Joined',
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
                            text: 'Number of users',
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
    </script>
    
     
    <!-- ====== ionicons ======= -->
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

</body>
</html>