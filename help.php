<?php
session_start();
require 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if user is not logged in
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id']; 

// Handle AJAX form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = [];
    $name = $conn->real_escape_string($_POST['name']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $issue = $conn->real_escape_string($_POST['issue']);

    // Insert data into the database
    $sql = "INSERT INTO contact_requests (name, phone, issue) VALUES ('$name', '$phone', '$issue')";
    if ($conn->query($sql) === TRUE) {
        $response['status'] = 'success';
        $response['message'] = 'Your request has been submitted successfully!';
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Something went wrong. Please try again.';
    }

    echo json_encode($response);
    exit;
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
        
        .container-11 {
            display: flex;
            background-color: #e9b10a;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            width: 70%;
            height: 80%;
            backdrop-filter: blur(10px);
            margin: 2% 0% 0% 15%;
            position: absolute;
        }

        .image-box img {
            width: 80%;
            height: 100%;
        
            background-size: cover;
            background-position: center;
            border-radius: 12px;
            margin-right: 20px;
            position: relative;
        }

        .form-box {
            width: 50%;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 24px;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        form label {
            font-size: 14px;
            margin-bottom: 6px;
            display: block;
            color: #ddd;
        }

        input, textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #fff;
            border-radius: 8px;
            background-color: rgba(255, 255, 255, 0.2);
            color: #fff;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        input:focus, textarea:focus {
            border-color: #ff9100;
            outline: none;
        }

        textarea {
            height: 120px;
            resize: vertical;
        }

        .submit-btn {
            width: 100%;
            padding: 12px;
            background-color: #e9b10a;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            border: 1px solid #fff;
        }

        .submit-btn:hover {
            background-color: #ffffff;
            color: #e9b10a;
        }

        /* ===================== Responsive Design ===================== */

        @media screen and (max-width: 1200px) {
        .container-11 {
            width: 80%;
            margin: 5% 10%;
            display:flex;
        }

        .image-box img {
            width: 70%;
        }

        .form-box {
            width: 60%;
        }
        }

        @media screen and (max-width: 992px) {
        .container-11 {
            width: 90%;
            margin: 10% 5%;
        }

        .image-box img {
            width: 60%;
        }

        .form-box {
            width: 70%;
        }

        .navigation {
            width: 200px;
            height: 100%;
        }

        .navigation.active {
            width: 60px;
            height: 100%;
        }

        .main {
            width: calc(100% - 200px);
            left: 200px;
            position: fixed;
        }

        .topbar {
            padding: 0 20px;
        }

        .toggle {
            font-size: 2rem;
        }
        }

     @media screen and (max-width: 768px) {
        .container-11 {
            flex-direction: column;
            padding: 20px;
          
            width: 100%;
            height: 90%;
        }

        .image-box img {
            width: 100%;
            margin-right: 0;
        }

        .form-box {
            width: 100%;
        }

    
    
        }

        @media screen and (max-width: 480px) {
        .toggle {
            font-size: 2rem;
        }

        .search label input {
            font-size: 14px;
        }

        .submit-btn {
            font-size: 14px;
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
             
        <div class="container-11">
      <!-- Left Side for Image -->
      <div class="image-box">
        <img src="assets/imgs/nhhh.jpg" alt="">
       </div>

      <!-- Right Side for Form -->
      <div class="form-box">
        <h2>Contact Us for Help</h2>
        <form id="contactForm">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" placeholder="Enter your full name" required>

            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" placeholder="Enter your phone number" required>

            <label for="issue">Describe Your Issue</label>
            <textarea id="issue" name="issue" placeholder="Describe your issue here" required></textarea>

            <button type="submit" class="submit-btn">Submit</button>
        </form>
    </div>
    </div>
             </div>

<!-- =========== Scripts =========  -->
<!-- =========== Scripts =========  -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('contactForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch(window.location.href, { // Use the current page URL
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message,
                        confirmButtonColor: '#3085d6',
                        timer: 3000
                    });
                    document.getElementById('contactForm').reset(); // Reset form fields
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.message,
                        confirmButtonColor: '#d33',
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops!',
                    text: 'An unexpected error occurred.',
                });
            });
        });
    </script>
    <script src="assets/js/main.js"></script>
<!-- ====== ionicons ======= -->
<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

</body>
</html>