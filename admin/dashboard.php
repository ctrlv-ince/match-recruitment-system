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
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h2>Admin Dashboard</h2>

        <!-- Active Job Listings Section -->
<h3>Active Job Listings</h3>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Title</th>
            <th>Employer</th>
            <th>Quota</th>
            <th>Candidates</th>
            <th>Actions</th>
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
                    echo "<ul>";
                    while ($candidate = $candidates_result->fetch_assoc()) {
                        $application_id = $candidate['application_id'];
                        echo "<li>{$candidate['full_name']} - Application Status: " . ($candidate['status'] ?? 'pending') . "</li>";

                        // Only show "Shortlist" and "Reject" buttons for candidates with status 'applied'
                        if ($candidate['status'] === 'applied') {
                            // Hide buttons if the hiring process is completed
                            if ($candidate['employer_decision'] === 'approved' || $candidate['employer_decision'] === 'rejected') {
                                echo "<li><p class='text-muted'>Hiring process completed.</p></li>";
                            } else {
                                echo "<li>
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

                
            }
        } else {
            echo "<tr><td colspan='5'>No active job listings found.</td></tr>";
        }
        ?>
    </tbody>
</table>



        <!-- Shortlisted Candidates Section -->
<h3>Shortlisted Candidates</h3>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Candidate Name</th>
            <th>Job Title</th>
            <th>Employer</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // Fetch shortlisted candidates
        $sql = "SELECT applications.application_id, users.full_name AS candidate_name, job_postings.title AS job_title, employers.company_name 
                FROM applications 
                JOIN users ON applications.seeker_id = users.user_id 
                JOIN job_postings ON applications.job_id = job_postings.job_id 
                JOIN employers ON job_postings.employer_id = employers.employer_id 
                WHERE applications.status = 'shortlisted' 
                ORDER BY applications.applied_at DESC";
        $shortlisted_result = $conn->query($sql);

        if ($shortlisted_result->num_rows > 0) {
            while ($row = $shortlisted_result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>{$row['candidate_name']}</td>";
                echo "<td>{$row['job_title']}</td>";
                echo "<td>{$row['company_name']}</td>";
                echo "<td>
                        <button type='button' class='btn btn-primary btn-sm' data-bs-toggle='modal' data-bs-target='#scheduleInterviewModal{$row['application_id']}'>Schedule Interview</button>
                      </td>";
                echo "</tr>";

                // Modal for scheduling an interview
                echo "
                <div class='modal fade' id='scheduleInterviewModal{$row['application_id']}' tabindex='-1' aria-labelledby='scheduleInterviewModalLabel{$row['application_id']}' aria-hidden='true'>
                    <div class='modal-dialog'>
                        <div class='modal-content'>
                            <div class='modal-header'>
                                <h5 class='modal-title' id='scheduleInterviewModalLabel{$row['application_id']}'>Schedule Interview</h5>
                                <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                            </div>
                            <div class='modal-body'>
                                <form action='schedule_interview.php' method='POST'>
                                    <input type='hidden' name='application_id' value='{$row['application_id']}'>
                                    <div class='mb-3'>
                                        <label for='scheduled_date' class='form-label'>Interview Date & Time</label>
                                        <input type='datetime-local' class='form-control' id='scheduled_date' name='scheduled_date' required>
                                    </div>
                                    <button type='submit' class='btn btn-primary'>Schedule</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>";
            }
        } else {
            echo "<tr><td colspan='4'>No shortlisted candidates found.</td></tr>";
        }
        ?>
    </tbody>
</table>

        <!-- Job Postings Section -->
        <h3>Pending Job Postings</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Employer</th>
                    <th>Description</th>
                    <th>Requirements</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch all pending job postings
                $sql = "SELECT job_postings.*, users.full_name 
                FROM job_postings 
                JOIN users ON job_postings.employer_id = users.user_id 
                WHERE job_postings.status = 'pending' 
                ORDER BY job_postings.created_at DESC";
                $job_postings_result = $conn->query($sql);

                if ($job_postings_result->num_rows > 0) {
                    while ($row = $job_postings_result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>{$row['title']}</td>";
                        echo "<td>{$row['full_name']}</td>";
                        echo "<td>{$row['description']}</td>";
                        echo "<td>{$row['requirements']}</td>";
                        echo "<td>{$row['status']}</td>";
                        echo "<td>
                        <a href='approve_job.php?id={$row['job_id']}' class='btn btn-success btn-sm'>Approve</a>
                        <a href='reject_job.php?id={$row['job_id']}' class='btn btn-danger btn-sm'>Reject</a>
                      </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No pending job postings found.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <!-- Rejected Job Postings Section -->
        <h3>Rejected Job Postings</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Employer</th>
                    <th>Description</th>
                    <th>Requirements</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch all rejected job postings
                $sql = "SELECT job_postings.*, users.full_name 
                FROM job_postings 
                JOIN users ON job_postings.employer_id = users.user_id 
                WHERE job_postings.status = 'rejected' 
                ORDER BY job_postings.created_at DESC";
                $rejected_job_postings_result = $conn->query($sql);

                if ($rejected_job_postings_result->num_rows > 0) {
                    while ($row = $rejected_job_postings_result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>{$row['title']}</td>";
                        echo "<td>{$row['full_name']}</td>";
                        echo "<td>{$row['description']}</td>";
                        echo "<td>{$row['requirements']}</td>";
                        echo "<td>{$row['status']}</td>";
                        echo "<td>
                        <a href='delete_job.php?id={$row['job_id']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this job posting?\")'>Delete</a>
                      </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No rejected job postings found.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <!-- Users Section -->
        <h3>Users</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>User Type</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch all users
                $sql = "SELECT * FROM users ORDER BY created_at DESC";
                $users_result = $conn->query($sql);

                if ($users_result->num_rows > 0) {
                    while ($row = $users_result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>{$row['full_name']}</td>";
                        echo "<td>{$row['email']}</td>";
                        echo "<td>{$row['user_type']}</td>";
                        echo "<td>{$row['status']}</td>";
                        echo "<td>";
                        if ($row['status'] === 'rejected') {
                            echo "<a href='delete_user.php?id={$row['user_id']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this user?\")'>Delete</a>";
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No users found.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <!-- Job Seeker Verifications Section -->
        <h3>Job Seeker Verifications</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Documents</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch pending job seekers
                $sql = "SELECT users.user_id, users.full_name, users.email, users.status 
                FROM users 
                WHERE users.user_type = 'job_seeker' AND users.status = 'pending'";
                $job_seekers_result = $conn->query($sql);

                if ($job_seekers_result->num_rows > 0) {
                    while ($row = $job_seekers_result->fetch_assoc()) {
                        $user_id = $row['user_id'];

                        // Fetch documents for the job seeker
                        $sql_docs = "SELECT * FROM job_seeker_documents WHERE seeker_id = $user_id";
                        $docs_result = $conn->query($sql_docs);

                        echo "<tr>";
                        echo "<td>{$row['full_name']}</td>";
                        echo "<td>{$row['email']}</td>";
                        echo "<td>{$row['status']}</td>";

                        // Display documents
                        echo "<td>";
                        if ($docs_result->num_rows > 0) {
                            while ($doc = $docs_result->fetch_assoc()) {
                                $doc_path = $doc['document_path'];
                                $doc_type = $doc['document_type'];
                                echo "<p><strong>$doc_type:</strong> <a href='$doc_path' target='_blank'>View Document</a></p>";
                            }
                        } else {
                            echo "No documents uploaded.";
                        }
                        echo "</td>";

                        // Actions (Verify/Reject)
                        echo "<td>
                        <a href='verify_user.php?id={$row['user_id']}&action=verify' class='btn btn-success btn-sm'>Verify</a>
                        <a href='verify_user.php?id={$row['user_id']}&action=reject' class='btn btn-danger btn-sm'>Reject</a>
                      </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No pending job seeker verifications.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <!-- Employer Verifications Section -->
        <h3>Employer Verifications</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Company</th>
                    <th>Documents</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch pending employers and their documents
                $sql = "SELECT users.user_id, users.full_name, employers.company_name, 
                       employer_documents.document_type, employer_documents.document_path
                FROM users
                JOIN employers ON users.user_id = employers.employer_id
                LEFT JOIN employer_documents ON employers.employer_id = employer_documents.employer_id
                WHERE users.user_type = 'employer' AND users.status = 'pending'";
                $employers_result = $conn->query($sql);

                if ($employers_result->num_rows > 0) {
                    $current_employer = null;
                    while ($row = $employers_result->fetch_assoc()) {
                        if ($current_employer !== $row['user_id']) {
                            if ($current_employer !== null) {
                                echo "</ul></td>";
                                echo "<td>
                                <a href='verify_user.php?id={$current_employer}&action=verify' class='btn btn-success btn-sm'>Verify</a>
                                <a href='verify_user.php?id={$current_employer}&action=reject' class='btn btn-danger btn-sm'>Reject</a>
                              </td>";
                                echo "</tr>";
                            }
                            echo "<tr>";
                            echo "<td>{$row['full_name']}</td>";
                            echo "<td>{$row['company_name']}</td>";
                            echo "<td><ul>";
                            $current_employer = $row['user_id'];
                        }
                        echo "<li>{$row['document_type']}: <a href='../employer/{$row['document_path']}' target='_blank'>View Document</a></li>";
                    }
                    if ($current_employer !== null) {
                        echo "</ul></td>";
                        echo "<td>
                        <a href='verify_user.php?id={$current_employer}&action=verify' class='btn btn-success btn-sm'>Verify</a>
                        <a href='verify_user.php?id={$current_employer}&action=reject' class='btn btn-danger btn-sm'>Reject</a>
                      </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No pending employer verifications.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <!-- Feedback Section -->
        <h3>Feedback</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Rating</th>
                    <th>Comments</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch all feedback
                $sql = "SELECT feedback.*, users.full_name 
                FROM feedback 
                JOIN users ON feedback.user_id = users.user_id 
                ORDER BY feedback.created_at DESC";
                $feedback_result = $conn->query($sql);

                if ($feedback_result->num_rows > 0) {
                    while ($row = $feedback_result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>{$row['full_name']}</td>";
                        echo "<td>{$row['rating']}/5</td>";
                        echo "<td>{$row['comments']}</td>";
                        echo "<td>{$row['created_at']}</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No feedback found.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <!-- Reports Section -->
        <!-- <h3>Reports</h3>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>User</th>
            <th>Issue Type</th>
            <th>Description</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody> -->
        <?php
        // Fetch all reports
        // $sql = "SELECT reports.*, users.full_name 
        //         FROM reports 
        //         JOIN users ON reports.user_id = users.user_id 
        //         ORDER BY reports.created_at DESC";
        // $reports_result = $conn->query($sql);

        // if ($reports_result->num_rows > 0) {
        //     while ($row = $reports_result->fetch_assoc()) {
        //         echo "<tr>";
        //         echo "<td>{$row['full_name']}</td>";
        //         echo "<td>{$row['issue_type']}</td>";
        //         echo "<td>{$row['description']}</td>";
        //         echo "<td>{$row['created_at']}</td>";
        //         echo "</tr>";
        //     }
        // } else {
        //     echo "<tr><td colspan='4'>No reports found.</td></tr>";
        // }
        ?>
        <!-- </tbody>
</table> -->

        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>

            <!-- Bootstrap JS (required for modal functionality) -->
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>