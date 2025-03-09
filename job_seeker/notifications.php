<?php
session_start();
include '../db.php';

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

/**
 * Extracts the offer ID from the notification message.
 * Assumes the message contains the offer ID in a specific format.
 * Example message: "You have received a job offer (Offer ID: 123). Please respond."
 */
function extractOfferIdFromMessage($message) {
    $pattern = '/Offer ID: (\d+)/'; // Matches "Offer ID: <number>"
    if (preg_match($pattern, $message, $matches)) {
        return $matches[1]; // Returns the offer ID
    }
    return null; // Return null if no offer ID is found
}
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

                // Add a link to respond to job offers
                if (strpos($row['message'], 'received a job offer') !== false) {
                    $offer_id = extractOfferIdFromMessage($row['message']);
                    if ($offer_id) {
                        echo "<a href='respond_offer.php?offer_id=$offer_id' class='btn btn-primary btn-sm mt-2'>Respond to Offer</a>";
                    } else {
                        echo "<p class='text-danger mt-2'>Error: Offer ID not found.</p>";
                    }
                }

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