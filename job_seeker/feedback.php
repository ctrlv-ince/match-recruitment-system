<?php
session_start();
include 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $job_id = $_POST['job_id'];
    $rating = $_POST['rating'];
    $comments = $_POST['comments'];

    // Insert feedback into the database
    $sql = "INSERT INTO feedback (user_id, job_id, rating, comments) VALUES ($user_id, $job_id, $rating, '$comments')";
    if ($conn->query($sql) === TRUE) {
        $message = "Thank you for your feedback!";
    } else {
        $message = "Error submitting feedback: " . $conn->error;
    }
}

// Fetch jobs for the feedback form
if ($user_type === 'job_seeker') {
    $sql = "SELECT job_postings.job_id, job_postings.title 
            FROM applications 
            JOIN job_postings ON applications.job_id = job_postings.job_id 
            WHERE applications.seeker_id = $user_id AND applications.status = 'hired'";
} else if ($user_type === 'employer') {
    $sql = "SELECT job_postings.job_id, job_postings.title 
            FROM job_postings 
            WHERE job_postings.employer_id = $user_id";
}
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Feedback</h2>
        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>
        <form action="feedback.php" method="POST">
            <div class="mb-3">
                <label for="job_id" class="form-label">Select Job</label>
                <select class="form-select" id="job_id" name="job_id" required>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='{$row['job_id']}'>{$row['title']}</option>";
                        }
                    } else {
                        echo "<option disabled>No jobs found.</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="rating" class="form-label">Rating</label>
                <select class="form-select" id="rating" name="rating" required>
                    <option value="1">1 - Poor</option>
                    <option value="2">2 - Fair</option>
                    <option value="3">3 - Good</option>
                    <option value="4">4 - Very Good</option>
                    <option value="5">5 - Excellent</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="comments" class="form-label">Comments</label>
                <textarea class="form-control" id="comments" name="comments" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Submit Feedback</button>
            <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>