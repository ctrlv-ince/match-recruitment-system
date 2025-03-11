<?php
session_start();
include '../db.php';

// Redirect if not logged in as a job seeker
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'job_seeker') {
    header("Location: login.php");
    exit();
}

$offer_id = $_GET['offer_id'];

// Fetch the job offer details
$sql = "SELECT job_offers.*, job_postings.title 
        FROM job_offers 
        JOIN job_postings ON job_offers.job_id = job_postings.job_id 
        WHERE job_offers.offer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $offer_id);
$stmt->execute();
$result = $stmt->get_result();
$offer = $result->fetch_assoc();

if (!$offer) {
    die("No offer found for ID: $offer_id");
}

$job_title = $offer['title'];
$offer_details = $offer['offer_details'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Respond to Job Offer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Respond to Job Offer</h2>
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title"><?php echo $job_title; ?></h5>
                <p class="card-text"><?php echo $offer_details; ?></p>
            </div>
        </div>

        <form action="respond_offer_handler.php" method="POST">
            <input type="hidden" name="offer_id" value="<?php echo $offer_id; ?>">
            <div class="mb-3">
                <label class="form-label">Your Response:</label>
                <div>
                    <button type="submit" name="response" value="accepted" class="btn btn-success me-2">Accept Offer</button>
                    <button type="submit" name="response" value="declined" class="btn btn-danger">Decline Offer</button>
                </div>
            </div>
        </form>

        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</body>
</html>