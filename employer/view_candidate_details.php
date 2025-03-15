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

// Check if the candidate has been interviewed and recommended
$sql = "SELECT interviews.interview_id, interviews.recommendation 
        FROM interviews 
        JOIN applications ON interviews.application_id = applications.application_id 
        WHERE applications.seeker_id = ? AND applications.job_id = ? 
        AND interviews.status = 'completed' 
        AND interviews.recommendation = 'recommended'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $seeker_id, $job_id);
$stmt->execute();
$result = $stmt->get_result();
$interview = $result->fetch_assoc();

if (!$interview) {
    die("This candidate has not been interviewed and recommended by the admin.");
}

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

// Fetch application status and date
$sql = "SELECT status, applied_at FROM applications WHERE seeker_id = ? AND job_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $seeker_id, $job_id);
$stmt->execute();
$result = $stmt->get_result();
$application = $result->fetch_assoc();
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
        
        .page-header {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 20px;
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
        
        .profile-header {
            display: flex;
            align-items: center;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 20px;
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
        
        .profile-details h3 {
            margin-bottom: 5px;
            color: #333;
            font-weight: 600;
        }
        
        .profile-badge {
            background-color: rgba(10, 102, 194, 0.1);
            color: var(--secondary-color);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-right: 8px;
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
        
        .form-section {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #005d91;
            border-color: #005d91;
        }
        
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
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
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.85rem;
        }
        
        .status-recommended {
            background-color: #e6f7e6;
            color: #2e7d32;
        }
        
        .application-info {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
        }
        
        .application-info p {
            margin-bottom: 6px;
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
                        <a class="nav-link" href="dashboard.php"><i class="fas fa-home"></i> Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="post_job.php"><i class="fas fa-briefcase"></i>Post a Job</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="notifications.php"><i class="fas fa-bell"></i> Notifications</a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4">
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">Candidate Details</h2>
                <span class="status-badge status-recommended">
                    <i class="fas fa-check-circle"></i> Recommended
                </span>
            </div>
            <p class="text-muted mb-0">Job Position: <?php echo $job_title; ?></p>
        </div>
        
        <div class="row">
            <!-- Candidate Profile -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Candidate Profile</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <div class="profile-image mx-auto">
                                <?php 
                                    $initials = strtoupper(substr($candidate['full_name'], 0, 1));
                                    echo $initials;
                                ?>
                            </div>
                            <h4 class="mt-3"><?php echo $candidate['full_name']; ?></h4>
                            <p class="text-muted">
                                <i class="fas fa-envelope me-2"></i><?php echo $candidate['email']; ?>
                            </p>
                        </div>
                        
                        <div class="mb-4">
                            <h6 class="text-uppercase text-muted mb-3">Skills</h6>
                            <div>
                                <?php 
                                    $skills = explode(',', $candidate['skills']);
                                    foreach ($skills as $skill) {
                                        echo '<span class="skill-badge">' . trim($skill) . '</span>';
                                    }
                                ?>
                            </div>
                        </div>
                        
                        <?php if (isset($application)): ?>
                        <div class="application-info">
                            <h6 class="text-uppercase text-muted mb-3">Application Info</h6>
                            <p><strong>Status:</strong> <?php echo ucfirst($application['status']); ?></p>
                            <p><strong>Applied On:</strong> <?php echo date('M d, Y', strtotime($application['applied_at'])); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Documents and Offer Form -->
            <div class="col-md-8">
                <!-- Documents -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Uploaded Documents</h5>
                    </div>
                    <div class="card-body">
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
                                        <div class="text-muted small">
                                            Uploaded with application
                                        </div>
                                    </div>
                                    <a href="../job_seeker/<?php echo $document['document_path']; ?>" target="_blank" class="document-action">
                                        <span>View</span>
                                        <i class="fas fa-external-link-alt ms-1"></i>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-file-alt text-muted mb-3" style="font-size: 2rem;"></i>
                                <p class="mb-0">No documents uploaded for this application.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Send Job Offer Form -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Send Job Offer</h5>
                    </div>
                    <div class="card-body">
                        <form action="send_offer.php" method="POST">
                            <input type="hidden" name="seeker_id" value="<?php echo $seeker_id; ?>">
                            <input type="hidden" name="job_id" value="<?php echo $job_id; ?>">
                            
                            <div class="mb-3">
                                <label for="offer_details" class="form-label">Offer Details</label>
                                <textarea class="form-control" id="offer_details" name="offer_details" rows="5" placeholder="Describe the job offer details, including position, start date, benefits, and any other relevant information..." required></textarea>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="salary" class="form-label">Salary (Monthly)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₱</span>
                                        <input type="number" class="form-control" id="salary" name="salary" step="0.01" min="0" placeholder="50000.00" required>
                                    </div>
                                </div>
                                
                            </div>
                            
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-1"></i> Send Offer
                                </button>
                                <a href="dashboard.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="bg-white mt-5 py-4 border-top">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">© 2025 GoSeekr. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Set default start date to 2 weeks from today
        window.addEventListener('DOMContentLoaded', function() {
            const startDateInput = document.getElementById('start_date');
            if (startDateInput) {
                const twoWeeksFromNow = new Date();
                twoWeeksFromNow.setDate(twoWeeksFromNow.getDate() + 14);
                
                // Format the date as YYYY-MM-DD for the input
                const year = twoWeeksFromNow.getFullYear();
                const month = String(twoWeeksFromNow.getMonth() + 1).padStart(2, '0');
                const day = String(twoWeeksFromNow.getDate()).padStart(2, '0');
                
                startDateInput.value = `${year}-${month}-${day}`;
            }
        });
    </script>
</body>
</html>