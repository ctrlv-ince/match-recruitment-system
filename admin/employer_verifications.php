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
    <title>Employer Verifications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet">
</head>
<style>
    /* Employer Verification Table Styling */
    .table {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
        background: white;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
        border-radius: 10px;
        overflow: hidden;
    }

    .table thead th {
        background: #2c3e50;
        color: white;
        font-weight: 500;
        padding: 16px 20px;
        border: none;
        position: sticky;
        top: 0;
        font-size: 0.9em;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .table tbody tr {
        transition: all 0.2s ease;
    }

    .table tbody tr:nth-child(even) {
        background-color: #f8fafc;
    }

    .table tbody tr:hover {
        background-color: #f1f9ff;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .table td {
        padding: 14px 20px;
        border-bottom: 1px solid #e9ecef;
        vertical-align: top;
    }

    /* Document List Styling */
    .table td ul {
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .table td ul li {
        padding: 6px 0;
        border-bottom: 1px dashed #e0e0e0;
    }

    .table td ul li:last-child {
        border-bottom: none;
    }

    .table td ul li a {
        color: #3498db;
        text-decoration: none;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
    }

    .table td ul li a:hover {
        color: #1a5276;
        text-decoration: underline;
    }

    .table td ul li a i {
        margin-right: 6px;
        font-size: 0.9em;
    }

    /* Button Styling */
    .table .btn {
        padding: 8px 16px;
        font-size: 0.85rem;
        border-radius: 6px;
        transition: all 0.2s;
        font-weight: 500;
        letter-spacing: 0.5px;
        margin: 4px;
        min-width: 90px;
        text-align: center;
    }

    .table .btn-success {
        background: #27ae60;
        border-color: #27ae60;
    }

    .table .btn-success:hover {
        background: #219653;
        border-color: #1e8449;
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(39, 174, 96, 0.3);
    }

    .table .btn-danger {
        background: #e74c3c;
        border-color: #e74c3c;
    }

    .table .btn-danger:hover {
        background: #c0392b;
        border-color: #b03a2e;
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(231, 76, 60, 0.3);
    }

    /* Empty State Styling */
    .table td[colspan] {
        text-align: center;
        color: #7f8c8d;
        font-style: italic;
        padding: 30px;
        background: #f9f9f9;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .table thead th {
            padding: 12px 15px;
            font-size: 0.8em;
        }

        .table td {
            padding: 10px 12px;
            font-size: 0.9em;
        }

        .table .btn {
            padding: 6px 10px;
            font-size: 0.8em;
            min-width: 80px;
            margin: 2px;
            display: block;
            width: 100%;
        }
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
                    <h2>Employer Verifications</h2>
                </div>

                <div class="table-responsive">
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
                </div>
            </main>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>