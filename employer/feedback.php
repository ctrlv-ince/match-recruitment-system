<?php
session_start();
include 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

// Fetch the user's role from the users table
$sql = "SELECT user_type, full_name, profile_img FROM users WHERE user_id = $user_id";
$result = $conn->query($sql);
$user = $result->fetch_assoc();
$user_type = $user['user_type']; // Get the user's role (job_seeker or employer)
$user_name = $user['full_name']; 
$profile_img = $user['profile_img'] ?: 'assets/images/default-avatar.png';

// Check if the user has a hired application
$sql = "SELECT a.*, j.job_title, e.company_name, u.full_name as other_name, u.profile_img as other_img
        FROM applications a
        JOIN jobs j ON a.job_id = j.job_id
        JOIN employers e ON j.employer_id = e.employer_id
        JOIN users u ON (a.seeker_id = u.user_id OR a.employer_id = u.user_id) AND u.user_id != $user_id
        WHERE (a.seeker_id = $user_id OR a.employer_id = $user_id) 
        AND a.status = 'hired'
        LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    // Redirect if no hired application is found
    header("Location: dashboard.php");
    exit();
}

$application = $result->fetch_assoc();
$job_title = $application['job_title'];
$company_name = $application['company_name'];
$other_name = $application['other_name'];
$other_img = $application['other_img'] ?: 'assets/images/default-avatar.png';

// Determine who the user is giving feedback for
$feedback_for = ($user_type == 'job_seeker') ? 'employer' : 'candidate';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = $_POST['rating'];
    $comments = mysqli_real_escape_string($conn, $_POST['comments']); // Sanitize input
    $application_id = $application['application_id'];
    $receiver_id = ($user_type == 'job_seeker') ? $application['employer_id'] : $application['seeker_id'];

    // Insert feedback into the database
    $sql = "INSERT INTO feedback (user_id, receiver_id, application_id, rating, comments) 
            VALUES ($user_id, $receiver_id, $application_id, $rating, '$comments')";
    if ($conn->query($sql) === TRUE) {
        $message = "success";
        
        // Update application to mark feedback as given
        $feedback_column = ($user_type == 'job_seeker') ? 'seeker_feedback_given' : 'employer_feedback_given';
        $update_sql = "UPDATE applications SET $feedback_column = 1 WHERE application_id = $application_id";
        $conn->query($update_sql);
    } else {
        $message = "error";
    }
}
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
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .card {
            border-radius: 10px;
            border: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
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
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
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
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-briefcase me-2"></i>GoSeekr
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="post_job.php">Post a Job</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="notifications.php">Notifications</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <!-- <a href="profile.php" class="text-decoration-none me-3">
                        <img src="<?php //echo $profile_img; ?>" alt="Profile" class="rounded-circle" width="32" height="32">
                    </a> -->
                    <a href="logout.php" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                    </a>
                </div>
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
                    <div class="profile-header d-flex align-items-center mb-4">
                        <img src="<?php echo $other_img; ?>" alt="<?php echo $other_name; ?>" class="profile-img me-3">
                        <div>
                            <h5 class="mb-1"><?php echo $other_name; ?></h5>
                            <p class="text-muted mb-0">
                                <?php if ($feedback_for == 'employer'): ?>
                                    <?php echo $company_name; ?>
                                <?php else: ?>
                                    Job Applicant
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                
                    <div class="card">
                        <div class="card-header">
                            <h4 class="feedback-title mb-0">
                                <?php if ($feedback_for == 'employer'): ?>
                                    Share Your Experience with this Employer
                                <?php else: ?>
                                    Rate this Candidate
                                <?php endif; ?>
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="job-details mb-4">
                                <h5 class="mb-2"><?php echo $job_title; ?></h5>
                                <p class="mb-0"><?php echo $company_name; ?></p>
                            </div>
                            
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
    
    <!-- Footer -->
    <footer class="footer mt-auto">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; 2023 GoSeekr. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

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
</html>