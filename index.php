<?php
session_start();
include 'db.php';

// Fetch popular jobs (recently posted and approved)
$sql = "SELECT * FROM job_postings WHERE status = 'approved' ORDER BY created_at DESC LIMIT 5";
$jobs_result = $conn->query($sql);

// Fetch recent feedbacks
$sql = "SELECT feedback.*, users.full_name 
        FROM feedback 
        JOIN users ON feedback.user_id = users.user_id 
        ORDER BY feedback.created_at DESC LIMIT 5";
$feedback_result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recruitment Agency Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card {
            transition: transform 0.2s;
        }
        .card:hover {
            transform: scale(1.02);
        }
        .testimonial-card {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Recruitment Agency</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="job_seeker/login.php">Job Seeker Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="employer/login.php">Employer Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin/login.php">Admin Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="bg-primary text-white py-5">
        <div class="container text-center">
            <h1 class="display-4">Find Your Dream Job or Hire Top Talent</h1>
            <p class="lead">Join our platform to connect job seekers and employers seamlessly.</p>
            <a href="job_seeker/register.php" class="btn btn-light btn-lg me-3">Job Seeker Sign Up</a>
            <a href="employer/register.php" class="btn btn-outline-light btn-lg">Employer Sign Up</a>
        </div>
    </div>

    <!-- Popular Jobs Section -->
    <div class="container my-5">
        <h2 class="text-center mb-4">Popular Jobs</h2>
        <div class="row">
            <?php
            if ($jobs_result->num_rows > 0) {
                while ($row = $jobs_result->fetch_assoc()) {
                    echo "<div class='col-md-4 mb-4'>";
                    echo "<div class='card h-100'>";
                    echo "<div class='card-body'>";
                    echo "<h5 class='card-title'>{$row['title']}</h5>";
                    echo "<p class='card-text'>{$row['description']}</p>";
                    echo "<p class='card-text'><strong>Skills:</strong> {$row['skills']}</p>";
                    echo "<a href='job_seeker/view_job.php?id={$row['job_id']}' class='btn btn-primary'>View Details</a>";
                    echo "</div>";
                    echo "</div>";
                    echo "</div>";
                }
            } else {
                echo "<p class='text-center'>No jobs found.</p>";
            }
            ?>
        </div>
    </div>

    <!-- Testimonials Section -->
    <div class="bg-light py-5">
        <div class="container">
            <h2 class="text-center mb-4">What Our Users Say</h2>
            <div class="row">
                <?php
                if ($feedback_result->num_rows > 0) {
                    while ($row = $feedback_result->fetch_assoc()) {
                        echo "<div class='col-md-4 mb-4'>";
                        echo "<div class='testimonial-card'>";
                        echo "<h5>{$row['full_name']}</h5>";
                        echo "<p>{$row['comments']}</p>";
                        echo "<small class='text-muted'>Rating: {$row['rating']}/5</small>";
                        echo "</div>";
                        echo "</div>";
                    }
                } else {
                    echo "<p class='text-center'>No feedback found.</p>";
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <p>&copy; 2023 Recruitment Agency. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>