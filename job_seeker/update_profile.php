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

    // Update skills in the job_seekers table
    $sql = "UPDATE job_seekers SET skills = '$skills' WHERE seeker_id = $user_id";
    if ($conn->query($sql) === TRUE) {
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
        <form action="update_profile.php" method="POST">
            <div class="mb-3">
                <label for="skills" class="form-label">Your Skills</label>
                <input type="text" class="form-control" id="skills" name="skills" value="<?php echo $profile['skills']; ?>" required>
                <small class="form-text text-muted">Separate skills with commas (e.g., PHP, MySQL, JavaScript).</small>
            </div>
            <button type="submit" class="btn btn-primary">Update Profile</button>
            <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>