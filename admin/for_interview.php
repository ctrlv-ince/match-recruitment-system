<?php
session_start();
include '../db.php';

// Redirect if not logged in as an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch scheduled interviews
$sql = "SELECT interviews.interview_id, interviews.scheduled_date, interviews.status, 
               users.full_name AS candidate_name, job_postings.title AS job_title, employers.company_name 
        FROM interviews 
        JOIN applications ON interviews.application_id = applications.application_id 
        JOIN users ON applications.seeker_id = users.user_id 
        JOIN job_postings ON applications.job_id = job_postings.job_id 
        JOIN employers ON job_postings.employer_id = employers.employer_id 
        ORDER BY interviews.scheduled_date DESC";
$interviews_result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scheduled Interviews</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet">
</head>

<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Main Content -->
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h2>Scheduled Interviews</h2>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Candidate Name</th>
                                <th>Job Title</th>
                                <th>Employer</th>
                                <th>Scheduled Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($interviews_result->num_rows > 0) {
                                while ($row = $interviews_result->fetch_assoc()) {
                                    $status_badge = ($row['status'] === 'pending') ? 'warning' : (($row['status'] === 'completed') ? 'success' : (($row['status'] === 'cancelled') ? 'danger' : 'secondary'));
                                    echo '
                                    <tr>
                                        <td>' . $row['candidate_name'] . '</td>
                                        <td>' . $row['job_title'] . '</td>
                                        <td>' . $row['company_name'] . '</td>
                                        <td>' . $row['scheduled_date'] . '</td>
                                        <td><span class="badge bg-' . $status_badge . '">' . $row['status'] . '</span></td>
                                        <td>
                                            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#updateInterviewModal' . $row['interview_id'] . '">Update Interview</button>
                                        </td>
                                    </tr>';

                                    // Modal for updating the interview
                                    echo '
                                    <div class="modal fade" id="updateInterviewModal' . $row['interview_id'] . '" tabindex="-1" aria-labelledby="updateInterviewModalLabel' . $row['interview_id'] . '" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="updateInterviewModalLabel' . $row['interview_id'] . '">Update Interview</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form action="update_interview.php" method="POST">
                                                        <input type="hidden" name="interview_id" value="' . $row['interview_id'] . '">
                                                        <div class="mb-3">
                                                            <label for="status" class="form-label">Status</label>
                                                            <select class="form-control" id="status" name="status" required>
                                                                <option value="pending" ' . ($row['status'] === 'pending' ? 'selected' : '') . '>Pending</option>
                                                                <option value="completed" ' . ($row['status'] === 'completed' ? 'selected' : '') . '>Completed</option>
                                                                <option value="cancelled" ' . ($row['status'] === 'cancelled' ? 'selected' : '') . '>Cancelled</option>
                                                            </select>
                                                        </div>
                                                        <button type="submit" class="btn btn-primary">Update</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>';
                                }
                            } else {
                                echo '<tr><td colspan="6">No scheduled interviews found.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>