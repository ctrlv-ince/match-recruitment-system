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
    <title>Job Seeker Verifications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet">
</head>
<style>
    /* Enhanced Table Styling - Add this to your existing styles */
    .table {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
        background: white;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        border-radius: 8px;
        overflow: hidden;
    }
    
    .table thead th {
        background: #3498db;
        color: white;
        font-weight: 500;
        padding: 15px;
        border: none;
        position: sticky;
        top: 0;
    }
    
    .table tbody tr {
        transition: all 0.2s ease;
    }
    
    .table tbody tr:nth-child(even) {
        background-color: #f8f9fa;
    }
    
    .table tbody tr:hover {
        background-color: #e9f7fe;
    }
    
    .table td {
        padding: 12px 15px;
        border-bottom: 1px solid #e0e0e0;
        vertical-align: middle;
    }
    
    /* Status Badge Styling */
    .table td:nth-child(3) {
        font-weight: 500;
    }
    
    .table td:nth-child(3):before {
        content: "";
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-right: 8px;
    }
    
    .table td:nth-child(3)[data-status="pending"] {
        color: #FFA500;
    }
    
    .table td:nth-child(3)[data-status="pending"]:before {
        background: #FFA500;
    }
    
    /* Document Links Styling */
    .table td:nth-child(4) a {
        color: #2980b9;
        text-decoration: none;
        transition: all 0.2s;
        display: inline-block;
        margin-right: 10px;
    }
    
    .table td:nth-child(4) a:hover {
        color: #1a5276;
        text-decoration: underline;
    }
    
    /* Button Styling */
    .table .btn {
        padding: 6px 12px;
        font-size: 0.85rem;
        border-radius: 4px;
        transition: all 0.2s;
    }
    
    .table .btn-sm {
        padding: 5px 10px;
        font-size: 0.8rem;
    }
    
    .table .btn-success {
        background: #27ae60;
        border-color: #27ae60;
    }
    
    .table .btn-success:hover {
        background: #219653;
        border-color: #1e8449;
    }
    
    .table .btn-danger {
        background: #e74c3c;
        border-color: #e74c3c;
    }
    
    .table .btn-danger:hover {
        background: #c0392b;
        border-color: #b03a2e;
    }
    
    /* Empty State Styling */
    .table td.empty-state {
        text-align: center;
        color: #7f8c8d;
        font-style: italic;
        padding: 30px;
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
                    <h2>Job Seeker Verifications</h2>
                </div>

                <div class="table-responsive">
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
                                            echo "<p><strong>$doc_type:</strong> <a href='../job_seeker/$doc_path' target='_blank'>View Document</a></p>";
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
                </div>
            </main>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>