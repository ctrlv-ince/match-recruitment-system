<?php
session_start();
include '../db.php';

// Redirect if not logged in as a job seeker
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'job_seeker') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

// Fetch current profile data
$sql = "SELECT * FROM job_seekers WHERE seeker_id = $user_id";
$result = $conn->query($sql);
$profile = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $skills = $_POST['skills'];
    $location = $_POST['location']; // Added location field

    // Update skills and location in the job_seekers table
    $sql = "UPDATE job_seekers SET skills = '$skills', location = '$location' WHERE seeker_id = $user_id";
    if ($conn->query($sql) === TRUE) {
        // Handle resume upload
        if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
            $file_name = $_FILES['resume']['name'];
            $file_tmp = $_FILES['resume']['tmp_name'];
            $upload_dir = "uploads/job_seekers/$user_id/resume/";

            // Create directory if it doesn't exist
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_path = $upload_dir . basename($file_name);

            // Move uploaded file
            if (move_uploaded_file($file_tmp, $file_path)) {
                // Update resume in job_seeker_documents table
                $sql = "UPDATE job_seeker_documents SET document_path = '$file_path' WHERE seeker_id = $user_id AND document_type = 'resume'";
                $conn->query($sql);
            }
        }

        $message = "Profile updated successfully!";
    } else {
        $message = "Error: " . $sql . "<br>" . $conn->error;
    }
}
$sql = "SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = $user_id AND status = 'unread'";
$unread_count = $conn->query($sql)->fetch_assoc()['unread_count'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile</title>
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

        .form-label {
            font-weight: 600;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-label i {
            font-size: 1.2rem;
            color: var(--primary-color);
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid var(--border-color);
            padding: 10px;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(0, 115, 177, 0.2);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-primary:hover {
            background-color: #005d91;
            border-color: #005d91;
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

        .text-muted {
            color: var(--text-muted);
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
        <h2><i class="fas fa-user-edit"></i>Update Your Profile</h2>
        <?php if ($message): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i><?php echo $message; ?>
            </div>
        <?php endif; ?>
        <form action="update_profile.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="skills" class="form-label">
                    <i class="fas fa-tools"></i>Your Skills
                </label>
                <input type="text" class="form-control" id="skills" name="skills" value="<?php echo $profile['skills']; ?>" required>
                <small class="form-text text-muted">Separate skills with commas (e.g., PHP, MySQL, JavaScript).</small>
            </div>
            <div class="mb-3">
                <label for="location" class="form-label">
                    <i class="fas fa-map-marker-alt"></i>Location
                </label>
                <input type="text" class="form-control" id="location" name="location" value="<?php echo $profile['location']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="resume" class="form-label">
                    <i class="fas fa-file-upload"></i>Update Resume/CV
                </label>
                <input type="file" class="form-control" id="resume" name="resume">
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>Update Profile
            </button>
            <a href="dashboard.php" class="btn btn-secondary">
                <i class="fas fa-times"></i>Cancel
            </a>
        </form>
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