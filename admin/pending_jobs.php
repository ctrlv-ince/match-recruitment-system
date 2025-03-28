<?php
session_start();
include '../db.php';

// Redirect if not logged in as an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch shortlisted candidates
$sql = "SELECT applications.application_id, users.full_name AS candidate_name, job_postings.title AS job_title, employers.company_name 
        FROM applications 
        JOIN users ON applications.seeker_id = users.user_id 
        JOIN job_postings ON applications.job_id = job_postings.job_id 
        JOIN employers ON job_postings.employer_id = employers.employer_id 
        WHERE applications.status = 'shortlisted' 
        ORDER BY applications.applied_at DESC";
$shortlisted_result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Jobs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet">
</head>
<style>
    /* Table styling */
    .table {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
        background-color: white;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
        border-radius: 8px;
        overflow: hidden;
    }

    .table thead th {
        background-color: #0a66c2;
        color: white;
        font-weight: 500;
        padding: 15px;
        position: sticky;
        top: 0;
        border: none;
    }

    .table tbody tr {
        transition: all 0.2s ease;
    }

    .table tbody tr:hover {
        background-color: rgba(10, 102, 194, 0.05);
        transform: translateY(-1px);
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .table td {
        padding: 12px 15px;
        vertical-align: middle;
        border-bottom: 1px solid #e9ecef;
    }

    /* Status styling */
    .table td:nth-child(5) {
        text-transform: capitalize;
        font-weight: 500;
        color: #6c757d;
    }

    /* Button styling */
    .btn-sm {
        padding: 6px 12px;
        font-size: 0.85rem;
        border-radius: 4px;
        transition: all 0.2s ease;
        margin-right: 5px;
    }

    .btn-success {
        background-color: #28a745;
        border-color: #28a745;
    }

    .btn-success:hover {
        background-color: #218838;
        border-color: #1e7e34;
        transform: translateY(-1px);
    }

    .btn-danger {
        background-color: #dc3545;
        border-color: #dc3545;
    }

    .btn-danger:hover {
        background-color: #c82333;
        border-color: #bd2130;
        transform: translateY(-1px);
    }

    /* Text content styling */
    .table td:nth-child(3),
    .table td:nth-child(4) {
        max-width: 300px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .table td:nth-child(3):hover,
    .table td:nth-child(4):hover {
        white-space: normal;
        overflow: visible;
        position: relative;
        z-index: 1;
        background-color: white;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    /* Empty state */
    .table tbody tr td[colspan] {
        text-align: center;
        color: #6c757d;
        padding: 30px;
        font-style: italic;
    }

    /* Action column styling */
    .table td:last-child {
        white-space: nowrap;
    }
</style>

<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Main Content -->
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h2>Pending Jobs</h2>
                </div>

                <div class="table-responsive">
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
                </div>
            </main>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>