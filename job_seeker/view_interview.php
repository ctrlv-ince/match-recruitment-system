<?php
session_start();
include '../db.php';

// Redirect if not logged in as a job seeker
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'job_seeker') {
    header("Location: login.php");
    exit();
}

$seeker_id = $_SESSION['user_id'];
$message = '';

// Fetch interview schedules for this job seeker
$sql = "SELECT interviews.interview_id, interviews.scheduled_date, interviews.status, job_postings.title 
        FROM interviews 
        JOIN applications ON interviews.application_id = applications.application_id 
        JOIN job_postings ON applications.job_id = job_postings.job_id 
        WHERE applications.seeker_id = $seeker_id";
$result = $conn->query($sql);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $interview_id = $_POST['interview_id'];
    $action = $_POST['action']; // 'confirm' or 'decline'

    // Update interview status based on action
    $status = ($action === 'confirm') ? 'confirmed' : 'declined';
    $sql = "UPDATE interviews SET status = '$status' WHERE interview_id = $interview_id";
    if ($conn->query($sql) === TRUE) {
        // Add notification for the employer
        $employer_id = $conn->query("SELECT job_postings.employer_id 
                                     FROM interviews 
                                     JOIN applications ON interviews.application_id = applications.application_id 
                                     JOIN job_postings ON applications.job_id = job_postings.job_id 
                                     WHERE interviews.interview_id = $interview_id")->fetch_assoc()['employer_id'];
        $message = "The candidate has $status the interview for job posting: {$row['title']}.";
        $sql = "INSERT INTO notifications (user_id, message) VALUES ($employer_id, '$message')";
        $conn->query($sql);

        $message = "Interview $status successfully!";
    } else {
        $message = "Error updating interview status: " . $conn->error;
    }
}
$sql = "SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = $seeker_id AND status = 'unread'";
$unread_count = $conn->query($sql)->fetch_assoc()['unread_count'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Interviews</title>
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

        .container {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 30px;
        }

        h2 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        h2 i {
            font-size: 1.5rem;
            color: var(--primary-color);
        }

        .alert {
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert i {
            font-size: 1.2rem;
        }

        .alert-info {
            background-color: #e9f5fe;
            border-color: #b3d9ff;
            color: #004085;
        }

        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .card-body {
            padding: 20px;
        }

        .card-title {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 15px;
        }

        .card-text {
            color: var(--text-muted);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-text i {
            font-size: 1.2rem;
            color: var(--primary-color);
        }

        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-success:hover {
            background-color: #218838;
            border-color: #218838;
        }

        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-danger:hover {
            background-color: #c82333;
            border-color: #c82333;
        }

        .btn-secondary {
            background-color: var(--text-muted);
            border-color: var(--text-muted);
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-secondary:hover {
            background-color: #555;
            border-color: #555;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .status-badge.pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-badge.confirmed {
            background-color: #d4edda;
            color: #155724;
        }

        .status-badge.declined {
            background-color: #f8d7da;
            color: #721c24;
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
            <a class="navbar-brand brand-logo" href="dashboard.php">
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
                        <a class="nav-link position-relative" href="notifications.php">
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
    <div class="container mt-5">
        <h2><i class="fas fa-calendar-alt"></i>Your Interview Schedules</h2>
        <?php if ($message): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i><?php echo $message; ?>
            </div>
        <?php endif; ?>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='card mb-3'>";
                echo "<div class='card-body'>";
                echo "<h5 class='card-title'>{$row['title']}</h5>";
                echo "<p class='card-text'><i class='fas fa-calendar-day'></i><strong>Scheduled Date:</strong> {$row['scheduled_date']}</p>";
                echo "<p class='card-text'><i class='fas fa-info-circle'></i><strong>Status:</strong> <span class='status-badge {$row['status']}'>{$row['status']}</span></p>";
                if ($row['status'] === 'pending') {
                    echo "<form action='view_interview.php' method='POST' class='d-inline'>";
                    echo "<input type='hidden' name='interview_id' value='{$row['interview_id']}'>";
                    echo "<button type='submit' name='action' value='confirm' class='btn btn-success me-2'>";
                    echo "<i class='fas fa-check-circle'></i>Confirm";
                    echo "</button>";
                    echo "<button type='submit' name='action' value='decline' class='btn btn-danger'>";
                    echo "<i class='fas fa-times-circle'></i>Decline";
                    echo "</button>";
                    echo "</form>";
                }
                echo "</div>";
                echo "</div>";
            }
        } else {
            echo "<div class='text-center py-5'>";
            echo "<i class='fas fa-calendar-times text-muted' style='font-size: 3rem;'></i>";
            echo "<h5>No Interview Schedules Found</h5>";
            echo "<p class='text-muted'>You have no upcoming interviews.</p>";
            echo "</div>";
        }
        ?>
        <a href="dashboard.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>Back to Dashboard
        </a>
    </div>
</body>
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