<?php
session_start();
include '../db.php';

// Redirect if not logged in as an employer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'employer') {
    header("Location: login.php");
    exit();
}

// Fetch employer details
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE user_id = $user_id";
$result = $conn->query($sql);
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employer Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Welcome, <?php echo $user['full_name']; ?>!</h2>
        <p>Email: <?php echo $user['email']; ?></p>
        <hr>

        <h3>Your Job Postings</h3>
        <?php
        // Fetch job postings for this employer
        $sql = "SELECT * FROM job_postings WHERE employer_id = $user_id ORDER BY created_at DESC";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo "<div class='accordion' id='jobPostingsAccordion'>";
            while ($row = $result->fetch_assoc()) {
                $job_id = $row['job_id'];
                $status = $row['status'];
                $status_badge = ($status === 'approved') ? 'success' : 
                                (($status === 'pending') ? 'warning' : 
                                (($status === 'rejected') ? 'danger' : 'secondary'));
                echo "
                <div class='accordion-item'>
                    <h2 class='accordion-header' id='heading{$job_id}'>
                        <button class='accordion-button' type='button' data-bs-toggle='collapse' data-bs-target='#collapse{$job_id}' aria-expanded='true' aria-controls='collapse{$job_id}'>
                            {$row['title']} <span class='badge bg-{$status_badge} ms-2'>{$status}</span>
                        </button>
                    </h2>
                    <div id='collapse{$job_id}' class='accordion-collapse collapse' aria-labelledby='heading{$job_id}' data-bs-parent='#jobPostingsAccordion'>
                        <div class='accordion-body'>
                            <p><strong>Description:</strong> {$row['description']}</p>
                            <p><strong>Requirements:</strong> {$row['requirements']}</p>
                            <p><strong>Posted On:</strong> {$row['created_at']}</p>
                            <p><strong>Status:</strong> <span class='badge bg-{$status_badge}'>{$status}</span></p>

                            <h4>Candidates</h4>";
                            // Fetch candidates for this job posting
                            $sql_candidates = "SELECT applications.application_id, applications.seeker_id, users.full_name, users.email 
                                              FROM applications 
                                              JOIN users ON applications.seeker_id = users.user_id 
                                              WHERE applications.job_id = $job_id";
                            $candidates_result = $conn->query($sql_candidates);

                            if ($candidates_result->num_rows > 0) {
                                echo "<table class='table table-bordered'>
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>";
                                while ($candidate = $candidates_result->fetch_assoc()) {
                                    echo "<tr>
                                            <td>{$candidate['full_name']}</td>
                                            <td>{$candidate['email']}</td>
                                            <td>
                                                <a href='view_candidate_details.php?seeker_id={$candidate['seeker_id']}&job_id={$job_id}' class='btn btn-primary btn-sm'>View Details</a>
                                            </td>
                                          </tr>";
                                }
                                echo "</tbody></table>";
                            } else {
                                echo "<p>No candidates have applied for this job yet.</p>";
                            }
                            echo "
                        </div>
                    </div>
                </div>";
            }
            echo "</div>";
        } else {
            echo "<p>You have not posted any jobs yet.</p>";
        }
        ?>

        <?php
        // Fetch the number of unread notifications
        $sql = "SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = $user_id AND status = 'unread'";
        $unread_count = $conn->query($sql)->fetch_assoc()['unread_count'];
        ?>
        <a href="notifications.php" class="btn btn-primary position-relative mt-3">
            Notifications
            <?php if ($unread_count > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    <?php echo $unread_count; ?>
                </span>
            <?php endif; ?>
        </a>
        <hr>
        <a href="post_job.php" class="btn btn-primary">Post a Job</a>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>

    <!-- Bootstrap JS (required for accordion functionality) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>