<?php
include('config.php');

// Start session if not already started
session_start();

// Ensure the user is logged in by checking if the user_id exists in the session
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page or handle this case as needed
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Get the logged-in user's ID

// Initialize the search query and referrer_id from GET or POST
$searchQuery = "";
$referrer_id = isset($_GET['referrer_id']) ? $_GET['referrer_id'] : $user_id; // Default to the logged-in user if no referrer_id is provided

// Check if the search form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search'])) {
    $searchQuery = $_POST['search'];
}

// Construct the SQL query with search filters
$sql = "SELECT * FROM users WHERE (username LIKE '%$searchQuery%' OR referral_code LIKE '%$searchQuery%' OR phone LIKE '%$searchQuery%')";

// Add referrer_id filter if provided
$sql .= " AND referrer_id = $referrer_id";

// Execute the query
$result = $conn->query($sql);

// Check if the connection and query were successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
  

    <style>
         /* =========== Google Fonts ============ */
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
    width: 90%;
    left: 250px;
    min-height: 100vh;
    background: var(--white);
    transition: 0.5s;
}

.main.active 
{
    width: calc(100% - 80px);
    left: 81px;
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

.draw input[type="text"]:focus {
    border-color: #e9b10a;
}

.draw input[type="button"] {
    width: 100%;
    padding: 12px;
    font-size: 1.1rem;
    font-weight: 500;
    color: #fff;
    background-color: #e9b10a;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.draw input[type="button"]:hover {
    background-color: #c79a0d;
}

.draw h4 {
    text-align: center;
    font-size: 1rem;
    font-weight: 400;
    color: #333;
    margin-top: 20px;
}

.draw h4 span {
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

        .table-container {
            max-width: 800px;
            width: 200%;
            margin: 0 auto; /* Centers horizontally */
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            
            position: absolute; /* Needed to position it precisely */
            top: 58%; /* Vertical center alignment */
            left:19%;   /* Align to the right side */
            transform: translateY(-50%); /* Adjust to align exactly at the center vertically */
        }
        table {
            width: 100%;
            border-collapse: collapse;
           
        }
        th, td {
            font-family: arial, sans-serif;
            background-color: white;
            padding: 15px;
            text-align: left;
            font-size: 16px;
            color: #333;
            font-weight: bold;
        }
        th {
            border: 1px solid #f1f1f1;
            background-color:#ffcb30;
            color: #fff;
        }
        tr {
            border-bottom: 1px solid #f1f1f1;
            background-color:white;
        }
        
        tr:last-child {
            border-bottom: none;
        }
        .header {
           
            text-align: center; 
            font-family: 'Times New Roman', serif;
            font-size: 24px;
            font-weight: bold; 
            margin: 30px 0;
            color: black;     
}     
.alert {
    padding: 15px;
    margin: 20px 0;
    border-radius: 5px;
    font-size: 16px;
    font-family: Arial, sans-serif;
    text-align: center;
    width: 100%;
}

/* Warning alert style */
.alert-warning {
    background-color: #fff3cd;
    color: #856404;
    border: 1px solid #ffeeba;
}

/* Optional: Add a close button to dismiss the alert */
.alert .close-btn {
    background: transparent;
    border: none;
    color: #856404;
    font-size: 20px;
    font-weight: bold;
    position: absolute;
    top: 10px;
    right: 15px;
    cursor: pointer;
}

.alert .close-btn:hover {
    color: #ff0000;
}
/* Container for the input and icon */
.search-container {
    margin-top: 20px;
}

/* Style the input field container */
.input-container {
    position: relative;
    display: flex;
    align-items: center;
   /* Limit the input width */
}

/* Style the search input */
.form-control {
    width: 100%;
    padding: 12px 100px 9px 100px; /* Add padding on the right for the icon */
    border: 2px solid #ccc;
    border-radius: 20px;
    font-size: 16px;
    margin-bottom:4%;
}

/* Style the search icon inside the input */
.search-icon {
    position: absolute;
    left: 10px; /* Position the icon on the right */
    font-size: 20px;
    color: #888;
    margin-bottom:4%;
}
.botbut {
                text-align: center; /* Centers the button horizontally */
                
            }

            /* Style for the button */
            .botbut input[type="button"] {
                background-color: #ffcd38; /* Green background */
                color: white; /* White text */
                padding: 10px 20px; /* Adds padding around the button text */
                border: 1.5px solid #ffcd38;
                border-radius: 5px; /* Rounded corners */
                font-size: 16px; /* Text size */
                cursor: pointer; /* Changes cursor to pointer on hover */
                transition: background-color 0.3s; /* Smooth background color transition */
                position: absolute;
                margin:4% 0% 0% -9%;
            }

            /* Button hover effect */
            .botbut input[type="button"]:hover {
                background-color: white;
                color: #ffcd38; /* Darker green on hover */
        }

/* Responsive design for smaller screens */
@media (max-width: 768px) {
    h2{
        margin-left:59%;
        width: 768px;   
    }
    .search-container {
        flex-direction: column;
    }
    .form-control {
        max-width: 90%;
    }
    .table-container {
            top: 48%; 
            left:19%;
            width: 768px;   
        }
.botbut {
                text-align: center; /* Centers the button horizontally */
                
            }

            /* Style for the button */
            .botbut input[type="button"] {
                margin:10% 0% 0% -9%;
            }

            /* Button hover effect */
            .botbut input[type="button"]:hover {
                background-color: white;
                color: #ffcd38; /* Darker green on hover */
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

        <!-- ========================= Main ==================== -->
        <div class="main">
            <div class="topbar">
                <div class="toggle">
                    <ion-icon name="menu-outline"></ion-icon>
                </div>
                <div class="search-container">
    <form method="POST" action="" class="form-inline">
        <!-- Search input field -->
        <div class="input-container">
            <input type="text" name="search" class="form-control" value="<?php echo htmlspecialchars($searchQuery); ?>" placeholder="Search ">
            <!-- Search icon -->
            <ion-icon name="search-outline" class="search-icon"></ion-icon>
        </div>
    </form>
</div>


                <div class="user">
                    <img src="assets/imgs/customer01.png" alt="">
                </div>
                </div>
                <div class="header">
                    <h2>List Of users</h2>
                </div>
                <div class="table-container">
    <?php
    if ($result->num_rows > 0) {
        echo "<table class='table'>";
        echo "<thead>
                <tr>
                    <th>Sno</th>
                    <th>Name</th>
                    <th>Referral Code</th>
                    <th>Phone</th>
                    <th>Stage</th>
                    <th>Level</th>
                    <th>Created At</th>
                </tr>
              </thead>
              <tbody>";

        // Initialize the counter
        $sno = 1;

        // Output data of each row
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>" . $sno . "</td>
                    <td>" . htmlspecialchars($row["username"]) . "</td>
                    <td>" . htmlspecialchars($row["referral_code"]) . "</td>
                    <td>" . htmlspecialchars($row["phone"]) . "</td>
                    <td>" . htmlspecialchars($row["current_stage"]) . "</td>
                    <td>" . htmlspecialchars($row["current_level"]) . "</td>
                    <td>" . htmlspecialchars($row["created_at"]) . "</td>
                  </tr>";

            // Increment the counter
            $sno++;
        }

        echo "</tbody></table>";
    } else {
        echo "<div class='alert alert-warning'>No users found who were referred by you.</div>";
    }

    // Close connection
    $conn->close();
    ?>

            <div class="botbut">
              <a href="matrix.php"><input type="button" value="Show More.."></a> 
             </div>
             </div>
             </div>
             </div>      
    <!-- =========== Scripts =========  -->
    <script src="assets/js/main.js"></script>
    <script>
        function populateTable(){
            constant tableBody =document.getElementById('table').getElementbyTagName('tbody')[0];
            data.forEach((item, index)=>{
                const row = tableBody.insertRow();
                SnoCell.textContent = index+1;
            });
        }
    </script>

    <!-- ====== ionicons ======= -->
     <!-- Ionicons CDN -->

     <script src="https://cdn.jsdelivr.net/npm/ionicons@5.5.0/dist/ionicons.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>

</html>