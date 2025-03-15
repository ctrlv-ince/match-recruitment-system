<?php
session_start();
include '../db.php';

// Redirect if not logged in as a job seeker
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'job_seeker') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: search_jobs.php");
    exit();
}

$job_id = $_GET['id'];
$seeker_id = $_SESSION['user_id'];

// Fetch job details
$sql = "SELECT * FROM job_postings WHERE job_id = $job_id";
$result = $conn->query($sql);
$job = $result->fetch_assoc();

// Fetch job seeker details
$sql = "SELECT * FROM job_seekers WHERE seeker_id = $seeker_id";
$result = $conn->query($sql);
$seeker = $result->fetch_assoc();

// Check if the job seeker has already applied for this job
$sql = "SELECT * FROM applications WHERE job_id = $job_id AND seeker_id = $seeker_id";
$application_result = $conn->query($sql);
$has_applied = $application_result->num_rows > 0;

// Check if the job seeker's skills or location match the job requirements
$skills_match = false;
$location_match = false;

if ($seeker) {
    $seeker_skills = explode(',', $seeker['skills']);
    $job_skills = explode(',', $job['skills']);
    $skills_match = !empty(array_intersect($seeker_skills, $job_skills));

    $location_match = $seeker['location'] === $job['location'];
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$has_applied) {
    // Insert application into the database
    $sql = "INSERT INTO applications (job_id, seeker_id, status) VALUES ($job_id, $seeker_id, 'applied')";
    if ($conn->query($sql) === TRUE) {
        $application_id = $conn->insert_id; // Get the auto-generated application ID

        // Handle document uploads
        if (!empty($_FILES['documents']['name'][0])) {
            $uploadDir = "uploads/application_documents/$user_id/"; // Base directory to store uploaded files

            // Create the base directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true); // Create the directory if it doesn't exist
            }

            foreach ($_FILES['documents']['tmp_name'] as $key => $tmp_name) {
                $document_type = $_POST['document_types'][$key]; // Get the document type
                $typeDir = $uploadDir . $document_type . '/'; // Directory for the specific document type

                // Create the document type directory if it doesn't exist
                if (!is_dir($typeDir)) {
                    mkdir($typeDir, 0755, true); // Create the directory if it doesn't exist
                }

                $fileName = uniqid() . '_' . basename($_FILES['documents']['name'][$key]); // Unique file name
                $filePath = $typeDir . $fileName; // Full path to the file

                // Move the uploaded file to the target directory
                if (move_uploaded_file($tmp_name, $filePath)) {
                    // Insert document details into the database
                    $sql = "INSERT INTO application_documents (application_id, document_type, document_path) 
                            VALUES ($application_id, '$document_type', '$filePath')";
                    if (!$conn->query($sql)) {
                        $message = "Error uploading document: " . $conn->error;
                        break;
                    }
                } else {
                    $message = "Error moving uploaded file for document type: $document_type.";
                    break;
                }
            }

            if (empty($message)) {
                $message = "Application submitted successfully!";
            }
        } else {
            $message = "No documents were uploaded.";
        }

        $has_applied = true;
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
    <title>Job Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2><?php echo $job['title']; ?></h2>
        <p><strong>Description:</strong> <?php echo $job['description']; ?></p>
        <p><strong>Requirements:</strong> <?php echo $job['requirements']; ?></p>
        <p><strong>Skills:</strong> <?php echo $job['skills']; ?></p>
        <p><strong>Location:</strong> <?php echo $job['location']; ?></p>

        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if ($has_applied): ?>
            <div class="alert alert-success">You have already applied for this job.</div>
        <?php else: ?>
            <?php if ($skills_match || $location_match): ?>
                <form action="view_job.php?id=<?php echo $job_id; ?>" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="documents" class="form-label">Upload Required Documents</label>
                        <input type="file" class="form-control" id="documents" name="documents[]" multiple required>
                        <small class="form-text text-muted">Upload documents (e.g., certifications, IDs) as required by the job.</small>
                    </div>
                    <div id="documentTypes"></div>
                    <button type="submit" class="btn btn-primary">Apply Now</button>
                </form>
            <?php else: ?>
                <div class="alert alert-warning">Your skills or location do not match the job requirements. You can still apply, but your application may be rejected.</div>
                <form action="view_job.php?id=<?php echo $job_id; ?>" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="documents" class="form-label">Upload Required Documents</label>
                        <input type="file" class="form-control" id="documents" name="documents[]" multiple required>
                        <small class="form-text text-muted">Upload documents (e.g., certifications, IDs) as required by the job.</small>
                    </div>
                    <div id="documentTypes"></div>
                    <button type="submit" class="btn btn-primary">Apply Now</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>

        <a href="search_jobs.php" class="btn btn-secondary mt-3">Back to Job Listings</a>
    </div>

    <script>
        // Dynamically add document type fields for each uploaded file
        document.getElementById('documents').addEventListener('change', function () {
            const documentTypesDiv = document.getElementById('documentTypes');
            documentTypesDiv.innerHTML = ''; // Clear previous fields

            Array.from(this.files).forEach((file, index) => {
                const div = document.createElement('div');
                div.className = 'mb-3';
                div.innerHTML = `
                    <label for="document_type_${index}" class="form-label">Document Type for ${file.name}</label>
                    <select class="form-control" id="document_type_${index}" name="document_types[]" required>
                        <option value="valid_id">Valid ID</option>
                        <option value="certification">Certification</option>
                        <option value="resume">Resume</option>
                        <option value="other">Other</option>
                    </select>
                `;
                documentTypesDiv.appendChild(div);
            });
        });
    </script>
</body>
</html>