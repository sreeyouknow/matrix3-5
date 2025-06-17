<?php
// Start session to access logged-in user details
session_start();
include('config.php'); // Include database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

// Get user ID from session
$user_id = $_SESSION['user_id'];
// Function to get the count of users under the given referral code
function getReferralCount($user_id, $conn) {
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
SELECT COUNT(*) AS total_users FROM ReferralTree;

    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($totalusers);
    $stmt->fetch();
    $stmt->close(); // Close the statement after fetching the result
    return $totalusers;
}

// Function to calculate reward and stage/level based on total users
function calculateProgress($totalusers) {
    // Default values
    $reward = 0;
    $stage = 0;
    $level = 0;

    // Define the stage and level thresholds
    if ($totalusers >= 2441405) {
        $stage = 3;
        $level = 3;
        $reward = 3937500; // Level 3 of Stage 3
    } elseif ($totalusers >= 488280) {
        $stage = 3;
        $level = 2;
        $reward = 787500; // Level 2 of Stage 3
    } elseif ($totalusers >= 97655) {
        $stage = 3;
        $level = 1;
        $reward = 157500; // Level 1 of Stage 3
    } elseif ($totalusers >= 19530) {
        $stage = 2;
        $level = 3;
        $reward = 0; // Level 3 of Stage 2
    } elseif ($totalusers >= 3905) {
        $stage = 2;
        $level = 2;
        $reward = 28125; // Level 2 of Stage 2
    } elseif ($totalusers >= 780) {
        $stage = 2;
        $level = 1;
        $reward = 5625; // Level 1 of Stage 2
    } elseif ($totalusers >= 155) {
        $stage = 1;
        $level = 3;
        $reward = 0; // Level 3 of Stage 1
    } elseif ($totalusers >= 30) {
        $stage = 1;
        $level = 2;
        $reward = 1125; // Level 2 of Stage 1
    } elseif ($totalusers >= 5) {
        $stage = 1;
        $level = 1;
        $reward = 225; // Level 1 of Stage 1
    }

    return [$reward, $stage, $level];
}

// Get the current referral count
$totalusers = getReferralCount($user_id, $conn);

// Calculate the reward, stage, and level based on the total users count
list($reward, $stage, $level) = calculateProgress($totalusers);

// Get the current reward status from the database
$totalusers = getReferralCount($user_id, $conn);

// Calculate the reward, stage, and level based on the total users count
list($reward, $stage, $level) = calculateProgress($totalusers);

// Get the current reward status, stage, and level from the database
$query = "SELECT reward_status, current_stage, current_level FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($rewardStatus, $currentStage, $currentLevel);
$stmt->fetch();
$stmt->close(); // Close the statement after fetching the result

// Check if the user has already received the reward for this stage and level
if ($reward > 0 && ($rewardStatus !== 'awarded' || $stage > $currentStage || ($stage == $currentStage && $level > $currentLevel))) {
    // Update total_earned, current_stage, current_level, and reward_status
    $updateQuery = "UPDATE users SET total_earned = total_earned + ?, current_stage = ?, current_level = ?, reward_status = 'awarded' WHERE user_id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("iiii", $reward, $stage, $level, $user_id);
    $stmt->execute();
    $stmt->close(); // Close the statement after execution

    // Optional: Show celebration modal if a level is completed
    echo "<div id='celebrationPopup' class='popup'>
            <div class='popup-content'>
                <h2>🎉 Congratulations! 🎉</h2>
                <p>You have completed Level $level of Stage $stage!</p>
                <p>You have referred $totalusers users!</p>
                <p>You have earned ₹" . $reward . " in rewards.</p>
                <button id='closePopup' class='btn'>Close</button>
            </div>
          </div>";
}
// Fetch user details from the database
$query = "SELECT username, phone, address, referral_code, created_at, current_stage, current_level, members_referred, total_earned 
          FROM users 
          WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "User not found.";
    exit();
}
// Fetch referred users
$referral_code = $user['referral_code'];
$referred_query = "SELECT username, phone, status, role,created_at, current_stage, current_level,members_referred, total_earned 
                   FROM users 
                   WHERE referrer_id = ? AND status='approved'And role='user'";
$stmt = $conn->prepare($referred_query);
$stmt->bind_param("i", $user_id); // Use logged-in user's ID as referrer_id
$stmt->execute();
$referred_result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti"></script>
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

/* ================== Order Details List ============== */
.details {
 position: relative;
 width: 100%;
 padding: 20px;
 display: grid;
 
 margin-left:20%;
 grid-template-columns: 2fr 1fr;
 grid-gap: 30px;
 /* margin-top: 10px; */
}

.details .recentOrders {
 position: relative;
 background: var(--white);
 padding: 20px;
 box-shadow: 0 7px 25px rgba(0, 0, 0, 0.08);
 border-radius: 20px;
}

.details .cardHeader {
 display: flex;
 padding: 2%;
 justify-content: space-between;
 align-items: flex-start;
}
.cardHeader h2 {
 font-weight: 600;
 border-radius: 0%;
 color: var(--black);
}
.cardHeader .btn {
 position: relative;
 padding: 5px 10px;
 background: #e9b10a;
 text-decoration: none;
 color: var(--white);
 border-radius: 6px;
}

.details table {
 width: 100%;
 border-collapse: collapse;
}
.details table thead td {
 font-weight: 600;
}
.details .recentOrders table tr {
 color: var(--black1);
 border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}
.details .recentOrders table tr:last-child {
 border-bottom: none;
}
.details .recentOrders table tbody tr:hover td {
 background: #e9b10a;
 color: var(--white);
 transition: 0.3s ease; 
}

.details .recentOrders table tr td {
 padding: 10px;
 font-size: 20px;
 font-style:arial;
}
.details .recentOrders table tr td:last-child {
 text-align: end;
}
.details .recentOrders table tr td:nth-child(2) {
 text-align: end;
}
.details .recentOrders table tr td:nth-child(3) {
 text-align: center;
}
.status.delivered {
 padding: 2px 4px;
 background: #8de02c;
 color: var(--white);
 border-radius: 4px;
 font-size: 14px;
 font-weight: 500;
}
.status.pending {
 padding: 2px 4px;
 background: #e9b10a;
 color: var(--white);
 border-radius: 4px;
 font-size: 14px;
 font-weight: 500;
}
.status.return {
 padding: 2px 4px;
 background: #f00;
 color: var(--white);
 border-radius: 4px;
 font-size: 14px;
 font-weight: 500;
}
.status.inProgress {
 padding: 2px 4px;
 background: #1795ce;
 color: var(--white);
 border-radius: 4px;
 font-size: 14px;
 font-weight: 500;
}

.recentCustomers {
 position: relative;
 display: grid;
 min-height: 500px;
 padding: 20px;
 background: var(--white);
 box-shadow: 0 7px 25px rgba(0, 0, 0, 0.08);
 border-radius: 20px;
}
.recentCustomers .imgBx {
 position: relative;
 width: 40px;
 height: 40px;
 border-radius: 50px;
 overflow: hidden;
}
.recentCustomers .imgBx img {
 position: absolute;
 top: 0;
 left: 0;
 width: 100%;
 height: 100%;
 object-fit: cover;
}
.recentCustomers table tr td {
 padding: 12px 10px;
}
.recentCustomers table tr td h4 {
 font-size: 16px;
 font-weight: 500;
 line-height: 1.2rem;
}
.recentCustomers table tr td h4 span {
 font-size: 14px;
 color: var(--black2);
}
.recentCustomers table tr:hover {
 background-color: #e9b10a;
 color: var(--white);
}
.recentCustomers table tr:hover td h4 span {
 color: var(--white);
}
 /* Popup Background */
 .popup {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.6);
        justify-content: center;
        align-items: center;
        z-index: 9999;
        animation: fadeIn 0.5s ease-in-out;
    }

    /* Popup Content */
    .popup-content {
        background: linear-gradient(45deg, #ff6b6b, #f7b7a3);
        border-radius: 12px;
        padding: 30px;
        text-align: center;
        width: 380px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        transform: scale(0.9);
        animation: scaleUp 0.5s forwards;
    }

    .popup h2 {
        color: #fff;
        font-size: 28px;
        margin-bottom: 10px;
        font-weight: bold;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    }

    .popup p {
        font-size: 18px;
        margin-bottom: 20px;
        font-weight: 500;
        color: #f1f1f1;
    }

    .btn {
        background-color: #fff;
        color: #f7b7a3;
        border: none;
        padding: 12px 20px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 16px;
        transition: all 0.3s ease;
    }

    .btn:hover {
        background-color: #ff6b6b;
        color: white;
        transform: scale(1.05);
    }

    @keyframes fadeIn {
        0% { opacity: 0; }
        100% { opacity: 1; }
    }

    @keyframes scaleUp {
        0% { transform: scale(0.8); }
        100% { transform: scale(1); }
    }
/* ====================== Responsive Design ========================== */
@media (max-width: 991px) {
 
 .cardBox {
   grid-template-columns: repeat(2, 1fr);
 }

}

@media (max-width: 768px) {
 .details {
   grid-template-columns: 1fr;
 }
 .recentOrders {
   overflow-x: auto;
   width: 768px;
 }
 .status.inProgress {
   white-space: nowrap;
 }


}

@media (max-width: 480px) {
 .cardBox {
    margin-left:90px;
    width: 500px;
   grid-template-columns: repeat(2, 1fr);
 }
 .cardHeader h2 {
   font-size: 20px;
 }
 .user {
   min-width: 40px;
 }
 
 .toggle {
   z-index: 10001;
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

            <!-- ======================= Cards ================== -->
            <div class="cardBox" >
                <div class="card">
                    <div>
                    
                        <div class="numbers">Stage - <?= htmlspecialchars($user['current_stage']); ?></div>
                        <div class="cardName">Level - <?= htmlspecialchars($user['current_level']); ?></div>
                    </div>

                    <div class="iconBx">
                        <ion-icon name="podium-outline"></ion-icon>
                    </div>
                </div>

                <div class="card">
                    <div>
                        <div class="numbers"><?= htmlspecialchars($user['members_referred']); ?></div>
                        <div class="cardName">Members Refered</div>
                    </div>

                    <div class="iconBx">
                        <ion-icon name="people-outline"></ion-icon>
                    </div>
                </div>

                <div class="card">
                    <div>
                        <div class="numbers"><?= htmlspecialchars($user['referral_code']); ?></div>
                        <div class="cardName">Referal Code</div>
                    </div>

                    <div class="iconBx">
                        <ion-icon name="gift-outline"></ion-icon>
                    </div>
                </div>

                <div class="card">
                    <div>
                        <div class="numbers">₹<?= htmlspecialchars($user['total_earned']); ?></div>
                        <div class="cardName">total Earning</div>
                    </div>

                    <div class="iconBx">
                        <ion-icon name="cash-outline"></ion-icon>
                    </div>
                </div>
            </div>

            <!-- ================ Order Details List ================= -->
            <div class="container">
    <div class="details">
        <div class="recentOrders">
            <div class="cardHeader">
                <h2>Referred Members</h2>
                <a href="matrix.php" class="btn">View All</a>
            </div>
            <table>
                <thead>
                <tr>
                    <td>Name</td>
                    <td>Created At</td>
                    <td>Members Referred</td>
                    <td>Stage</td>
                    <td>Level</td>
                </tr>
                </thead>
                <tbody>
                <?php if ($referred_result->num_rows > 0): ?>
                    <?php while ($referred_user = $referred_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($referred_user['username']); ?></td>
                            <td><?= htmlspecialchars($referred_user['created_at']); ?></td>
                            <td><?= htmlspecialchars($referred_user['members_referred']); ?></td>
                            <td><?= htmlspecialchars($referred_user['current_stage']); ?></td>
                            <td><?= htmlspecialchars($referred_user['current_level']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">No referred members found.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

            </div>
        </div>
    </div>

    <!-- =========== Scripts =========  -->
    <script src="assets/js/main.js"></script>

<script>
    // Show the popup if the user has earned a reward
    window.onload = function() {
        var reward = <?php echo $reward; ?>;
        if (reward > 0) {
            document.getElementById('celebrationPopup').style.display = 'flex';
            startConfetti(); // Start confetti animation
        }
    };

    // Close the popup when the close button is clicked
    document.getElementById('closePopup').onclick = function() {
        document.getElementById('celebrationPopup').style.display = 'none';
    };

    // Function to start confetti animation
    function startConfetti() {
        var myConfetti = confetti.create(document.getElementById('celebrationPopup'), {
            resize: true,
            useWorker: true
        });
        myConfetti({
            particleCount: 200,
            spread: 160,
            origin: { y: 0.6 }
        });
    }
</script>
    <!-- ====== ionicons ======= -->
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>

</html>