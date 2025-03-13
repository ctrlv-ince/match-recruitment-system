<?php
session_start();
include '../db.php';

// Redirect if not logged in as an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch key metrics
$total_jobs = $conn->query("SELECT COUNT(*) as total FROM job_postings")->fetch_assoc()['total'];
$active_candidates = $conn->query("SELECT COUNT(*) as total FROM applications WHERE status = 'applied'")->fetch_assoc()['total'];
$pending_approvals = $conn->query("SELECT COUNT(*) as total FROM job_postings WHERE status = 'pending'")->fetch_assoc()['total'];

// Fetch recent activity
$recent_activity = $conn->query("SELECT job_postings.title, users.full_name, job_postings.created_at 
                                 FROM job_postings 
                                 JOIN users ON job_postings.employer_id = users.user_id 
                                 ORDER BY job_postings.created_at DESC 
                                 LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet">
</head>

<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <style>
        /* Dashboard styles */
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f3f6f8;
        }

        .navbar {
            padding: 10px 0;
            background-color: #ffffff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .btn-primary {
            background-color: #0a66c2;
            border: none;
        }

        .btn-primary:hover {
            background-color: #004182;
        }

        .display-4 {
            font-size: 2.5rem;
            font-weight: bold;
        }
    </style>

    <!-- Main Content -->
    <main class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-12">
                <h2>Dashboard Overview</h2>
                <div class="row">
                    <!-- Key Metrics Cards -->
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">Total Jobs</h5>
                                <p class="card-text display-4"><?php echo $total_jobs; ?></p>
                                <a href="active_jobs.php" class="btn btn-primary">View Jobs</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">Active Candidates</h5>
                                <p class="card-text display-4"><?php echo $active_candidates; ?></p>
                                <a href="shortlisted.php" class="btn btn-primary">View Candidates</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">Pending Approvals</h5>
                                <p class="card-text display-4"><?php echo $pending_approvals; ?></p>
                                <a href="pending_jobs.php" class="btn btn-primary">Review</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <h3 class="mt-4">Quick Links</h3>
                <div class="row">
                    <div class="col-md-3 mb-4">
                        <a href="active_jobs.php" class="card h-100 text-decoration-none">
                            <div class="card-body">
                                <h5 class="card-title">Jobs</h5>
                                <p class="card-text">Manage active job postings.</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3 mb-4">
                        <a href="shortlisted.php" class="card h-100 text-decoration-none">
                            <div class="card-body">
                                <h5 class="card-title">Candidates</h5>
                                <p class="card-text">View shortlisted candidates.</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3 mb-4">
                        <a href="for_interview.php" class="card h-100 text-decoration-none">
                            <div class="card-body">
                                <h5 class="card-title">Interviews</h5>
                                <p class="card-text">Manage scheduled interviews.</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3 mb-4">
                        <a href="users.php" class="card h-100 text-decoration-none">
                            <div class="card-body">
                                <h5 class="card-title">Users</h5>
                                <p class="card-text">Manage user accounts.</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3 mb-4">
                        <a href="job_seeker_verifications.php" class="card h-100 text-decoration-none">
                            <div class="card-body">
                                <h5 class="card-title">Job Seeker Verifications</h5>
                                <p class="card-text">Manage Job Seeker Verifications.</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3 mb-4">
                        <a href="employer_verifications.php" class="card h-100 text-decoration-none">
                            <div class="card-body">
                                <h5 class="card-title">Employer Verifications</h5>
                                <p class="card-text">Manage Employer Verifications.</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3 mb-4">
                        <a href="feedbacks.php" class="card h-100 text-decoration-none">
                            <div class="card-body">
                                <h5 class="card-title">Feedbacks</h5>
                                <p class="card-text">Manage feedbacks from users.</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3 mb-4">
                        <a href="pending_jobs.php" class="card h-100 text-decoration-none">
                            <div class="card-body">
                                <h5 class="card-title">Pending Jobs</h5>
                                <p class="card-text">Manage Pending Jobs.</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3 mb-4">
                        <a href="rejected_jobs.php" class="card h-100 text-decoration-none">
                            <div class="card-body">
                                <h5 class="card-title">Rejected Jobs</h5>
                                <p class="card-text">Manage Rejected Jobs.</p>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Recent Activity -->
                <h3 class="mt-4">Recent Activity</h3>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Job Title</th>
                                <th>Employer</th>
                                <th>Date Posted</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($recent_activity->num_rows > 0) {
                                while ($row = $recent_activity->fetch_assoc()) {
                                    echo '
                                    <tr>
                                        <td>' . $row['title'] . '</td>
                                        <td>' . $row['full_name'] . '</td>
                                        <td>' . $row['created_at'] . '</td>
                                    </tr>';
                                }
                            } else {
                                echo '<tr><td colspan="3">No recent activity found.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>