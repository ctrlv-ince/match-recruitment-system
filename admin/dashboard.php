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

        <!-- Job Postings Section -->
        <h3>Job Postings</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Employer</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($job_postings_result->num_rows > 0) {
                    while ($row = $job_postings_result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>{$row['title']}</td>";
                        echo "<td>{$row['full_name']}</td>";
                        echo "<td>{$row['status']}</td>";
                        echo "<td>
                                <a href='approve_job.php?id={$row['job_id']}' class='btn btn-success btn-sm'>Approve</a>
                                <a href='reject_job.php?id={$row['job_id']}' class='btn btn-danger btn-sm'>Reject</a>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No job postings found.</td></tr>";
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
                </tr>
            </thead>
            <tbody>
                <?php
                if ($users_result->num_rows > 0) {
                    while ($row = $users_result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>{$row['full_name']}</td>";
                        echo "<td>{$row['email']}</td>";
                        echo "<td>{$row['user_type']}</td>";
                        echo "<td>{$row['status']}</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No users found.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <!-- Pending Employer Verifications Section -->
<h3>Pending Employer Verifications</h3>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Name</th>
            <th>Company</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // Fetch employers with pending document verification
        $sql = "SELECT employers.*, users.full_name 
                FROM employers 
                JOIN users ON employers.employer_id = users.user_id 
                WHERE employers.verification_status = 'pending'";
        $employers_result = $conn->query($sql);

        if ($employers_result->num_rows > 0) {
            while ($row = $employers_result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>{$row['full_name']}</td>";
                echo "<td>{$row['company_name']}</td>";
                echo "<td>
                        <a href='verify_employer.php?id={$row['employer_id']}&action=verify' class='btn btn-success btn-sm'>Verify</a>
                        <a href='verify_employer.php?id={$row['employer_id']}&action=reject' class='btn btn-danger btn-sm'>Reject</a>
                      </td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='3'>No pending employer verifications.</td></tr>";
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
                echo "<tr>";
                echo "<td>{$row['full_name']}</td>";
                echo "<td>{$row['email']}</td>";
                echo "<td>{$row['status']}</td>";
                echo "<td>
                        <a href='verify_user.php?id={$row['user_id']}&action=verify' class='btn btn-success btn-sm'>Verify</a>
                        <a href='verify_user.php?id={$row['user_id']}&action=reject' class='btn btn-danger btn-sm'>Reject</a>
                      </td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='4'>No pending job seeker verifications.</td></tr>";
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
                echo "<li>{$row['document_type']}: <a href='{$row['document_path']}' target='_blank'>View Document</a></li>";
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
<h3>Reports</h3>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>User</th>
            <th>Issue Type</th>
            <th>Description</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // Fetch all reports
        $sql = "SELECT reports.*, users.full_name 
                FROM reports 
                JOIN users ON reports.user_id = users.user_id 
                ORDER BY reports.created_at DESC";
        $reports_result = $conn->query($sql);

        if ($reports_result->num_rows > 0) {
            while ($row = $reports_result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>{$row['full_name']}</td>";
                echo "<td>{$row['issue_type']}</td>";
                echo "<td>{$row['description']}</td>";
                echo "<td>{$row['created_at']}</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='4'>No reports found.</td></tr>";
        }
        ?>
    </tbody>
</table>

        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
</body>
</html>