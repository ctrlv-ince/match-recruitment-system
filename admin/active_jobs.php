<?php
session_start();
include '../db.php';

// Redirect if not logged in as an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch all job postings
$sql = "SELECT job_postings.*, users.full_name 
        FROM job_postings 
        JOIN users ON job_postings.employer_id = users.user_id 
        ORDER BY job_postings.created_at DESC";
$job_postings_result = $conn->query($sql);

// Fetch all users
$sql = "SELECT * FROM users ORDER BY created_at DESC";
$users_result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Active Job Listings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet">
</head>

<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <!-- Include sidebar -->
            <?php include 'includes/sidebar.php'; ?>
            
            <!-- Main content area -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h2>Active Job Listings</h2>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Employer</th>
                                <th>Quota</th>
                                <th>Candidates</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Fetch active job postings
                            $sql = "SELECT job_postings.*, users.full_name 
                                    FROM job_postings 
                                    JOIN users ON job_postings.employer_id = users.user_id 
                                    WHERE job_postings.status = 'approved' 
                                    ORDER BY job_postings.created_at DESC";
                            $active_jobs_result = $conn->query($sql);

                            if ($active_jobs_result->num_rows > 0) {
                                while ($row = $active_jobs_result->fetch_assoc()) {
                                    $job_id = $row['job_id'];
                                    echo "<tr>";
                                    echo "<td>{$row['title']}</td>";
                                    echo "<td>{$row['full_name']}</td>";
                                    echo "<td>{$row['quota']}</td>";

                                    // Fetch candidates for this job
                                    $sql_candidates = "SELECT applications.*, users.full_name 
                                                      FROM applications 
                                                      JOIN users ON applications.seeker_id = users.user_id 
                                                      WHERE applications.job_id = $job_id";
                                    $candidates_result = $conn->query($sql_candidates);

                                    echo "<td>";
                                    if ($candidates_result->num_rows > 0) {
                                        echo "<ul class='list-unstyled'>";
                                        while ($candidate = $candidates_result->fetch_assoc()) {
                                            $application_id = $candidate['application_id'];
                                            echo "<li class='mb-2'>{$candidate['full_name']} - Application Status: " . ($candidate['status'] ?? 'pending') . "</li>";

                                            // Only show "Shortlist" and "Reject" buttons for candidates with status 'applied'
                                            if ($candidate['status'] === 'applied') {
                                                // Hide buttons if the hiring process is completed
                                                if ($candidate['employer_decision'] === 'approved' || $candidate['employer_decision'] === 'rejected') {
                                                    echo "<li><p class='text-muted'>Hiring process completed.</p></li>";
                                                } else {
                                                    echo "<li class='mb-2'>
                                                        <form action='shortlist_candidate.php' method='POST' style='display:inline;'>
                                                            <input type='hidden' name='application_id' value='{$application_id}'>
                                                            <button type='submit' class='btn btn-success btn-sm'>Shortlist</button>
                                                        </form>
                                                        <form action='reject_candidate.php' method='POST' style='display:inline;'>
                                                            <input type='hidden' name='application_id' value='{$application_id}'>
                                                            <button type='submit' class='btn btn-danger btn-sm'>Reject</button>
                                                        </form>
                                                    </li>";
                                                }
                                            }
                                        }
                                        echo "</ul>";
                                    } else {
                                        echo "No candidates yet.";
                                    }
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5'>No active job listings found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>