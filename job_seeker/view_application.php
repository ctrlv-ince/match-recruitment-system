<?php
session_start();
include '../db.php';

// Redirect if not logged in as a job seeker
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'job_seeker') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: view_applications.php");
    exit();
}

$application_id = $_GET['id'];
$seeker_id = $_SESSION['user_id'];

// Fetch application details
$sql = "SELECT applications.*, job_postings.title 
        FROM applications 
        JOIN job_postings ON applications.job_id = job_postings.job_id 
        WHERE applications.application_id = $application_id AND applications.seeker_id = $seeker_id";
$application_result = $conn->query($sql);
$application = $application_result->fetch_assoc();

if (!$application) {
    die("Invalid application ID.");
}

// Fetch documents for the application
$sql = "SELECT * FROM application_documents WHERE application_id = $application_id";
$documents_result = $conn->query($sql);
$sql = "SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = $seeker_id AND status = 'unread'";
$unread_count = $conn->query($sql)->fetch_assoc()['unread_count'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Details</title>
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

        h3 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        h3 i {
            font-size: 1.2rem;
            color: var(--primary-color);
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

        .table {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .table thead {
            background-color: var(--primary-color);
            color: white;
        }

        .table th,
        .table td {
            padding: 12px;
            text-align: left;
        }

        .table tbody tr:hover {
            background-color: rgba(0, 115, 177, 0.05);
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

        .status-badge.accepted {
            background-color: #d4edda;
            color: #155724;
        }

        .status-badge.rejected {
            background-color: #f8d7da;
            color: #721c24;
        }

        .document-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }

        .document-link:hover {
            text-decoration: underline;
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
        <h2><i class="fas fa-file-alt"></i><?php echo $application['title']; ?></h2>
        <p class="card-text">
            <i class="fas fa-calendar-check"></i>
            <strong>Applied At:</strong> <?php echo $application['applied_at']; ?>
        </p>
        <p class="card-text">
            <i class="fas fa-info-circle"></i>
            <strong>Status:</strong>
            <span class="status-badge <?php echo $application['status']; ?>">
                <?php echo ucfirst($application['status']); ?>
            </span>
        </p>

        <h3><i class="fas fa-file-upload"></i>Uploaded Documents</h3>
        <?php if ($documents_result->num_rows > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Document Type</th>
                        <th>File</th>
                        <th>Uploaded At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($document = $documents_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo ucfirst($document['document_type']); ?></td>
                            <td>
                                <a href="<?php echo $document['document_path']; ?>" class="document-link" target="_blank">
                                    <i class="fas fa-file-pdf"></i>View Document
                                </a>
                            </td>
                            <td><?php echo $document['uploaded_at']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="text-center py-4">
                <i class="fas fa-file-alt text-muted" style="font-size: 3rem;"></i>
                <h5>No Documents Uploaded</h5>
                <p class="text-muted">No documents were uploaded for this application.</p>
            </div>
        <?php endif; ?>

        <a href="view_applications.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>Back to Applications
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