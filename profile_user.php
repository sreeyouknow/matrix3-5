<?php
session_start();
require 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if user is not logged in
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Assume the user ID is stored in the session after login

// Fetch current user data
$sql = "SELECT username, phone, email, dob, address, referral_code, created_at, status, password FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Update user information
    $username = $_POST['username'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    // If password is provided, hash and update it; otherwise, keep the old password
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : $user['password'];

    // SQL query to update user information
    $update_sql = "UPDATE users SET username = ?, email = ?, address = ?, password = ? WHERE user_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssssi", $username, $email, $address, $password, $user_id);
    
    if ($update_stmt->execute()) {
        $_SESSION['success_message'] = "Profile updated successfully!";
        header("Location: profile_user.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Failed to update profile.";
    }
}
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
    width: 69px;
}

.navigation ul 
{
    position: absolute;
    top: 0;
    left: 0%;
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

/*profile*/

/* Profile Container */
.frame {
background-color: #f0f0f0; /* Lighter background */
border-radius: 10%;
margin: 10% 5%; /* Adjust margin for better alignment */
padding: 20px;
position: absolute;
box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); /* Adding a subtle shadow */

}

/* Centered Content */
.center {
display: flex;
flex-direction: row;
align-items: center; /* Centers everything inside */
gap:10%;

}

/* Frame Section */
.frame {
background-color: #ffc832;
margin:auto;
border-radius: 1%;
width: 900px; /* Adjust width to match profile and stats sections */
max-width: 900px; /* Set max width to limit expansion */
height: 425px; /* Set a fixed height for the frame */
padding: 20px;
box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
position: absolute;
top: 50%;
left: 60%;
transform: translate(-50%, -50%); /* Center the frame */
}

/* Profile Section */
.profile {
text-align: center;
background-color: white;
border-radius: 8px;
width: 100%;
height: 50%; /* Takes 50% of the frame's height */
padding: 20px;
margin-top: 20px;
box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}

.profile .image img {
width: 120px;
height: 120px;
border-radius: 50%;
object-fit: cover;
margin-bottom: 10px;
}

.profile .name {
font-size: 24px;
font-weight: 600;
margin-top: 10px;
}

.profile .phone {
font-size: 16px;
margin-top: 5px;
color: #777;
}

.profile .actions {
margin-top: 15px;
}

.profile .btn {
background-color: #ffcd38;
color: white;
padding: 10px 20px;
border: 1.5px solid #ffcd38;
border-radius: 5px;
cursor: pointer;
font-size: 16px;
transition: background-color 0.3s;
}

.profile .btn:hover {
background-color: white;
color:  #ffcd38;
}

/* Stats Section */
/* Stats Section */
.stats {
background-color: white;
border-radius: 8px;
width: 100%;
height: 50%; /* Takes 50% of the frame's height */
padding: 20px;
margin-top: 20px;
box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
display: flex;
flex-direction: column;
justify-content: space-between;
}

.stats .box {
display: flex;
justify-content: space-between;
align-items: center; /* Ensures the value and parameter are aligned */
margin-top: 15px;
padding: 10px 0;
border-bottom: 1px solid #f1f1f1; /* Adds a thin separator between boxes */
}

.stats .box:last-child {
border-bottom: none; /* Removes the border from the last box */
}

.stats .value {
font-size: 18px;
font-weight: bold;
color: #333;
flex-basis: 30%; /* Ensures it takes up 30% of the space */
}

.stats .parameter {
font-size: 16px;
color: #555;
flex-grow: 1; /* Makes the parameter take up the remaining space */
text-align: right; /* Aligns the parameter text to the right */
}

/* Edit Section */
.edit {
background-color:#ffc832;
border-radius: 8px;
padding: 20px;

display: none; /* Hidden by default */
box-shadow: 0 5px 6px rgba(0, 0, 0, 0.1);
position: absolute;
width: 100%; /* Increase the width by 20% */
max-width: 800px; /* Adjust max width */
margin-top: 7.5%;
left: 31%;
}

.edit h2 {
text-align: center;
margin-bottom: 20px;
color:white;
}

.form-group {
margin-bottom: 10px;
color:white;
}

.form-group label {
display: block;
font-size: 16px;
margin-bottom: 5px;
text-align: left;
}

.form-group input {
width: 100%;
padding: 12px;
border: 1px solid white;
border-radius: 5px;
font-size: 16px;
}

.button-group {
display: flex;
gap: 10px;
justify-content: center;
margin-top: 20px;
}

.button-group button {
background-color: #4CAF50;
color: white;
padding: 12px 20px;
border: none;
border-radius: 5px;
cursor: pointer;
font-size: 16px;
transition: background-color 0.3s;
}

.button-group button:hover {
background-color: #45a049;
}

.button-group button[type="button"] {
background-color: #f44336;
}

.button-group button[type="button"]:hover {
background-color: #e53935;
}

/* Responsive Design */
@media (max-width: 768px) {
.frame {
    margin: -20% 158%;


}

.center {
flex-direction: row;
align-items: center;
}

.profile, .stats, .edit {
width: 90%;
max-width: none; /* Remove max width for better responsiveness */

}
.edit{
    margin-top: 30.5%;
    left: 70%;
}

.stats .value {
font-size: 16px;
margin-bottom: 5px;
}

.stats .parameter {
font-size: 14px;
text-align: left;
width: 100%; /* Ensures full width for smaller screens */
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

                <div class="user">
                    <img src="assets/imgs/customer01.png" alt="">
                </div>

            </div>
            
    </div>
    </div>
      
<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert">
        <?php echo $_SESSION['success_message']; ?>
        <button onclick="this.parentElement.style.display='none';">&times;</button>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert red">
        <?php echo $_SESSION['error_message']; ?>
        <button onclick="this.parentElement.style.display='none';">&times;</button>
    </div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

<div class="both">
    <div class="frame" id="frame">
        <div class="center">
            <div class="profile">
                <div class="image">
                    <img src="assets/imgs/customer01.png" alt="Profile Image">
                </div>
                <div class="name"><?php echo htmlspecialchars($user['username']); ?></div>
                <div class="phone"><?php echo htmlspecialchars($user['phone']); ?></div>
                <div class="actions">
                    <button class="btn" onclick="toggleEditForm()">Edit</button><br><br><br><br>
                </div>
            </div>

            <div class="stats">
                <div class="box">
                    <span class="value">Address:</span>
                    <span class="parameter"><?php echo htmlspecialchars($user['address']); ?></span>
                </div>
                <div class="box">
                    <span class="value">Referral Code:</span>
                    <span class="parameter"><?php echo htmlspecialchars($user['referral_code']); ?></span>
                </div>
                <div class="box">
                    <span class="value">Date of Birth:</span>
                    <span class="parameter"><?php echo htmlspecialchars($user['dob']); ?></span>
                </div>
                <div class="box">
                    <span class="value">Joined At:</span>
                    <span class="parameter"><?php echo htmlspecialchars($user['created_at']); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Form -->
    <div class="edit" id="edit-form">
        <form action="profile_user.php" method="POST">
            <h2>Edit Profile</h2>
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="form-group">
                <label for="address">Address:</label>
                <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user['address']); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">New Password (leave empty if not changing):</label>
                <input type="password" id="password" name="password">
            </div>
            <div class="button-group">
                <button type="submit">Save</button>
                <button type="button" onclick="toggleEditForm()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleEditForm() {
        const profileFrame = document.getElementById('frame');
        const editForm = document.getElementById('edit-form');

        // Toggle visibility: If form is hidden, show it. If visible, hide it.
        if (editForm.style.display === 'none' || editForm.style.display === '') {
            profileFrame.style.display = 'none'; // Hide the profile and stats section
            editForm.style.display = 'block'; // Show the edit form
        } else {
            profileFrame.style.display = 'block'; // Show the profile and stats section
            editForm.style.display = 'none'; // Hide the edit form
        }
    }
</script>

<!-- =========== Scripts =========  -->
<script src="assets/js/main.js"></script>
<!-- ====== ionicons ======= -->
<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

</body>
</html>
