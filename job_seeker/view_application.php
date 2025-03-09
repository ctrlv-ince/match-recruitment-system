<?php
session_start();
include '../db.php';

// Redirect if not logged in as a job seeker
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'job_seeker') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: view_applications.php");
    exit();
}

$application_id = $_GET['id'];
$seeker_id = $_SESSION['user_id'];

// Fetch application details
$sql = "SELECT applications.*, job_postings.title 
        FROM applications 
        JOIN job_postings ON applications.job_id = job_postings.job_id 
        WHERE applications.application_id = $application_id AND applications.seeker_id = $seeker_id";
$application_result = $conn->query($sql);
$application = $application_result->fetch_assoc();

if (!$application) {
    die("Invalid application ID.");
}

// Fetch documents for the application
$sql = "SELECT * FROM application_documents WHERE application_id = $application_id";
$documents_result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2><?php echo $application['title']; ?></h2>
        <p><strong>Applied At:</strong> <?php echo $application['applied_at']; ?></p>
        <p><strong>Status:</strong> <?php echo ucfirst($application['status']); ?></p>

        <h3>Uploaded Documents</h3>
        <?php if ($documents_result->num_rows > 0): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Document Type</th>
                        <th>File</th>
                        <th>Uploaded At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($document = $documents_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo ucfirst($document['document_type']); ?></td>
                            <td><a href="<?php echo $document['document_path']; ?>" target="_blank">View Document</a></td>
                            <td><?php echo $document['uploaded_at']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No documents uploaded for this application.</p>
        <?php endif; ?>

        <a href="view_applications.php" class="btn btn-secondary">Back to Applications</a>
    </div>
</body>
</html>