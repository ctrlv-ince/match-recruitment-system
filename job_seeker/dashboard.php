<?php
session_start();
include '../db.php';

// Redirect if not logged in as a job seeker
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'job_seeker') {
    header("Location: login.php");
    exit();
}

// Fetch job seeker details
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE user_id = $user_id";
$result = $conn->query($sql);
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Seeker Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Welcome, <?php echo $user['full_name']; ?>!</h2>
        <p>Email: <?php echo $user['email']; ?></p>
        <hr>

        <h3>Your Applications</h3>
        <?php
        // Fetch applications for this job seeker
        $sql = "SELECT applications.*, job_postings.title 
                FROM applications 
                JOIN job_postings ON applications.job_id = job_postings.job_id 
                WHERE applications.seeker_id = $user_id";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo "<ul>";
            while ($row = $result->fetch_assoc()) {
                echo "<li>{$row['title']} - Status: {$row['status']}</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>You have not applied for any jobs yet.</p>";
        }
        ?>
        <?php
        // Fetch the number of unread notifications
        $sql = "SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = $user_id AND status = 'unread'";
        $unread_count = $conn->query($sql)->fetch_assoc()['unread_count'];
        ?>
        <a href="notifications.php" class="btn btn-primary position-relative">
            Notifications
            <?php if ($unread_count > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    <?php echo $unread_count; ?>
                </span>
            <?php endif; ?>
        </a>
        <hr>
        <a href="search_jobs.php" class="btn btn-primary">Search Jobs</a>
        <a href="view_interview.php" class="btn btn-primary">View Interviews</a>  
        <a href="update_profile.php" class="btn btn-primary">Update Profile</a>  
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
</body>
</html>