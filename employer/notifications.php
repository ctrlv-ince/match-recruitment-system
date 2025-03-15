<?php
session_start();
include '../db.php';

// Redirect if not logged in as an employer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'employer') {
    header("Location: login.php");
    exit();
}

// Fetch all notifications for the logged-in employer
$employer_id = $_SESSION['user_id'];
$sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employer_id);
$stmt->execute();
$result = $stmt->get_result();

// Handle marking notifications as read or unread
if (isset($_GET['mark_as_read'])) {
    $notification_id = intval($_GET['mark_as_read']);
    $sql_update = "UPDATE notifications SET status = 'read' WHERE notification_id = ? AND user_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("ii", $notification_id, $employer_id);
    $stmt_update->execute();

    // Refresh the page to reflect the changes
    header("Location: notifications.php");
    exit();
}

if (isset($_GET['mark_as_unread'])) {
    $notification_id = intval($_GET['mark_as_unread']);
    $sql_update = "UPDATE notifications SET status = 'unread' WHERE notification_id = ? AND user_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("ii", $notification_id, $employer_id);
    $stmt_update->execute();

    // Refresh the page to reflect the changes
    header("Location: notifications.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications | GoSeekr</title>
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

        .notification-item {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 15px;
            padding: 15px;
            border-left: 3px solid var(--primary-color);
            transition: all 0.2s ease;
        }
        
        .notification-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .notification-item.unread {
            border-left: 3px solid #ffc107;
            background-color: rgba(255, 248, 230, 0.3);
        }

        .notification-time {
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        .notification-actions {
            margin-top: 10px;
        }

        .notification-message {
            margin-bottom: 8px;
            font-size: 1rem;
        }

        .notification-detail {
            color: var(--text-muted);
            margin-bottom: 5px;
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
                        <a class="nav-link active" href="notifications.php"><i class="fas fa-bell"></i> Notifications</a>
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
                <h2 class="mb-0">Notifications</h2>
            </div>
            <p class="text-muted mb-0">View and manage all your notifications</p>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">All Notifications</h5>
            </div>
            <div class="card-body">               
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($notification = $result->fetch_assoc()): ?>
                        <?php
                        // Fetch candidate name and job details if the notification is about a recommended candidate
                        $candidate_name = null;
                        $job_title = null;
                        if ($notification['message'] === "A candidate has been recommended for your job posting. Please review and decide whether to send a job offer.") {
                            // Fetch seeker_id and job_id from the interviews table
                            $interview_id = $notification['interview_id'];
                            $sql_interview = "SELECT a.seeker_id, a.job_id 
                                            FROM interviews i 
                                            JOIN applications a ON i.application_id = a.application_id 
                                            WHERE i.interview_id = ?";
                            $stmt_interview = $conn->prepare($sql_interview);
                            $stmt_interview->bind_param("i", $interview_id);
                            $stmt_interview->execute();
                            $interview_result = $stmt_interview->get_result();

                            if ($interview_result->num_rows > 0) {
                                $interview_data = $interview_result->fetch_assoc();
                                $seeker_id = $interview_data['seeker_id'];
                                $job_id = $interview_data['job_id'];

                                // Fetch candidate name
                                $sql_candidate = "SELECT full_name FROM users WHERE user_id = ?";
                                $stmt_candidate = $conn->prepare($sql_candidate);
                                $stmt_candidate->bind_param("i", $seeker_id);
                                $stmt_candidate->execute();
                                $candidate_result = $stmt_candidate->get_result();
                                if ($candidate_result->num_rows > 0) {
                                    $candidate_name = $candidate_result->fetch_assoc()['full_name'];
                                }

                                // Fetch job title
                                $sql_job = "SELECT title FROM job_postings WHERE job_id = ?";
                                $stmt_job = $conn->prepare($sql_job);
                                $stmt_job->bind_param("i", $job_id);
                                $stmt_job->execute();
                                $job_result = $stmt_job->get_result();
                                if ($job_result->num_rows > 0) {
                                    $job_title = $job_result->fetch_assoc()['title'];
                                }
                            }
                        }
                        ?>

                        <div class="notification-item <?php echo $notification['status'] === 'unread' ? 'unread' : ''; ?>">
                            <div class="notification-message">
                                <i class="<?php echo $notification['status'] === 'unread' ? 'fas fa-circle text-warning me-2' : 'far fa-circle text-muted me-2'; ?>"></i>
                                <?php echo $notification['message']; ?>
                            </div>
                            
                            <?php if ($candidate_name && $job_title): ?>
                                <div class="notification-detail">
                                    <strong><i class="fas fa-user me-2"></i>Candidate:</strong> <?php echo $candidate_name; ?>
                                </div>
                                <div class="notification-detail">
                                    <strong><i class="fas fa-briefcase me-2"></i>Job:</strong> <?php echo $job_title; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="notification-time">
                                <i class="far fa-clock me-1"></i>
                                <?php echo date('M d, Y h:i A', strtotime($notification['created_at'])); ?>
                            </div>

                            <div class="notification-actions">
                                <?php if ($notification['status'] === 'unread'): ?>
                                    <a href="?mark_as_read=<?php echo $notification['notification_id']; ?>" class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-check me-1"></i> Mark as Read
                                    </a>
                                <?php else: ?>
                                    <a href="?mark_as_unread=<?php echo $notification['notification_id']; ?>" class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-undo me-1"></i> Mark as Unread
                                    </a>
                                <?php endif; ?>

                                <?php if ($notification['message'] === "A candidate has been recommended for your job posting. Please review and decide whether to send a job offer." && isset($seeker_id) && isset($job_id)): ?>
                                    <a href="view_candidate_details.php?seeker_id=<?php echo $seeker_id; ?>&job_id=<?php echo $job_id; ?>" class="btn btn-sm btn-primary ms-2">
                                        <i class="fas fa-eye me-1"></i> View Candidate Details
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-bell-slash text-muted mb-3" style="font-size: 3rem;"></i>
                        <h5>No notifications found</h5>
                        <p class="text-muted">You don't have any notifications at the moment.</p>
                    </div>
                <?php endif; ?>
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