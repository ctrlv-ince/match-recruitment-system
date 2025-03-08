<?php
session_start();
include '../db.php';

// Redirect if not logged in as an employer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'employer') {
    header("Location: login.php");
    exit();
}

$employer_id = $_SESSION['user_id'];
$message = '';

// Fetch shortlisted candidates for this employer's job postings
$sql = "SELECT applications.application_id, users.full_name, job_postings.title 
        FROM applications 
        JOIN users ON applications.seeker_id = users.user_id 
        JOIN job_postings ON applications.job_id = job_postings.job_id 
        WHERE job_postings.employer_id = $employer_id AND applications.status = 'shortlisted'";
$result = $conn->query($sql);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $application_id = $_POST['application_id'];
    $scheduled_date = $_POST['scheduled_date'];

    // Insert interview schedule into the database
    $sql = "INSERT INTO interviews (application_id, scheduled_date, status) 
            VALUES ($application_id, '$scheduled_date', 'pending')";
    if ($conn->query($sql) === TRUE) {
        // Update application status to 'interview_scheduled'
        $sql = "UPDATE applications SET status = 'interview_scheduled' WHERE application_id = $application_id";
        if ($conn->query($sql) === TRUE) {
            $message = "Interview scheduled successfully!";
        } else {
            $message = "Error updating application status: " . $conn->error;
        }
    } else {
        $message = "Error scheduling interview: " . $conn->error;
    }
}

if ($conn->query($sql) === TRUE) {
    // Update application status to 'interview_scheduled'
    $sql = "UPDATE applications SET status = 'interview_scheduled' WHERE application_id = $application_id";
    if ($conn->query($sql) === TRUE) {
        // Add notification for the job seeker
        $seeker_id = $conn->query("SELECT seeker_id FROM applications WHERE application_id = $application_id")->fetch_assoc()['seeker_id'];
        $message = "An interview has been scheduled for you. Please check your interview schedule.";
        $sql = "INSERT INTO notifications (user_id, message) VALUES ($seeker_id, '$message')";
        $conn->query($sql);

        $message = "Interview scheduled successfully!";
    } else {
        $message = "Error updating application status: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Interview</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Schedule Interview</h2>
        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>
        <form action="schedule_interview.php" method="POST">
            <div class="mb-3">
                <label for="application_id" class="form-label">Select Candidate</label>
                <select class="form-select" id="application_id" name="application_id" required>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='{$row['application_id']}'>{$row['full_name']} - {$row['title']}</option>";
                        }
                    } else {
                        echo "<option disabled>No shortlisted candidates found.</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="scheduled_date" class="form-label">Interview Date and Time</label>
                <input type="datetime-local" class="form-control" id="scheduled_date" name="scheduled_date" required>
            </div>
            <button type="submit" class="btn btn-primary">Schedule Interview</button>
            <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>