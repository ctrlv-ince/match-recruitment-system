<?php
session_start();
include 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch notifications for the logged-in user
$sql = "SELECT * FROM notifications WHERE user_id = $user_id ORDER BY created_at DESC";
$result = $conn->query($sql);

// Mark all notifications as read
$sql = "UPDATE notifications SET status = 'read' WHERE user_id = $user_id AND status = 'unread'";
$conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Notifications</h2>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $badge = ($row['status'] === 'unread') ? "<span class='badge bg-danger'>New</span>" : "";
                echo "<div class='card mb-3'>";
                echo "<div class='card-body'>";
                echo "<p class='card-text'>{$row['message']} $badge</p>";
                echo "<small class='text-muted'>{$row['created_at']}</small>";
                echo "</div>";
                echo "</div>";
            }
        } else {
            echo "<p>No notifications found.</p>";
        }
        ?>
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</body>
</html>