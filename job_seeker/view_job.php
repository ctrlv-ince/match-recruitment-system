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
$user_id = $_SESSION['user_id'];
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
// Fetch the number of unread notifications
$sql = "SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = $user_id AND status = 'unread'";
$unread_count = $conn->query($sql)->fetch_assoc()['unread_count'];

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0073b1;
            --secondary-color: #0a66c2;
            --accent-color: #f5f5f5;
            --text-muted: #666;
            --border-color: #e0e0e0;
        }

        body {
            background-color: #f3f2ef;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 30px;
        }

        h2 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        h2 i {
            font-size: 1.5rem;
            color: var(--primary-color);
        }

        .job-detail {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text-muted);
        }

        .job-detail i {
            font-size: 1.2rem;
            color: var(--primary-color);
        }

        .alert {
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert i {
            font-size: 1.2rem;
        }

        .alert-info {
            background-color: #e9f5fe;
            border-color: #b3d9ff;
            color: #004085;
        }

        .alert-success {
            background-color: #e6f4ea;
            border-color: #b3e6cc;
            color: #155724;
        }

        .alert-warning {
            background-color: #fff3cd;
            border-color: #ffeeba;
            color: #856404;
        }

        .form-label {
            font-weight: 600;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-label i {
            font-size: 1.2rem;
            color: var(--primary-color);
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid var(--border-color);
            padding: 10px;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(0, 115, 177, 0.2);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-primary:hover {
            background-color: #005d91;
            border-color: #005d91;
        }

        .btn-secondary {
            background-color: var(--text-muted);
            border-color: var(--text-muted);
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-secondary:hover {
            background-color: #555;
            border-color: #555;
        }

        .document-type-select {
            margin-bottom: 15px;
        }

        .document-type-select label {
            font-weight: 600;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .document-type-select select {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }

        .brand-logo {
            font-size: 24px;
            font-weight: bold;
            color: var(--primary-color);
        }
    </style>
</head>

<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand brand-logo" href="dashboard.php">
                <i class="fas fa-briefcase"></i> GoSeekr
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="search_jobs.php">
                            <i class="fas fa-search"></i> Jobs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="view_interview.php">
                            <i class="fas fa-calendar-check"></i> Interviews
                        </a>
                    </li>
                    <li class="nav-item nav-notification">
                        <a class="nav-link position-relative" href="notifications.php">
                            <i class="fas fa-bell"></i> Notifications
                            <?php if ($unread_count > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    <?php echo $unread_count; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="update_profile.php">
                            <i class="fas fa-user-edit"></i> Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="view_applications.php">
                            <i class="fas fa-file-alt"></i> Applications
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-5">
        <h2><i class="fas fa-briefcase"></i><?php echo $job['title']; ?></h2>

        <div class="job-detail">
            <i class="fas fa-file-alt"></i>
            <p><strong>Description:</strong> <?php echo $job['description']; ?></p>
        </div>

        <div class="job-detail">
            <i class="fas fa-clipboard-list"></i>
            <p><strong>Requirements:</strong> <?php echo $job['requirements']; ?></p>
        </div>

        <div class="job-detail">
            <i class="fas fa-tools"></i>
            <p><strong>Skills:</strong> <?php echo $job['skills']; ?></p>
        </div>

        <div class="job-detail">
            <i class="fas fa-map-marker-alt"></i>
            <p><strong>Location:</strong> <?php echo $job['location']; ?></p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i><?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($has_applied): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>You have already applied for this job.
            </div>
        <?php else: ?>
            <?php if ($skills_match || $location_match): ?>
                <form action="view_job.php?id=<?php echo $job_id; ?>" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="documents" class="form-label">
                            <i class="fas fa-file-upload"></i>Upload Required Documents
                        </label>
                        <input type="file" class="form-control" id="documents" name="documents[]" multiple required>
                        <small class="form-text text-muted">Upload documents (e.g., certifications, IDs) as required by the job.</small>
                    </div>
                    <div id="documentTypes"></div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i>Apply Now
                    </button>
                </form>
            <?php else: ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>Your skills or location do not match the job requirements. You can still apply, but your application may be rejected.
                </div>
                <form action="view_job.php?id=<?php echo $job_id; ?>" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="documents" class="form-label">
                            <i class="fas fa-file-upload"></i>Upload Required Documents
                        </label>
                        <input type="file" class="form-control" id="documents" name="documents[]" multiple required>
                        <small class="form-text text-muted">Upload documents (e.g., certifications, IDs) as required by the job.</small>
                    </div>
                    <div id="documentTypes"></div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i>Apply Now
                    </button>
                </form>
            <?php endif; ?>
        <?php endif; ?>

        <a href="search_jobs.php" class="btn btn-secondary mt-3">
            <i class="fas fa-arrow-left"></i>Back to Job Listings
        </a>
    </div>

    <script>
        // Dynamically add document type fields for each uploaded file
        document.getElementById('documents').addEventListener('change', function() {
            const documentTypesDiv = document.getElementById('documentTypes');
            documentTypesDiv.innerHTML = ''; // Clear previous fields

            Array.from(this.files).forEach((file, index) => {
                const div = document.createElement('div');
                div.className = 'mb-3 document-type-select';
                div.innerHTML = `
                    <label for="document_type_${index}" class="form-label">
                        <i class="fas fa-file"></i>Document Type for ${file.name}
                    </label>
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
<!-- Footer -->
<footer class="footer mt-auto">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <p class="mb-0">&copy; 2025 GoSeekr. All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>

</html>