<?php
session_start();
include '../db.php';

// Redirect if not logged in as an employer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'employer') {
    header("Location: login.php");
    exit();
}

$message = ''; // To display success or error messages

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $requirements = $_POST['requirements'];
    $skills = $_POST['skills'];
    $location = $_POST['location'];
    $quota = $_POST['quota']; // New field for job quota
    $employer_id = $_SESSION['user_id'];

    // Insert job posting into the database
    $sql = "INSERT INTO job_postings (employer_id, title, description, requirements, skills, location, quota, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $message = "Error preparing statement: " . $conn->error;
    } else {
        $stmt->bind_param("isssssi", $employer_id, $title, $description, $requirements, $skills, $location, $quota);
        if ($stmt->execute()) {
            $message = "Job posted successfully! Waiting for admin approval.";
        } else {
            $message = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a Job | GoSeekr</title>
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
        
        .form-label {
            font-weight: 500;
            color: #333;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(0, 115, 177, 0.25);
        }
        
        .alert-success {
            background-color: #e6f7e6;
            color: #2e7d32;
            border-color: #c8e6c9;
        }
        
        .form-text {
            color: var(--text-muted);
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
                        <a class="nav-link active" href="post_job.php"><i class="fas fa-briefcase"></i> Post a Job</a>
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
                <h2 class="mb-0">Post a Job</h2>
            </div>
            <p class="text-muted mb-0">Create a new job listing for potential candidates</p>
        </div>
        
        <?php if ($message): ?>
            <div class="alert <?php echo strpos($message, "successfully") !== false ? 'alert-success' : 'alert-danger'; ?> d-flex align-items-center" role="alert">
                <i class="<?php echo strpos($message, "successfully") !== false ? 'fas fa-check-circle me-2' : 'fas fa-exclamation-circle me-2'; ?>"></i>
                <div><?php echo $message; ?></div>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Job Details</h5>
            </div>
            <div class="card-body">
                <form action="post_job.php" method="POST">
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="title" class="form-label">Job Title</label>
                            <input type="text" class="form-control" id="title" name="title" placeholder="E.g., Senior Web Developer" required>
                        </div>
                        <div class="col-md-4">
                            <label for="quota" class="form-label">Number of Vacancies</label>
                            <input type="number" class="form-control" id="quota" name="quota" min="1" value="1" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="location" class="form-label">Branch Location</label>
                        <input type="text" class="form-control" id="location" name="location" placeholder="E.g., Manila, Philippines" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Job Description</label>
                        <textarea class="form-control" id="description" name="description" rows="5" placeholder="Provide a detailed description of the job role and responsibilities..." required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="requirements" class="form-label">Requirements</label>
                        <textarea class="form-control" id="requirements" name="requirements" rows="4" placeholder="List the educational background, experience, and other requirements..." required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="skills" class="form-label">Required Skills</label>
                        <input type="text" class="form-control" id="skills" name="skills" placeholder="E.g., PHP, MySQL, JavaScript" required>
                        <small class="form-text text-muted">Separate skills with commas (e.g., PHP, MySQL, JavaScript).</small>
                    </div>
                    
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-1"></i> Post Job
                        </button>
                        <a href="dashboard.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i> Cancel
                        </a>
                    </div>
                </form>
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
</body>
</html>