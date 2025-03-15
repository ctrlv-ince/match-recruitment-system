<?php
session_start();
include '../db.php';

// Redirect if not logged in as a job seeker
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'job_seeker') {
    header("Location: login.php");
    exit();
}

// Fetch job seeker details
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE user_id = $user_id";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

// Fetch the number of unread notifications
$sql = "SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = $user_id AND status = 'unread'";
$unread_count = $conn->query($sql)->fetch_assoc()['unread_count'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GoSeekr Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0073b1;
            --secondary-color: #f5f5f5;
            --accent-color: #e7f3ff;
            --border-color: #e0e0e0;
            --text-dark: #333;
            --text-muted: #666;
        }

        /* Flexbox layout to push footer to the bottom */
        html,
        body {
            height: 100%;
            margin: 0;
            display: flex;
            flex-direction: column;
        }

        body {
            background-color: var(--secondary-color);
            color: var(--text-dark);
            font-family: 'Segoe UI', Arial, sans-serif;
            flex: 1;
        }

        .navbar {
            background-color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 12px 0;
        }

        .brand-logo {
            font-size: 24px;
            font-weight: bold;
            color: var(--primary-color);
        }

        .nav-notification {
            position: relative;
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 30px auto;
            flex: 1;
            padding-bottom: 60px;
            /* Add padding to prevent footer overlap */
        }

        .profile-card {
            background: white;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .profile-image {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin-right: 20px;
        }

        .profile-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .profile-email {
            color: var(--text-muted);
            margin-bottom: 0;
        }

        .card-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border-color);
        }

        .application-item {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 12px;
            border: 1px solid var(--border-color);
            background-color: white;
            transition: transform 0.2s;
        }

        .application-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .application-title {
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-dark);
        }

        .status-badge {
            font-size: 0.85rem;
            padding: 4px 10px;
            border-radius: 12px;
        }

        .status-applied {
            background-color: #e7f3ff;
            color: #0073b1;
        }

        .status-interview {
            background-color: #fdf6ec;
            color: #e7761e;
        }

        .status-rejected {
            background-color: #ffeaea;
            color: #d32f2f;
        }

        .status-accepted {
            background-color: #ecf7ed;
            color: #2e7d32;
        }

        .action-buttons {
            margin-top: 24px;
        }

        .action-btn {
            padding: 10px 20px;
            border-radius: 24px;
            font-weight: 500;
            margin-right: 12px;
            margin-bottom: 12px;
            display: inline-flex;
            align-items: center;
        }

        .action-btn i {
            margin-right: 8px;
        }

        .primary-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
        }

        .primary-btn:hover {
            background-color: #005b8e;
            color: white;
        }

        .outline-btn {
            background-color: white;
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
        }

        .outline-btn:hover {
            background-color: var(--accent-color);
            color: var(--primary-color);
        }

        .danger-btn {
            background-color: white;
            color: #d32f2f;
            border: 1px solid #d32f2f;
        }

        .danger-btn:hover {
            background-color: #ffebee;
            color: #d32f2f;
        }

        .no-applications {
            padding: 40px;
            text-align: center;
            color: var(--text-muted);
            border: 1px dashed var(--border-color);
            border-radius: 8px;
            margin: 20px 0;
        }

        @media (max-width: 768px) {
            .profile-header {
                flex-direction: column;
                text-align: center;
            }

            .profile-image {
                margin-right: 0;
                margin-bottom: 16px;
            }
        }

        /* Footer styling */
        footer {
            background-color: #343a40;
            color: white;
            padding: 20px 0;
            text-align: center;
            margin-top: auto;
            /* Push footer to the bottom */
            width: 100%;
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

    <!-- Main Dashboard Content -->
    <div class="container dashboard-container">
        <!-- Profile Section -->
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-image">
                    <?php echo substr($user['full_name'], 0, 1); ?>
                </div>
                <div>
                    <h2 class="profile-name"><?php echo $user['full_name']; ?></h2>
                    <p class="profile-email"><i class="fas fa-envelope"></i> <?php echo $user['email']; ?></p>
                </div>
            </div>

            <div class="action-buttons">
                <a href="search_jobs.php" class="btn action-btn primary-btn">
                    <i class="fas fa-search"></i> Find Jobs
                </a>
                <a href="update_profile.php" class="btn action-btn outline-btn">
                    <i class="fas fa-user-edit"></i> Update Profile
                </a>
                <a href="notifications.php" class="btn action-btn outline-btn">
                    <i class="fas fa-bell"></i> Notifications
                    <?php if ($unread_count > 0): ?>
                        <span class="badge bg-danger ms-1"><?php echo $unread_count; ?></span>
                    <?php endif; ?>
                </a>
                <a href="view_interview.php" class="btn action-btn outline-btn">
                    <i class="fas fa-calendar-check"></i> Interviews
                </a>
                <a href="view_applications.php" class="btn action-btn outline-btn">
                    <i class="fas fa-file-alt"></i></i> Applications
                </a>
            </div>
        </div>

        <!-- Applications Section -->
        <div class="profile-card">
            <h3 class="card-title"><i class="fas fa-file-alt"></i> Your Job Applications</h3>

            <?php
            // Fetch applications for this job seeker
            $sql = "SELECT applications.*, job_postings.title 
                    FROM applications 
                    JOIN job_postings ON applications.job_id = job_postings.job_id 
                    WHERE applications.seeker_id = $user_id";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                echo "<ul class='list-unstyled'>";
                while ($row = $result->fetch_assoc()) {
                    $statusClass = "";
                    switch (strtolower($row['status'])) {
                        case 'applied':
                            $statusClass = "status-applied";
                            break;
                        case 'interview':
                            $statusClass = "status-interview";
                            break;
                        case 'rejected':
                            $statusClass = "status-rejected";
                            break;
                        case 'accepted':
                            $statusClass = "status-accepted";
                            break;
                        default:
                            $statusClass = "status-applied";
                    }
                    echo "<li class='application-item'>
                            <h5 class='application-title'>{$row['title']}</h5>
                            <span class='status-badge $statusClass'>
                                {$row['status']}
                            </span>
                            <div class='application-date mt-2 text-muted'>
                                <small><i class='fas fa-clock'></i> Applied on: " . date("M d, Y", strtotime($row['applied_at'])) . "</small>
                            </div>
                          </li>";
                }
                echo "</ul>";
            } else {
                echo '<div class="no-applications">
                        <i class="fas fa-file-alt fa-3x mb-3"></i>
                        <h4>No Applications Yet</h4>
                        <p>Start your career journey by applying to jobs that match your skills and interests.</p>
                        <a href="search_jobs.php" class="btn primary-btn">Find Jobs Now</a>
                      </div>';
            }
            ?>
        </div>
    </div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
<!-- Footer -->
<footer>
    <div class="container text-center">
        <p>&copy; 2025 GoSeekr. All Rights Reserved.</p>
    </div>
</footer>

</html>