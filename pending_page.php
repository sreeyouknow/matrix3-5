<?php
session_start();
include 'config.php'; // Include your database connection file

// Handle login functionality
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = $_POST['phone'];
    $password = $_POST['password'];

    // Check if the user exists and determine their role (user/admin) and status
    $query = "SELECT user_id, password, role, status FROM Users WHERE phone = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Bind the result to variables
        $stmt->bind_result($user_id, $hashed_password, $role, $status);
        $stmt->fetch();

        // Check if the user's account status is approved
        if ($status === 'approved') {
            // Verify the password
            if (password_verify($password, $hashed_password)) {
                // Successful login, store user information in session
                $_SESSION['user_id'] = $user_id;
                $_SESSION['phone'] = $phone;
                $_SESSION['role'] = $role;

                // Redirect based on user role
                if ($role === 'admin') {
                    header("Location: admin_dashboard.php");
                    exit();
                } else {
                    header("Location: pending_page.php");
                    exit();
                }
            } else {
                // Incorrect password
                $message = "Incorrect password.";
            }
        } else if ($status === 'pending') {
            // Account is pending
            $message = "Your account is pending approval. Please wait for admin approval.";
        } else if ($status === 'rejected') {
            // Account is rejected
            $message = "Your account has been rejected. Please contact support.";
        }
    } else {
        // User not found
        $message = "No account found with that phone number.";
    }

    // Close the statement and connection after login check
    $stmt->close();
}

// Fetch the latest uploaded image from the database
$query = "SELECT image_name, image_path FROM images ORDER BY id DESC LIMIT 1";
$result = $conn->query($query);

$imageData = null;
$imagePath = null;
$imageName = null;

if ($result->num_rows > 0) {
    // Get the image data
    $image = $result->fetch_assoc();
    $imageName = $image['image_name'];
    $imagePath = $image['image_path'];
} else {
    $imageData = "No image uploaded yet.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
 
    <style> /* =========== Google Fonts ============ */
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
     min-width: 100vh;
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
     margin-left:2%;
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

.main-container{
      justify-content: center;
      min-height: 80vh;
      margin-left:10%;
      }
      /* Header styles */
      .header {
      margin-right:40%;
      padding: 20px;
      }
      .header h1 {
      font-size: 30px;
      color: #333;
      }
      /* Content section styles */
  .content {
      background-color: #fff;
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 20px;
      width: 100%;
      margin-right: 20%;
      max-width: 900px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      text-align: center;
  }

  /* User info section */
  .user-info {
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
  }

  .icon-container {
      padding-right:20px;
      align-items: center;
      margin-bottom: 10px;
  }
  .user-info{
      margin-right: 64%;
  }
  .user-info h2 {
    height: auto;
      font-size: 20px;
      color: #555;
  }

  .user-info p {
      font-size: 14px;
      color: #888;
  }
  .profil-icon{
    height:60px;
    width: 60px;
}

  .status-box {
  display: flex;
  align-items: center;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 8px;
  background-color: #f9f9f9;
  max-width: 75%;
  margin: 0 auto;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.status-btn {
  padding: 10px 20px;
  border: none;
  border-radius: 5px;
  margin-right: 15px;
  background-color: #ffc107; /* Yellow */
  color: #fff;
  font-size: 16px;
  cursor: pointer;
}

.status-box label {
  font-size: 20px;
  color: black;
  flex: 1;
  text-align: left;
}

  /* QR code section */
  .qr-section {
      margin-bottom: 20px;
  }
  .qr-section p 
  {
      font-size: 25px;
      margin-right: 2%;
      font-family: 'Times New Roman', Times, serif;
      font-style: bolder;
  }
  .qr-icon {
      font-size: 250px;
      color: #555;
  }
 .qr-code{
    width: 300px;
    height: 300px;
 }

 .user-icon{
    width: 100px;
    height: 100px;
 }
  .label-container {
          width: 100%;
          max-width: 100%;
          margin: 20px auto;
          padding: 15px;
          border: 2px solid #4CAF50;
          border-radius: 8px;
          background-color: #f9f9f9;
          font-family: Arial, sans-serif;
          color: #333;
          text-align: center;
      }
      .label-container h3 {
          color: #4CAF50;
          font-size: 1.2rem;
          margin-bottom: 10px;
      }
      .label-container ul {
          text-align: left;
          margin: 0;
          padding: 0 10px;
          list-style-type: decimal;
      }
      .label-container ul li {
          margin-bottom: 10px;
          line-height: 1.5;
      }
      /* ===== Mobile Adaptability ===== */
/* ===== Mobile Adaptability ===== */
@media (max-width: 768px) {
    /* Main container adjustments */
    .main-container {
        margin: 0;
        padding: 10px;
        width: 100%;
    }

    /* Header adjustments */
    .header {
        margin-right: 0;
        padding: 10px;
        text-align: center;
    }

    .header h1 {
        font-size: 22px;
    }

    /* Content section adjustments */
    .content {
        background-color: #fff;
        width: 95%; /* Increased width to utilize space */
        margin: 0 auto; /* Center align */
        padding: 15px; /* Added padding for better spacing */
        box-shadow: none; /* Simplify for mobile */
        border-radius: 5px; /* Softer edges */
    }

    /* User info adjustments */
    .user-info {
        flex-direction: column;
        align-items: center;
        text-align: center;
        margin-bottom: 15px;
    }

    .icon-container {
        margin-bottom: 10px;
        padding-right: 0;
    }

    .user-info h2 {
        font-size: 18px;
    }

    .user-info p {
        font-size: 14px;
    }

    .profil-icon {
        height: 50px;
        width: 50px;
    }

    /* QR section adjustments */
    .qr-section {
        text-align: center;
        margin-bottom: 20px;
    }

    .qr-code {
        width: 250px;
        height: 250px;
    }

    /* Status box adjustments */
    .status-box {
        flex-direction: column;
        max-width: 95%;
        margin: 0 auto;
        padding: 15px;
    }

    .status-btn {
        width: 100%;
        margin: 10px 0;
    }

    .status-box label {
        font-size: 16px;
        text-align: left;
    }

    /* Label container adjustments */
    .label-container {
        margin: 10px auto;
        width: 95%;
        padding: 10px;
        font-size: 14px;
        border-radius: 5px;
    }

    .label-container h3 {
        font-size: 16px;
    }

    .label-container ul {
        padding-left: 20px;
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
                    <a href="#">
                        <span class="icon">
                            <ion-icon name="home-outline"></ion-icon>
                        </span>
                        <span class="title">Dashboard</span>
                    </a>
                </li>

                <li>
                    <a href="#">
                        <span class="icon">
                            <ion-icon name="people-outline"></ion-icon>
                        </span>
                        <span class="title">Customers</span>
                    </a>
                </li>

                <li>
                    <a href="#">
                        <span class="icon">
                            <ion-icon name="cash-outline"></ion-icon>
                        </span>
                        <span class="title">Withdrawal</span>
                    </a>
                </li>

                <li>
                    <a href="#">
                        <span class="icon">
                            <ion-icon name="person-outline"></ion-icon>
                        </span>
                        <span class="title">Profile</span>
                    </a>
                </li>

                <li>
                    <a href="#">
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
        

    <!-- =========== Scripts =========  -->
    <script src="assets/js/main.js"></script>

    <!-- ====== ionicons ======= -->
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

    <div class="main-container">
        <!-- Header section -->
        <div class="header">
            <h1>Pending Invitation</h1>
        </div>

        <!-- Content section -->
        <div class="content">
            <div class="user-info">
                <!-- Profile icon -->
                <div class="icon-container">
                    <img src="assets/imgs/customer01.png" alr="" class="profil-icon">
                </div>
                <div>
                <h2>Welcome!</h2>
                </div>
            </div>
            <div class="qr-section">
                <p>Pless Scan Me and Pay ₹499</p>
                <div>
                <?php
                    if ($imagePath) {
                        echo "<div class='image'>
                                <img src='$imagePath' alt='$imageName' class='qr-code'>
                            </div>";
                    } else {
                        echo "<p>No image uploaded yet.</p>";
                    }
                    ?>
                </div>
            </div>
            <div class="status-buttons">
            <div class="status-box">
                <button class="status-btn pending">contact</button>
                <label>
                After Payment Send Screenshot to +916379686824
                </label>
            </div>
        </div>       
            <div class="label-container">
        <h3>NOTE:</h3>
        <ul>
            <li>This QR code is for the payment of the membership fee.</li>
            <li>If you have not paid the membership fee yet, you will not be able to access the dashboard.</li>
            <li>If you have paid the membership fee, you will be able to access the dashboard when admin gives access.</li>
        </ul>
    </div>

        </div>
    </div>
  </div>
</body>
</html>
