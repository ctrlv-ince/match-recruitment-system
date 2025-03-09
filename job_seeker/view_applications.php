<?php
session_start();
include '../db.php';

// Redirect if not logged in as a job seeker
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'job_seeker') {
    header("Location: login.php");
    exit();
}

$seeker_id = $_SESSION['user_id'];

// Fetch all applications for the job seeker
$sql = "SELECT applications.*, job_postings.title 
        FROM applications 
        JOIN job_postings ON applications.job_id = job_postings.job_id 
        WHERE applications.seeker_id = $seeker_id 
        ORDER BY applications.applied_at DESC";
$applications_result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Applications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>My Applications</h2>
        <?php if ($applications_result->num_rows > 0): ?>
            <?php while ($application = $applications_result->fetch_assoc()): ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $application['title']; ?></h5>
                        <p class="card-text"><strong>Applied At:</strong> <?php echo $application['applied_at']; ?></p>
                        <p class="card-text"><strong>Status:</strong> <?php echo ucfirst($application['status']); ?></p>
                        <a href="view_application.php?id=<?php echo $application['application_id']; ?>" class="btn btn-primary">View Details</a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>You have not applied for any jobs yet.</p>
        <?php endif; ?>
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</body>
</html>