<?php
session_start();
include '../db.php';

// Redirect if not logged in as an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Get sort parameters
$sort_column = isset($_GET['sort']) ? $_GET['sort'] : 'scheduled_date';
$sort_order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

// Validate sort column to prevent SQL injection
$allowed_columns = ['candidate_name', 'job_title', 'company_name', 'scheduled_date', 'status'];
if (!in_array($sort_column, $allowed_columns)) {
    $sort_column = 'scheduled_date';
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

// Fetch scheduled interviews with sorting
$sql = "SELECT interviews.interview_id, interviews.scheduled_date, interviews.status, 
               interviews.notes, interviews.recommendation, 
               users.full_name AS candidate_name, job_postings.title AS job_title, employers.company_name 
        FROM interviews 
        JOIN applications ON interviews.application_id = applications.application_id 
        JOIN users ON applications.seeker_id = users.user_id 
        JOIN job_postings ON applications.job_id = job_postings.job_id 
        JOIN employers ON job_postings.employer_id = employers.employer_id 
        ORDER BY $sort_column $sort_order";
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

    /* Status badges */
    .badge {
        padding: 6px 10px;
        font-weight: 500;
        font-size: 0.8rem;
        border-radius: 4px;
    }

    .bg-warning {
        background-color: #ffc107 !important;
        color: #212529;
    }

    .bg-success {
        background-color: #28a745 !important;
    }

    .bg-danger {
        background-color: #dc3545 !important;
    }

    /* Button styling */
    .btn-sm {
        padding: 6px 12px;
        font-size: 0.85rem;
        border-radius: 4px;
        transition: all 0.2s ease;
    }

    .btn-info {
        background-color: #17a2b8;
        border-color: #17a2b8;
    }

    .btn-info:hover {
        background-color: #138496;
        border-color: #117a8b;
        transform: translateY(-1px);
    }

    /* Modal styling */
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

    /* Form styling */
    .form-control {
        padding: 8px 12px;
        border-radius: 4px;
        border: 1px solid #ced4da;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .form-control:focus {
        border-color: #0a66c2;
        box-shadow: 0 0 0 0.25rem rgba(10, 102, 194, 0.25);
    }

    textarea.form-control {
        min-height: 100px;
    }

    /* Empty state */
    .table tbody tr td[colspan] {
        text-align: center;
        color: #6c757d;
        padding: 30px;
        font-style: italic;
    }

    /* Date column styling */
    .table td:nth-child(4) {
        white-space: nowrap;
    }

    /* Action column styling */
    .table td:last-child {
        white-space: nowrap;
    }
    
    /* Sortable header styles */
    .sortable {
        cursor: pointer;
        position: relative;
        padding-right: 25px !important;
    }

    .sortable:hover {
        background-color: #0857a2;
    }

    .sortable i {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
    }

    /* Highlight the active sort column */
    .sort-active {
        background-color: #0857a2;
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
                    <h2>Scheduled Interviews</h2>
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
                                <th class="sortable <?php echo $sort_column === 'scheduled_date' ? 'sort-active' : ''; ?>">
                                    <a href="<?php echo getSortUrl('scheduled_date', $sort_column, $sort_order); ?>">
                                        Scheduled Date <?php echo getSortIndicator('scheduled_date', $sort_column, $sort_order); ?>
                                    </a>
                                </th>
                                <th class="sortable <?php echo $sort_column === 'status' ? 'sort-active' : ''; ?>">
                                    <a href="<?php echo getSortUrl('status', $sort_column, $sort_order); ?>">
                                        Status <?php echo getSortIndicator('status', $sort_column, $sort_order); ?>
                                    </a>
                                </th>
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
                <div class="mb-3">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3">' . $row['notes'] . '</textarea>
                </div>
                <div class="mb-3">
                    <label for="recommendation" class="form-label">Recommendation</label>
                    <select class="form-control" id="recommendation" name="recommendation" required>
                        <option value="recommended" ' . ($row['recommendation'] === 'recommended' ? 'selected' : '') . '>Recommended</option>
                        <option value="not recommended" ' . ($row['recommendation'] === 'not recommended' ? 'selected' : '') . '>Not Recommended</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
            </form>
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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>