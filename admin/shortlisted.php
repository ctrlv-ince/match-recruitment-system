<?php
session_start();
include '../db.php';

// Redirect if not logged in as an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Get sort parameters
$sort_column = isset($_GET['sort']) ? $_GET['sort'] : 'applied_at';
$sort_order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

// Validate sort column to prevent SQL injection
$allowed_columns = ['candidate_name', 'job_title', 'company_name', 'applied_at'];
if (!in_array($sort_column, $allowed_columns)) {
    $sort_column = 'applied_at';
}

// Validate sort order
if ($sort_order != 'ASC' && $sort_order != 'DESC') {
    $sort_order = 'DESC';
}

// Helper function to generate sort URL
function getSortUrl($column, $currentSort, $currentOrder) {
    $newOrder = ($currentSort === $column && $currentOrder === 'ASC') ? 'DESC' : 'ASC';
    return "?sort=" . $column . "&order=" . $newOrder;
}

// Helper function to display sort indicator
function getSortIndicator($column, $currentSort, $currentOrder) {
    if ($currentSort !== $column) {
        return '<i class="bi bi-arrow-down-up text-muted"></i>';
    }
    return ($currentOrder === 'ASC') ? 
        '<i class="bi bi-sort-down-alt"></i>' : 
        '<i class="bi bi-sort-down"></i>';
}

// Fetch shortlisted candidates with sorting
$sql = "SELECT applications.application_id, users.full_name AS candidate_name, job_postings.title AS job_title, employers.company_name, applications.applied_at 
        FROM applications 
        JOIN users ON applications.seeker_id = users.user_id 
        JOIN job_postings ON applications.job_id = job_postings.job_id 
        JOIN employers ON job_postings.employer_id = employers.employer_id 
        WHERE applications.status = 'shortlisted' 
        ORDER BY $sort_column $sort_order";
$shortlisted_result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shortlisted Candidates</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet">
</head>
<style>
    /* Improved table styles */
    .table {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
    }

    .table thead th {
        background-color: #0a66c2;
        color: white;
        font-weight: 500;
        border-bottom: none;
        padding: 12px 15px;
        position: sticky;
        top: 0;
    }

    .table tbody tr {
        transition: all 0.2s ease;
        background-color: white;
    }

    .table tbody tr:hover {
        background-color: rgba(10, 102, 194, 0.05);
        transform: translateY(-1px);
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .table td,
    .table th {
        border-right: 1px solid #e0e0e0;
        border-bottom: 1px solid #e0e0e0;
        padding: 12px 15px;
        vertical-align: middle;
    }

    .table td:first-child,
    .table th:first-child {
        border-left: 1px solid #e0e0e0;
    }

    .table tr:first-child th:first-child {
        border-top-left-radius: 8px;
    }

    .table tr:first-child th:last-child {
        border-top-right-radius: 8px;
    }

    .table tr:last-child td:first-child {
        border-bottom-left-radius: 8px;
    }

    .table tr:last-child td:last-child {
        border-bottom-right-radius: 8px;
    }

    /* Button styles */
    .btn-sm {
        padding: 6px 12px;
        font-size: 0.85rem;
        border-radius: 4px;
        transition: all 0.2s ease;
    }

    .btn-primary {
        background-color: #0a66c2;
        border-color: #0a66c2;
    }

    .btn-primary:hover {
        background-color: #0957a8;
        border-color: #0957a8;
        transform: translateY(-1px);
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    /* Modal styles */
    .modal-content {
        border-radius: 8px;
        border: none;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    .modal-header {
        background-color: #0a66c2;
        color: white;
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
    }

    .modal-title {
        font-weight: 500;
    }

    /* Form styles */
    .form-control {
        padding: 8px 12px;
        border-radius: 4px;
        border: 1px solid #ced4da;
    }

    .form-control:focus {
        border-color: #0a66c2;
        box-shadow: 0 0 0 0.25rem rgba(10, 102, 194, 0.25);
    }

    /* Empty state style */
    .table tbody tr td[colspan] {
        text-align: center;
        color: #6c757d;
        padding: 20px;
    }
    
    /* Sortable header styles */
    .sortable {
        cursor: pointer;
        position: relative;
        padding-right: 25px !important;
    }

    .sortable:hover {
        background-color: #0957a8;
    }

    .sortable i {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
    }

    /* Highlight the active sort column */
    .sort-active {
        background-color: #0957a8;
    }
    
    /* Make sure links in headers are white and no underline */
    .sortable a {
        color: white;
        text-decoration: none;
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
                    <h2>Shortlisted Candidates</h2>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th class="sortable <?php echo $sort_column === 'candidate_name' ? 'sort-active' : ''; ?>">
                                    <a href="<?php echo getSortUrl('candidate_name', $sort_column, $sort_order); ?>">
                                        Candidate Name <?php echo getSortIndicator('candidate_name', $sort_column, $sort_order); ?>
                                    </a>
                                </th>
                                <th class="sortable <?php echo $sort_column === 'job_title' ? 'sort-active' : ''; ?>">
                                    <a href="<?php echo getSortUrl('job_title', $sort_column, $sort_order); ?>">
                                        Job Title <?php echo getSortIndicator('job_title', $sort_column, $sort_order); ?>
                                    </a>
                                </th>
                                <th class="sortable <?php echo $sort_column === 'company_name' ? 'sort-active' : ''; ?>">
                                    <a href="<?php echo getSortUrl('company_name', $sort_column, $sort_order); ?>">
                                        Employer <?php echo getSortIndicator('company_name', $sort_column, $sort_order); ?>
                                    </a>
                                </th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($shortlisted_result->num_rows > 0) {
                                while ($row = $shortlisted_result->fetch_assoc()) {
                                    echo '
                                    <tr>
                                        <td>' . $row['candidate_name'] . '</td>
                                        <td>' . $row['job_title'] . '</td>
                                        <td>' . $row['company_name'] . '</td>
                                        <td>
                                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#scheduleInterviewModal' . $row['application_id'] . '">Schedule Interview</button>
                                        </td>
                                    </tr>';

                                    // Modal for scheduling an interview
                                    echo '
                                    <div class="modal fade" id="scheduleInterviewModal' . $row['application_id'] . '" tabindex="-1" aria-labelledby="scheduleInterviewModalLabel' . $row['application_id'] . '" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="scheduleInterviewModalLabel' . $row['application_id'] . '">Schedule Interview</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form action="schedule_interview.php" method="POST">
                                                        <input type="hidden" name="application_id" value="' . $row['application_id'] . '">
                                                        <div class="mb-3">
                                                            <label for="scheduled_date" class="form-label">Interview Date & Time</label>
                                                            <input type="datetime-local" class="form-control" id="scheduled_date" name="scheduled_date" required>
                                                        </div>
                                                        <button type="submit" class="btn btn-primary">Schedule</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>';
                                }
                            } else {
                                echo '<tr><td colspan="4">No shortlisted candidates found.</td></tr>';
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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>