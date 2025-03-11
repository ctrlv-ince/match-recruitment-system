<?php
session_start();
include '../db.php';

// Redirect if not logged in as an employer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'employer') {
    header("Location: login.php");
    exit();
}

// Fetch all notifications for the logged-in employer
$employer_id = $_SESSION['user_id'];
$sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employer_id);
$stmt->execute();
$result = $stmt->get_result();
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
        <?php if ($result->num_rows > 0): ?>
            <ul class="list-group">
                <?php while ($notification = $result->fetch_assoc()): ?>
                    <li class="list-group-item">
                        <p><?php echo $notification['message']; ?></p>
                        <small class="text-muted"><?php echo $notification['created_at']; ?></small>
                        <?php if ($notification['message'] === "A candidate has been recommended for your job posting. Please review and decide whether to send a job offer."): ?>
                            <!-- Add a link to view candidate details -->
                            <a href="view_candidate_details.php?notification_id=<?php echo $notification['notification_id']; ?>" class="btn btn-primary btn-sm mt-2">
                                View Candidate Details
                            </a>
                        <?php endif; ?>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>No notifications found.</p>
        <?php endif; ?>
    </div>
</body>
</html>