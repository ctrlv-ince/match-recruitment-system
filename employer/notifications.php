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

// Count unread notifications
$unread_sql = "SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = ? AND is_read = 0";
$unread_stmt = $conn->prepare($unread_sql);
$unread_stmt->bind_param("i", $employer_id);
$unread_stmt->execute();
$unread_result = $unread_stmt->get_result();
$unread_count = $unread_result->fetch_assoc()['unread_count'];

// Mark all as read
if (isset($_POST['mark_all_read'])) {
    $update_sql = "UPDATE notifications SET is_read = 1 WHERE user_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $employer_id);
    $update_stmt->execute();
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
        
        .notification-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .notification-header {
            background-color: var(--primary-color);
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .notification-item {
            border-left: 3px solid transparent;
            transition: all 0.2s ease;
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .notification-item:hover {
            background-color: var(--accent-color);
        }
        
        .notification-item.unread {
            border-left: 3px solid var(--secondary-color);
            background-color: rgba(10, 102, 194, 0.05);
        }
        
        .notification-item .time {
            font-size: 0.8rem;
            color: var(--text-muted);
        }
        
        .notification-item .title {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .notification-item .message {
            color: #333;
            margin-bottom: 10px;
        }
        
        .action-btn {
            background-color: var(--secondary-color);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }
        
        .action-btn:hover {
            background-color: #084b8a;
            color: white;
        }
        
        .sidebar {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 20px;
            position: sticky;
            top: 20px;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-menu li {
            margin-bottom: 10px;
        }
        
        .sidebar-menu a {
            color: #333;
            text-decoration: none;
            display: block;
            padding: 10px;
            border-radius: 4px;
            transition: all 0.2s ease;
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background-color: var(--accent-color);
            color: var(--primary-color);
        }
        
        .sidebar-menu a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .badge-notification {
            background-color: #ff4d4f;
            color: white;
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 10px;
        }
        
        .mark-all-read {
            font-size: 0.9rem;
            color: white;
            text-decoration: none;
            cursor: pointer;
        }
        
        .mark-all-read:hover {
            text-decoration: underline;
            color: white;
        }
        
        .no-notifications {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-muted);
        }
        
        .no-notifications i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #ccc;
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
                        <a class="nav-link" href="dashboard.php"><i class="fas fa-home"></i>Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="post_job.php"><i class="fas fa-briefcase"></i>Post a Job</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="view_shortlisted_candidate.php"><i class="fas fa-users"></i>View Shortlisted Candidates</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="notifications.php">
                            <i class="fas fa-bell"></i> Notifications
                            <?php if ($unread_count > 0): ?>
                                <span class="badge-notification"><?php echo $unread_count; ?></span>
                            <?php endif; ?>
                        </a>
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
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 mb-4">
                <div class="sidebar">
                    <h5 class="mb-3">Employer Menu</h5>
                    <ul class="sidebar-menu">
                        <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Home</a></li>
                        <li><a href="post_job.php"><i class="fas fa-plus-circle"></i> Post a Job</a></li>
                        <li><a href="view_shortlisted_candidate.php"><i class="fas fa-users"></i>View Shortlisted Candidates</a></li>
                        <li><a class="active" href="notifications.php">
                            <i class="fas fa-bell"></i> Notifications
                            <?php if ($unread_count > 0): ?>
                                <span class="badge-notification"><?php echo $unread_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </ul>
                </div>
            </div>
            
            <!-- Notifications Content -->
            <div class="col-md-9">
                <div class="notification-card">
                    <div class="notification-header">
                        <h4 class="m-0">Notifications</h4>
                        <?php if ($result->num_rows > 0): ?>
                            <form method="post" class="d-inline">
                                <button type="submit" name="mark_all_read" class="mark-all-read bg-transparent border-0">
                                    <i class="fas fa-check-double"></i> Mark all as read
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($notification = $result->fetch_assoc()): ?>
                            <div class="notification-item <?php echo $notification['is_read'] ? '' : 'unread'; ?>">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="title">
                                        <?php 
                                            if (strpos($notification['message'], 'candidate') !== false) {
                                                echo '<i class="fas fa-user-tie text-primary me-2"></i>';
                                            } elseif (strpos($notification['message'], 'application') !== false) {
                                                echo '<i class="fas fa-file-alt text-success me-2"></i>';
                                            } elseif (strpos($notification['message'], 'interview') !== false) {
                                                echo '<i class="fas fa-calendar-check text-warning me-2"></i>';
                                            } else {
                                                echo '<i class="fas fa-info-circle text-info me-2"></i>';
                                            }
                                            
                                            // Get the first sentence as the title
                                            $title = strtok($notification['message'], '.');
                                            echo $title;
                                        ?>
                                    </div>
                                    <span class="time">
                                        <?php 
                                            $date = new DateTime($notification['created_at']);
                                            echo $date->format('M d, Y - h:i A'); 
                                        ?>
                                    </span>
                                </div>
                                <div class="message">
                                    <?php 
                                        // Show the rest of the message without the first sentence
                                        $message = substr($notification['message'], strlen($title) + 1);
                                        echo trim($message);
                                    ?>
                                </div>
                                
                                <?php if (strpos($notification['message'], 'candidate has been recommended') !== false): ?>
                                    <div class="d-flex gap-2">
                                        <a href="view_candidate_details.php?notification_id=<?php echo $notification['notification_id']; ?>" class="action-btn">
                                            <i class="fas fa-user"></i> View Candidate
                                        </a>
                                        <a href="send_job_offer.php?notification_id=<?php echo $notification['notification_id']; ?>" class="action-btn">
                                            <i class="fas fa-paper-plane"></i> Send Offer
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="no-notifications">
                            <i class="fas fa-bell-slash"></i>
                            <h5>No notifications yet</h5>
                            <p>When you receive notifications, they will appear here.</p>
                        </div>
                    <?php endif; ?>
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

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mark notification as read when clicked
        document.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', function() {
                const notificationId = this.dataset.id;
                if (notificationId) {
                    fetch('mark_notification_read.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'notification_id=' + notificationId
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.classList.remove('unread');
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>