<?php
include('config.php');

$query = "SELECT user_id, username, phone, status FROM users WHERE status = 'pending'";
$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    echo "<tr>
        <td></td>
        <td>" . htmlspecialchars($row['username']) . "</td>
        <td>" . htmlspecialchars($row['phone']) . "</td>
        <td class='actions'>
            <button class='approve-btn' onclick='handleRequest(\"approve\", " . $row['user_id'] . ")'>Approve</button>
            <button class='reject-btn' onclick='handleRequest(\"reject\", " . $row['user_id'] . ")'>Reject</button>
        </td>
    </tr>";
}
?>
