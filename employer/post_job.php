<?php
session_start();
include '../db.php';

// Redirect if not logged in as an employer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'employer') {
    header("Location: login.php");
    exit();
}

$message = ''; // To display success or error messages

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $requirements = $_POST['requirements'];
    $skills = $_POST['skills'];
    $location = $_POST['location'];
    $employer_id = $_SESSION['user_id'];

    // Insert job posting into the database
    $sql = "INSERT INTO job_postings (employer_id, title, description, requirements, skills, location, status) 
            VALUES ($employer_id, '$title', '$description', '$requirements', '$skills', '$location', 'pending')";
    if ($conn->query($sql) === TRUE) {
        $message = "Job posted successfully! Waiting for admin approval.";
    } else {
        $message = "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a Job</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Post a Job</h2>
        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>
        <form action="post_job.php" method="POST">
            <div class="mb-3">
                <label for="title" class="form-label">Job Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Job Description</label>
                <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
            </div>
            <div class="mb-3">
                <label for="location" class="form-label">Job Location/City</label>
                <input type="text" class="form-control" id="location" name="location" required>
            </div>
            <div class="mb-3">
                <label for="requirements" class="form-label">Requirements</label>
                <textarea class="form-control" id="requirements" name="requirements" rows="3" required></textarea>
            </div>
            <div class="mb-3">
                <label for="skills" class="form-label">Required Skills</label>
                <input type="text" class="form-control" id="skills" name="skills" required>
                <small class="form-text text-muted">Separate skills with commas (e.g., PHP, MySQL, JavaScript).</small>
            </div>
            <button type="submit" class="btn btn-primary">Post Job</button>
            <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>