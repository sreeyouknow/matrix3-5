<?php
session_start();
include 'config.php'; // Include database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get the logged-in user's ID
$userId = $_SESSION['user_id']; 

// Function to get the users under the given referral code using referral_code and referrer_id
function getReferralMatrix($userId, $conn) {
    // Recursive SQL query to get all the users under the referral code using referral_code and referrer_id
    $query = "
        WITH RECURSIVE ReferralTree AS (
            SELECT user_id, username, referral_code, referrer_id, status
            FROM users
            WHERE referrer_id = ? AND status = 'approved'

            UNION ALL

            SELECT u.user_id, u.username, u.referral_code, u.referrer_id, u.status
            FROM users u
            INNER JOIN ReferralTree t ON u.referrer_id = t.user_id
            WHERE u.status = 'approved'
        )
        SELECT user_id, username, referral_code, referrer_id, status
        FROM ReferralTree;
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }

    return $users;
}


// Get the logged-in user's referral code
$referralCode = '';
$query = "SELECT referral_code FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->bind_result($referralCode);
    $stmt->fetch();
}

// Get the users under the referral code (your referral matrix)
$referralMatrix = [];
if (!empty($referralCode)) {
    $referralMatrix = getReferralMatrix($userId, $conn);
}

// Function to get the referrer's username
function getReferrerUsername($referrerId, $conn) {
    $query = "SELECT username FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $referrerId);
    $stmt->execute();
    $stmt->bind_result($username);
    $stmt->fetch();
    return $username;
}
// Group users by levels and stages
function groupByLevelsAndStages($referralMatrix, $userId) {
    $levels = [
        'stage1' => [
            'level1' => [],
            'level2' => [],
            'level3' => [],
        ],
        'stage2' => [
            'level1' => [],
            'level2' => [],
            'level3' => [],
        ],
        'stage3' => [
            'level1' => [],
            'level2' => [],
            'level3' => [],
        ],
    ];

    // Level 1 is direct referrals of the logged-in user (Stage 1)
    foreach ($referralMatrix as $user) {
        if ($user['referrer_id'] == $userId) {
            $levels['stage1']['level1'][] = $user;
        }
    }

    // Level 2 and 3 are referrals of referrals
    foreach ($referralMatrix as $user) {
        if ($user['referrer_id'] != $userId) {
            // Check for level 2 (referrals of level 1)
            foreach ($levels['stage1']['level1'] as $level1User) {
                if ($user['referrer_id'] == $level1User['user_id']) {
                    $levels['stage1']['level2'][] = $user;
                }
            }
            // Check for level 3 (referrals of level 2)
            foreach ($levels['stage1']['level2'] as $level2User) {
                if ($user['referrer_id'] == $level2User['user_id']) {
                    $levels['stage1']['level3'][] = $user;
                }
            }
        }
    }

    // Now we can start with Stage 2 and Stage 3 logic similarly
    // You would need additional logic to fetch users for Stage 2 and Stage 3 as per the required hierarchy.

    return $levels;
}

// Group users into levels and stages
$groupedLevels = groupByLevelsAndStages($referralMatrix, $userId);
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
            height:100%;
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

.container-12 {
    max-width: 1200px;
    margin: auto;
    position: absolute;
    background: #ffffff;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    top:10%;
    left:8.5%;
}

.tree-level {
    margin-bottom: 30px;
    padding: 20px;
}

.level-header {
    text-align: center;
    font-size: 24px;
    font-weight: bold;
    color: #333;
    margin-bottom: 20px;
    text-transform: uppercase;
}
.text-center{
    text-align: center;
    
}
.node {
    display: inline-block;
    text-align:left;
    width: 350px;
    margin: 15px;
    padding: 20px;
    background: linear-gradient(135deg, #6c63ff, #3f51b5); /* Gradient background */
    color: #fff;
    border-radius: 12px;
    font-size: 30px;
    font-weight: bold;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    position: relative;
    transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
}

.node:hover {
    transform: scale(1.05); /* Slight zoom on hover */
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15); /* Enhanced shadow on hover */
}

.node span {
    font-size: 14px; 
    font-weight: bold;
    display:block;
    margin-top: 8px;
    color: white;
}
/* ======== Media Query for Mobile ======== */
@media (max-width: 768px) {
 
    @media (max-width: 768px) {
    .main{
        width: 134%;
    }
    .container-12 {
        width: 134%;           /* Reduce width for mobile */
        left: 15%;             /* Center the container horizontally */
        top: 15%;              /* Adjust top positioning */
        margin: 0;            /* Remove auto margin */
        padding: 20px;        /* Reduce padding for smaller screens */
    }
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
        

<div class="container-12">
        <h2 class="text-center">Your Referral Matrix</h2>
        <p class="text-muted text-center">Below is your referral matrix showing all your referred users and their levels.</p>

        <?php if (!empty($groupedLevels)): ?>

            <!-- Stage 1 -->
            <?php if (!empty($groupedLevels['stage1']['level1'])): ?>
                <div class="tree-level">
                    <div class="level-header">Stage 1 - Level 1</div>
                    <div class="text-center">
                        <?php foreach ($groupedLevels['stage1']['level1'] as $user): ?>
                            <div class="node">
                            <div><?php echo $user['username']; ?></div>
                            <span>Referral Code: <?php echo $user['referral_code']; ?></span>
                            <span>Referred by: <?php echo getReferrerUsername($user['referrer_id'], $conn); ?></span>
                            <span>Status: <strong><?php echo ucfirst($user['status']); ?></strong></span>
                        </div>

                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Level 2 -->
            <?php if (!empty($groupedLevels['stage1']['level2'])): ?>
                <div class="tree-level">
                    <div class="level-header">Stage 1 - Level 2</div>
                    <div class="text-center">
                        <?php foreach ($groupedLevels['stage1']['level2'] as $user): ?>
                            <div class="node">
                            <div><?php echo $user['username']; ?></div>
                            <span>Referral Code: <?php echo $user['referral_code']; ?></span>
                            <span>Referred by: <?php echo getReferrerUsername($user['referrer_id'], $conn); ?></span>
                            <span>Status: <strong><?php echo ucfirst($user['status']); ?></strong></span>
                        </div>

                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Level 3 -->
            <?php if (!empty($groupedLevels['stage1']['level3'])): ?>
                <div class="tree-level">
                    <div class="level-header">Stage 1 - Level 3</div>
                    <div class="text-center">
                        <?php foreach ($groupedLevels['stage1']['level3'] as $user): ?>
                            <div class="node">
                            <div><?php echo $user['username']; ?></div>
                            <span>Referral Code: <?php echo $user['referral_code']; ?></span>
                            <span>Referred by: <?php echo getReferrerUsername($user['referrer_id'], $conn); ?></span>
                            <span>Status: <strong><?php echo ucfirst($user['status']); ?></strong></span>
                            </div>

                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="alert alert-warning">
                No referral data found for your account. Please refer some members to see the matrix.
            </div>
        <?php endif; ?>

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