<?php
session_start();
include '../db.php';

// Redirect if not logged in as an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Get sort parameters
$sort_column = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
$sort_order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

// Validate sort column to prevent SQL injection
$allowed_columns = ['full_name', 'rating', 'comments', 'created_at'];
if (!in_array($sort_column, $allowed_columns)) {
    $sort_column = 'created_at';
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
    <title>Feedbacks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet">
</head>
<style>
    /* Unified Admin Table Design System */
    .table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        background: #fff;
        box-shadow: 0 2px 16px rgba(0, 0, 0, 0.08);
        border-radius: 8px;
        overflow: hidden;
        font-size: 0.925em;
        margin: 20px 0;
    }

    /* Consistent Header Styling */
    .table thead th {
        background: #2c3e50;
        color: #fff;
        padding: 14px 20px;
        font-weight: 500;
        text-align: left;
        border: none;
        position: sticky;
        top: 0;
    }

    /* Consistent Body Styling */
    .table tbody tr {
        transition: all 0.2s ease;
    }

    .table tbody tr:nth-child(even) {
        background-color: #f8fafc;
    }

    .table tbody tr:hover {
        background-color: #f1f7fe;
    }

    .table td {
        padding: 14px 20px;
        border-bottom: 1px solid #eaeff5;
        vertical-align: middle;
    }

    /* Consistent Status/Data Point Styling */
    .table td:nth-child(2) { /* Rating column */
        color: #e67e22;
        font-weight: 500;
    }

    /* Consistent Action Button Area */
    .table td:last-child {
        white-space: nowrap;
    }

    /* Consistent Empty State */
    .table td.empty-state {
        text-align: center;
        padding: 30px;
        color: #7f8c8d;
        font-style: italic;
        background: #f9f9f9;
    }
    
    @media (max-width: 768px) {
        .table {
            font-size: 0.85em;
        }
        .table th, 
        .table td {
            padding: 12px 15px;
        }
    }
    
    /* Sortable header styles */
    .sortable {
        cursor: pointer;
        position: relative;
        padding-right: 25px !important;
    }

    .sortable:hover {
        background-color: #1a2530;
    }

    .sortable i {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
    }

    /* Highlight the active sort column */
    .sort-active {
        background-color: #1a2530;
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
                    <h2>Feedbacks</h2>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th class="sortable <?php echo $sort_column === 'full_name' ? 'sort-active' : ''; ?>">
                                    <a href="<?php echo getSortUrl('full_name', $sort_column, $sort_order); ?>" class="text-white text-decoration-none">
                                        User <?php echo getSortIndicator('full_name', $sort_column, $sort_order); ?>
                                    </a>
                                </th>
                                <th class="sortable <?php echo $sort_column === 'rating' ? 'sort-active' : ''; ?>">
                                    <a href="<?php echo getSortUrl('rating', $sort_column, $sort_order); ?>" class="text-white text-decoration-none">
                                        Rating <?php echo getSortIndicator('rating', $sort_column, $sort_order); ?>
                                    </a>
                                </th>
                                <th class="sortable <?php echo $sort_column === 'comments' ? 'sort-active' : ''; ?>">
                                    <a href="<?php echo getSortUrl('comments', $sort_column, $sort_order); ?>" class="text-white text-decoration-none">
                                        Comments <?php echo getSortIndicator('comments', $sort_column, $sort_order); ?>
                                    </a>
                                </th>
                                <th class="sortable <?php echo $sort_column === 'created_at' ? 'sort-active' : ''; ?>">
                                    <a href="<?php echo getSortUrl('created_at', $sort_column, $sort_order); ?>" class="text-white text-decoration-none">
                                        Date <?php echo getSortIndicator('created_at', $sort_column, $sort_order); ?>
                                    </a>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Fetch all feedback with sorting
                            $sql = "SELECT feedback.*, users.full_name 
                                    FROM feedback 
                                    JOIN users ON feedback.user_id = users.user_id 
                                    ORDER BY $sort_column $sort_order";
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
                </div>
            </main>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>