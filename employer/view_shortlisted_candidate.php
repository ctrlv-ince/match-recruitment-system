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
$sql = "SELECT users.full_name, users.email, job_seekers.skills
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
    <title>Candidate Details | GoSeekr</title>
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
        
        .navbar {
            background-color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 10px 0;
        }
        
        .navbar-brand {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .navbar-brand span {
            color: #333;
        }
        
        .card {
            border: none;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .card-header {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            padding: 15px 20px;
            border-radius: 8px 8px 0 0 !important;
        }
        
        .profile-image {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin-right: 20px;
        }
        
        .skill-badge {
            background-color: #e9f3ff;
            color: var(--secondary-color);
            padding: 5px 12px;
            border-radius: 20px;
            margin-right: 8px;
            margin-bottom: 8px;
            display: inline-block;
            font-size: 0.85rem;
        }
        
        .document-card {
            display: flex;
            align-items: center;
            padding: 15px;
            background-color: white;
            border-radius: 8px;
            margin-bottom: 10px;
            border-left: 3px solid var(--primary-color);
            transition: all 0.2s ease;
        }
        
        .document-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .document-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background-color: rgba(10, 102, 194, 0.1);
            color: var(--secondary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }
        
        .document-details {
            flex-grow: 1;
        }
        
        .document-title {
            font-weight: 600;
            margin-bottom: 2px;
            color: #333;
        }
        
        .document-action {
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
        }
        
        .document-action:hover {
            text-decoration: underline;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #005d91;
            border-color: #005d91;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Go<span>Seekr</span></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="job_postings.php"><i class="fas fa-briefcase"></i> Jobs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="candidates.php"><i class="fas fa-users"></i> Candidates</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="notifications.php"><i class="fas fa-bell"></i> Notifications</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Candidate Details</h4>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-4">
                    <div class="profile-image">
                        <?php 
                            $initials = strtoupper(substr($candidate['full_name'], 0, 1));
                            echo $initials;
                        ?>
                    </div>
                    <div>
                        <h3 class="mb-1"><?php echo $candidate['full_name']; ?></h3>
                        <p class="text-muted mb-1">
                            <i class="fas fa-envelope me-2"></i><?php echo $candidate['email']; ?>
                        </p>
                        <p class="text-muted mb-0">
                            <i class="fas fa-briefcase me-2"></i><?php echo $job_title; ?>
                        </p>
                    </div>
                </div>
                
                <div class="mb-4">
                    <h5 class="mb-3">Skills</h5>
                    <div>
                        <?php 
                            $skills = explode(',', $candidate['skills']);
                            foreach ($skills as $skill) {
                                echo '<span class="skill-badge">' . trim($skill) . '</span>';
                            }
                        ?>
                    </div>
                </div>
                
                <div class="mb-4">
                    <h5 class="mb-3">Uploaded Documents</h5>
                    <?php if (!empty($documents)): ?>
                        <?php foreach ($documents as $document): ?>
                            <div class="document-card">
                                <div class="document-icon">
                                    <?php 
                                        if ($document['document_type'] == 'resume') {
                                            echo '<i class="fas fa-file-alt"></i>';
                                        } elseif ($document['document_type'] == 'cover_letter') {
                                            echo '<i class="fas fa-file-word"></i>';
                                        } elseif ($document['document_type'] == 'certification') {
                                            echo '<i class="fas fa-certificate"></i>';
                                        } else {
                                            echo '<i class="fas fa-file"></i>';
                                        }
                                    ?>
                                </div>
                                <div class="document-details">
                                    <div class="document-title">
                                        <?php echo ucfirst(str_replace('_', ' ', $document['document_type'])); ?>
                                    </div>
                                </div>
                                <a href="../job_seeker/<?php echo $document['document_path']; ?>" target="_blank" class="document-action">
                                    <span>View Document</span>
                                    <i class="fas fa-external-link-alt ms-1"></i>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-info">No documents uploaded for this application.</div>
                    <?php endif; ?>
                </div>
                
                <div class="text-end">
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>