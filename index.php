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
    <title>GoSeekr</title>
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

        .hero-section {
            background-color: var(--primary-color);
            color: white;
            padding: 60px 0;
            text-align: center;
        }

        .hero-section h1 {
            font-size: 2.5rem;
            font-weight: 700;
        }

        .hero-section p {
            font-size: 1.2rem;
            margin-bottom: 20px;
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

        .card {
            border: none;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-radius: 8px;
            margin-bottom: 20px;
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .card-header {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            padding: 15px 20px;
            border-radius: 8px 8px 0 0 !important;
        }

        .testimonial-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
            border-left: 3px solid var(--primary-color);
        }

        .testimonial-card h5 {
            color: var(--primary-color);
        }

        .footer {
            background-color: white;
            padding: 20px 0;
            border-top: 1px solid var(--border-color);
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-briefcase"></i> GoSeekr
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
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
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <h1>Find Your Dream Job or Hire Top Talent</h1>
            <p>Join our platform to connect job seekers and employers seamlessly.</p>
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
    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 GoSeekr. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>