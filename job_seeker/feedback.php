<?php
session_start();
include '../db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

// Fetch the user's role from the users table
$sql = "SELECT user_type FROM users WHERE user_id = $user_id";
$result = $conn->query($sql);
$user = $result->fetch_assoc();
$user_type = $user['user_type']; // Get the user's role (job_seeker or employer)

// Check if the user has a hired application
$sql = "SELECT * FROM applications 
        WHERE (seeker_id = $user_id OR job_id = $user_id) 
        AND status = 'hired'";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    // Redirect if no hired application is found
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = $_POST['rating'];
    $comments = $_POST['comments'];

    // Insert feedback into the database
    $sql = "INSERT INTO feedback (user_id, rating, comments) 
            VALUES ($user_id, $rating, '$comments')";
    if ($conn->query($sql) === TRUE) {
        $message = "success";
    } else {
        $message = "error";
    }
}
// Fetch the number of unread notifications
$sql = "SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = $user_id AND status = 'unread'";
$unread_count = $conn->query($sql)->fetch_assoc()['unread_count'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Share Your Experience | GoSeekr</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0a66c2;
            --secondary-color: #057642;
            --light-gray: #f3f2ef;
            --dark-gray: #666666;
        }

        body {
            background-color: var(--light-gray);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background-color: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-weight: bold;
            color: var(--primary-color);
        }

        .card {
            border-radius: 10px;
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background-color: white;
            border-bottom: 1px solid #eaeaea;
            padding: 20px;
            border-radius: 10px 10px 0 0 !important;
        }

        .feedback-title {
            font-weight: 600;
            color: #292929;
        }

        .profile-header {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .profile-img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 10px 24px;
            font-weight: 600;
        }

        .btn-secondary {
            background-color: white;
            border-color: var(--primary-color);
            color: var(--primary-color);
            padding: 10px 24px;
            font-weight: 600;
        }

        .rating-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 30px;
        }

        .rating-stars {
            font-size: 2rem;
            color: #ddd;
            cursor: pointer;
        }

        .rating-stars .fas {
            color: #ffb400;
        }

        .rating-text {
            margin-top: 10px;
            font-weight: 500;
        }

        .feedback-icon {
            font-size: 48px;
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .success-message {
            text-align: center;
            padding: 40px 20px;
        }

        .footer {
            background-color: white;
            padding: 20px 0;
            border-top: 1px solid #eaeaea;
            margin-top: 40px;
        }

        .job-details {
            background-color: rgba(10, 102, 194, 0.1);
            border-left: 4px solid var(--primary-color);
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
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
            <a class="navbar-brand brand-logo" href="#">
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
                        <a class="nav-link" href="notifications.php">
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
                            <i class="fas fa-file-alt"></i></i> Applications
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

    <!-- Main Content -->
    <div class="container mt-4 mb-5">
        <?php if ($message == 'success'): ?>
            <div class="card">
                <div class="success-message">
                    <i class="fas fa-check-circle feedback-icon text-success"></i>
                    <h3>Thank You for Your Feedback!</h3>
                    <p class="text-muted mb-4">Your feedback helps build a better community for everyone.</p>
                    <a href="dashboard.php" class="btn btn-primary">Return to Dashboard</a>
                </div>
            </div>
        <?php elseif ($message == 'error'): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                There was an error submitting your feedback. Please try again later.
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="feedback-title mb-0">Share Your Experience</h4>
                        </div>
                        <div class="card-body">
                            <form action="feedback.php" method="POST" id="feedbackForm">
                                <input type="hidden" id="rating" name="rating" value="0">

                                <div class="rating-container">
                                    <p class="mb-2 text-center">How would you rate your experience?</p>
                                    <div class="rating-stars mb-2">
                                        <i class="far fa-star" data-rating="1"></i>
                                        <i class="far fa-star" data-rating="2"></i>
                                        <i class="far fa-star" data-rating="3"></i>
                                        <i class="far fa-star" data-rating="4"></i>
                                        <i class="far fa-star" data-rating="5"></i>
                                    </div>
                                    <div class="rating-text">Select a rating</div>
                                </div>

                                <div class="mb-4">
                                    <label for="comments" class="form-label fw-bold">Share more about your experience</label>
                                    <textarea class="form-control" id="comments" name="comments" rows="5"
                                        placeholder="What was your experience like? Your feedback helps others make better decisions." required></textarea>
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="dashboard.php" class="btn btn-secondary me-md-2">Cancel</a>
                                    <button type="submit" class="btn btn-primary" id="submitBtn" disabled>Submit Feedback</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const stars = document.querySelectorAll('.rating-stars i');
            const ratingInput = document.getElementById('rating');
            const ratingText = document.querySelector('.rating-text');
            const submitBtn = document.getElementById('submitBtn');
            const ratingTexts = [
                'Select a rating',
                'Poor - Unsatisfactory experience',
                'Fair - Below average experience',
                'Good - Average experience',
                'Very Good - Above average experience',
                'Excellent - Outstanding experience'
            ];

            stars.forEach(star => {
                star.addEventListener('click', function() {
                    const rating = this.getAttribute('data-rating');
                    ratingInput.value = rating;

                    // Update star display
                    stars.forEach((s, index) => {
                        if (index < rating) {
                            s.classList.remove('far');
                            s.classList.add('fas');
                        } else {
                            s.classList.remove('fas');
                            s.classList.add('far');
                        }
                    });

                    // Update text and enable button
                    ratingText.textContent = ratingTexts[rating];
                    submitBtn.disabled = false;
                });

                // Hover effects
                star.addEventListener('mouseover', function() {
                    const rating = this.getAttribute('data-rating');

                    stars.forEach((s, index) => {
                        if (index < rating) {
                            s.classList.add('hover');
                        }
                    });
                });

                star.addEventListener('mouseout', function() {
                    stars.forEach(s => {
                        s.classList.remove('hover');
                    });
                });
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