<?php
session_start();
include '../db.php';

// Redirect if not logged in as a job seeker
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'job_seeker') {
    header("Location: login.php");
    exit();
}

$seeker_id = $_SESSION['user_id'];
$message = '';

// Fetch interview schedules for this job seeker
$sql = "SELECT interviews.interview_id, interviews.scheduled_date, interviews.status, job_postings.title 
        FROM interviews 
        JOIN applications ON interviews.application_id = applications.application_id 
        JOIN job_postings ON applications.job_id = job_postings.job_id 
        WHERE applications.seeker_id = $seeker_id";
$result = $conn->query($sql);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $interview_id = $_POST['interview_id'];
    $action = $_POST['action']; // 'confirm' or 'decline'

    // Update interview status based on action
    $status = ($action === 'confirm') ? 'confirmed' : 'declined';
    $sql = "UPDATE interviews SET status = '$status' WHERE interview_id = $interview_id";
    if ($conn->query($sql) === TRUE) {
        $message = "Interview $status successfully!";
    } else {
        $message = "Error updating interview status: " . $conn->error;
    }
}

if ($conn->query($sql) === TRUE) {
    // Add notification for the employer
    $employer_id = $conn->query("SELECT job_postings.employer_id 
                                 FROM interviews 
                                 JOIN applications ON interviews.application_id = applications.application_id 
                                 JOIN job_postings ON applications.job_id = job_postings.job_id 
                                 WHERE interviews.interview_id = $interview_id")->fetch_assoc()['employer_id'];
    $message = "The candidate has $status the interview for job posting: {$row['title']}.";
    $sql = "INSERT INTO notifications (user_id, message) VALUES ($employer_id, '$message')";
    $conn->query($sql);

    $message = "Interview $status successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Interviews</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Your Interview Schedules</h2>
        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='card mb-3'>";
                echo "<div class='card-body'>";
                echo "<h5 class='card-title'>{$row['title']}</h5>";
                echo "<p class='card-text'><strong>Scheduled Date:</strong> {$row['scheduled_date']}</p>";
                echo "<p class='card-text'><strong>Status:</strong> {$row['status']}</p>";
                if ($row['status'] === 'pending') {
                    echo "<form action='view_interview.php' method='POST' class='d-inline'>";
                    echo "<input type='hidden' name='interview_id' value='{$row['interview_id']}'>";
                    echo "<button type='submit' name='action' value='confirm' class='btn btn-success me-2'>Confirm</button>";
                    echo "<button type='submit' name='action' value='decline' class='btn btn-danger'>Decline</button>";
                    echo "</form>";
                }
                echo "</div>";
                echo "</div>";
            }
        } else {
            echo "<p>No interview schedules found.</p>";
        }
        ?>
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</body>
</html>