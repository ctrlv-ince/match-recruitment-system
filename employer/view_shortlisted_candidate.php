<?php
session_start();
include '../db.php';

// Redirect if not logged in as an employer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'employer') {
    header("Location: login.php");
    exit();
}

$seeker_id = $_GET['seeker_id'];
$job_id = $_GET['job_id'];

// Fetch candidate details
$sql = "SELECT users.full_name, users.email, job_seekers.skills, job_seekers.resume_image 
        FROM users 
        JOIN job_seekers ON users.user_id = job_seekers.seeker_id 
        WHERE users.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $seeker_id);
$stmt->execute();
$result = $stmt->get_result();
$candidate = $result->fetch_assoc();

if (!$candidate) {
    die("No candidate found for ID: $seeker_id");
}

// Fetch job title
$sql = "SELECT title FROM job_postings WHERE job_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $job_id);
$stmt->execute();
$result = $stmt->get_result();
$job = $result->fetch_assoc();

if (!$job) {
    die("No job found for ID: $job_id");
}

$job_title = $job['title'];

// Fetch uploaded documents for this candidate's application
$sql = "SELECT application_documents.document_type, application_documents.document_path 
        FROM application_documents 
        JOIN applications ON application_documents.application_id = applications.application_id 
        WHERE applications.seeker_id = ? AND applications.job_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $seeker_id, $job_id);
$stmt->execute();
$result = $stmt->get_result();
$documents = $result->fetch_all(MYSQLI_ASSOC);
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
        <p><strong>Name:</strong> <?php echo $candidate['full_name']; ?></p>
        <p><strong>Email:</strong> <?php echo $candidate['email']; ?></p>
        <p><strong>Skills:</strong> <?php echo $candidate['skills']; ?></p>

        <h3>Uploaded Documents</h3>
        <?php if (!empty($documents)): ?>
            <ul>
                <?php foreach ($documents as $document): ?>
                    <li>
                        <strong><?php echo ucfirst($document['document_type']); ?>:</strong>
                        <a href="../job_seeker/<?php echo $document['document_path']; ?>" target="_blank">View Document</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No documents uploaded for this application.</p>
        <?php endif; ?>

        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</body>
</html>