<?php
session_start();
include '../db.php';

// Redirect if not logged in as an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch admin details
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
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Welcome, <?php echo $user['full_name']; ?>!</h2>
        <p>Email: <?php echo $user['email']; ?></p>
        <hr>

        <h3>Job Postings for Approval</h3>
        <?php
        // Fetch job postings pending approval
        $sql = "SELECT * FROM job_postings WHERE status = 'pending'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo "<ul>";
            while ($row = $result->fetch_assoc()) {
                echo "<li>{$row['title']} - <a href='approve_job.php?id={$row['job_id']}'>Approve</a> | <a href='reject_job.php?id={$row['job_id']}'>Reject</a></li>";
            }
            echo "</ul>";
        } else {
            echo "<p>No job postings pending approval.</p>";
        }
        ?>

        <hr>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
</body>
</html>