<?php
session_start();
include '../db.php';

// Redirect if not logged in as an employer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'employer') {
    header("Location: login.php");
    exit();
}

$notification_id = $_GET['notification_id'];

// Fetch the notification details
$sql = "SELECT * FROM notifications WHERE notification_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $notification_id);
$stmt->execute();
$result = $stmt->get_result();
$notification = $result->fetch_assoc();

// Fetch the candidate and job details
$sql = "SELECT applications.seeker_id, applications.job_id, job_postings.title 
        FROM applications 
        JOIN job_postings ON applications.job_id = job_postings.job_id 
        WHERE applications.application_id = (
            SELECT application_id FROM interviews WHERE interview_id = (
                SELECT interview_id FROM notifications WHERE notification_id = ?
            )
        )";
$stmt2 = $conn->prepare($sql);
$stmt2->bind_param("i", $notification_id);
$stmt2->execute();
$result = $stmt2->get_result();
$details = $result->fetch_assoc();

$seeker_id = $details['seeker_id'];
$job_id = $details['job_id'];
$job_title = $details['title'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidate Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Candidate Details</h2>
        <p><strong>Job Title:</strong> <?php echo $job_title; ?></p>
        <p><strong>Candidate ID:</strong> <?php echo $seeker_id; ?></p>

        <h3>Send Job Offer</h3>
        <form id="offerForm">
            <input type="hidden" name="seeker_id" value="<?php echo $seeker_id; ?>">
            <input type="hidden" name="job_id" value="<?php echo $job_id; ?>">
            <div class="mb-3">
                <label for="offer_details" class="form-label">Offer Details</label>
                <textarea class="form-control" id="offer_details" name="offer_details" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Send Offer</button>
        </form>
        <div id="responseMessage" class="mt-3"></div>
    </div>

    <script>
        document.getElementById('offerForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('send_offer.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('responseMessage').innerHTML = `
                    <div class="alert alert-${data.status}">${data.message}</div>
                `;
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    </script>
</body>
</html>