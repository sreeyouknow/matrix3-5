<?php
$servername = "localhost"; // Database host
$username = "u605251166_mlm1";        // Database username
$password = "Mlm123!@#";            // Database password (for XAMPP, the default is an empty string)
$dbname = "u605251166_mlm"; // Database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
