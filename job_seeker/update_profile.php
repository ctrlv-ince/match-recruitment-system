<?php
session_start();
include '../db.php';

// Redirect if not logged in as a job seeker
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'job_seeker') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

// Fetch current profile data
$sql = "SELECT * FROM job_seekers WHERE seeker_id = $user_id";
$result = $conn->query($sql);
$profile = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $skills = $_POST['skills'];
    $location = $_POST['location']; // Added location field

    // Update skills and location in the job_seekers table
    $sql = "UPDATE job_seekers SET skills = '$skills', location = '$location' WHERE seeker_id = $user_id";
    if ($conn->query($sql) === TRUE) {
        // Handle resume upload
        if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
            $file_name = $_FILES['resume']['name'];
            $file_tmp = $_FILES['resume']['tmp_name'];
            $upload_dir = "uploads/job_seekers/$user_id/resume/";

            // Create directory if it doesn't exist
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_path = $upload_dir . basename($file_name);

            // Move uploaded file
            if (move_uploaded_file($file_tmp, $file_path)) {
                // Update resume in job_seeker_documents table
                $sql = "UPDATE job_seeker_documents SET document_path = '$file_path' WHERE seeker_id = $user_id AND document_type = 'resume'";
                $conn->query($sql);
            }
        }

        $message = "Profile updated successfully!";
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
    <title>Update Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Update Your Profile</h2>
        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>
        <form action="update_profile.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="skills" class="form-label">Your Skills</label>
                <input type="text" class="form-control" id="skills" name="skills" value="<?php echo $profile['skills']; ?>" required>
                <small class="form-text text-muted">Separate skills with commas (e.g., PHP, MySQL, JavaScript).</small>
            </div>
            <div class="mb-3">
                <label for="location" class="form-label">Location</label>
                <input type="text" class="form-control" id="location" name="location" value="<?php echo $profile['location']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="resume" class="form-label">Update Resume/CV</label>
                <input type="file" class="form-control" id="resume" name="resume">
            </div>
            <button type="submit" class="btn btn-primary">Update Profile</button>
            <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>