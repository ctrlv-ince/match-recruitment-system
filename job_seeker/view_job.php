<?php
session_start();
include '../db.php';

// Redirect if not logged in as a job seeker
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'job_seeker') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: search_jobs.php");
    exit();
}

$job_id = $_GET['id'];
$seeker_id = $_SESSION['user_id'];

// Fetch job details
$sql = "SELECT * FROM job_postings WHERE job_id = $job_id";
$result = $conn->query($sql);
$job = $result->fetch_assoc();

// Check if the job seeker has already applied for this job
$sql = "SELECT * FROM applications WHERE job_id = $job_id AND seeker_id = $seeker_id";
$application_result = $conn->query($sql);
$has_applied = $application_result->num_rows > 0;

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$has_applied) {
    // Insert application into the database
    $sql = "INSERT INTO applications (job_id, seeker_id, status) VALUES ($job_id, $seeker_id, 'applied')";
    if ($conn->query($sql) === TRUE) {
        $message = "Application submitted successfully!";
        $has_applied = true;
    } else {
        $message = "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2><?php echo $job['title']; ?></h2>
        <p><strong>Description:</strong> <?php echo $job['description']; ?></p>
        <p><strong>Requirements:</strong> <?php echo $job['requirements']; ?></p>
        <p><strong>Skills:</strong> <?php echo $job['skills']; ?></p>

        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if ($has_applied): ?>
            <div class="alert alert-success">You have already applied for this job.</div>
        <?php else: ?>
            <form action="view_job.php?id=<?php echo $job_id; ?>" method="POST">
                <button type="submit" class="btn btn-primary">Apply Now</button>
            </form>
        <?php endif; ?>

        <a href="search_jobs.php" class="btn btn-secondary mt-3">Back to Job Listings</a>
    </div>
</body>
</html>