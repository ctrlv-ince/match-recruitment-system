<?php
session_start();
include '../db.php';

// Redirect if not logged in as an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Set default date range (last 30 days)
$end_date = date('Y-m-d');
$start_date = date('Y-m-d', strtotime('-30 days'));

// Handle date range filter
if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];
}

// Error handling for database queries
function safeQuery($conn, $sql) {
    $result = $conn->query($sql);
    if (!$result) {
        // Log the error instead of displaying it
        error_log("Database query error: " . $conn->error . " in query: " . $sql);
        return false;
    }
    return $result;
}

// Fetch key metrics with error handling
$total_jobs_query = safeQuery($conn, "SELECT COUNT(*) as total FROM job_postings WHERE status = 'approved'");
$total_jobs = $total_jobs_query ? $total_jobs_query->fetch_assoc()['total'] : 0;

$total_applications_query = safeQuery($conn, "SELECT COUNT(*) as total FROM applications");
$total_applications = $total_applications_query ? $total_applications_query->fetch_assoc()['total'] : 0;

$hired_candidates_query = safeQuery($conn, "SELECT COUNT(*) as total FROM applications WHERE status = 'hired'");
$hired_candidates = $hired_candidates_query ? $hired_candidates_query->fetch_assoc()['total'] : 0;

$rejected_candidates_query = safeQuery($conn, "SELECT COUNT(*) as total FROM applications WHERE status = 'rejected'");
$rejected_candidates = $rejected_candidates_query ? $rejected_candidates_query->fetch_assoc()['total'] : 0;

// Jobs by category - Check if category column exists
$jobs_by_category_check = safeQuery($conn, "SHOW COLUMNS FROM job_postings LIKE 'category'");
if ($jobs_by_category_check && $jobs_by_category_check->num_rows > 0) {
    $jobs_by_category = safeQuery($conn, "
        SELECT category, COUNT(*) as count 
        FROM job_postings 
        WHERE status = 'approved' 
        GROUP BY category 
        ORDER BY count DESC
    ");
} else {
    // If category column doesn't exist, group by title as a fallback
    $jobs_by_category = safeQuery($conn, "
        SELECT title as category, COUNT(*) as count 
        FROM job_postings 
        WHERE status = 'approved' 
        GROUP BY title 
        ORDER BY count DESC
        LIMIT 10
    ");
}

// Applications by status
$applications_by_status = safeQuery($conn, "
    SELECT status, COUNT(*) as count 
    FROM applications 
    GROUP BY status 
    ORDER BY count DESC
");

// Monthly job postings (for the chart)
$monthly_jobs = safeQuery($conn, "
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count 
    FROM job_postings 
    WHERE status = 'approved' AND created_at BETWEEN '$start_date' AND '$end_date 23:59:59'
    GROUP BY DATE_FORMAT(created_at, '%Y-%m') 
    ORDER BY month
");

// Monthly applications (for the chart)
$monthly_applications = safeQuery($conn, "
    SELECT DATE_FORMAT(applied_at, '%Y-%m') as month, COUNT(*) as count 
    FROM applications 
    WHERE applied_at BETWEEN '$start_date' AND '$end_date 23:59:59'
    GROUP BY DATE_FORMAT(applied_at, '%Y-%m') 
    ORDER BY month
");

// Top employers by job postings
$top_employers = safeQuery($conn, "
    SELECT u.full_name, COUNT(j.job_id) as job_count 
    FROM job_postings j
    JOIN users u ON j.employer_id = u.user_id
    WHERE j.status = 'approved'
    GROUP BY j.employer_id
    ORDER BY job_count DESC
    LIMIT 5
");

// Recent hires - Updated to match your database schema
$recent_hires = safeQuery($conn, "
    SELECT a.application_id, u.full_name as candidate_name, j.title as job_title, 
           e.full_name as employer_name, a.applied_at as hire_date
    FROM applications a
    JOIN users u ON a.seeker_id = u.user_id
    JOIN job_postings j ON a.job_id = j.job_id
    JOIN users e ON j.employer_id = e.user_id
    WHERE a.status = 'hired'
    ORDER BY a.applied_at DESC
    LIMIT 10
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="../css/styles.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f3f6f8;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .stats-card {
            text-align: center;
            padding: 15px;
        }

        .stats-card .number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #0a66c2;
        }

        .stats-card .label {
            font-size: 1rem;
            color: #666;
        }

        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 20px;
        }

        .table-container {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .date-filter {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
    </style>

    <div class="container-fluid">
        <div class="row">
            <!-- Include sidebar if it exists -->
            <?php if (file_exists('includes/sidebar.php')) include 'includes/sidebar.php'; ?>
            
            <!-- Main Content -->
            <main class="<?php echo file_exists('includes/sidebar.php') ? 'col-md-9 ms-sm-auto col-lg-10 px-md-4' : 'container'; ?> mt-4">
                <h2 class="mb-4">Admin Reports</h2>

                <!-- Date Range Filter -->
                <div class="date-filter">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-4">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">Apply Filter</button>
                        </div>
                    </form>
                </div>

                <!-- Key Metrics -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="number"><?php echo $total_jobs; ?></div>
                            <div class="label">Approved Jobs</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="number"><?php echo $total_applications; ?></div>
                            <div class="label">Total Applications</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="number"><?php echo $hired_candidates; ?></div>
                            <div class="label">Hired Candidates</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="number"><?php echo $rejected_candidates; ?></div>
                            <div class="label">Rejected Candidates</div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row mt-4">
                    <!-- Monthly Trends Chart -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5>Monthly Trends</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="monthlyTrendsChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Applications by Status -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5>Applications by Status</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="applicationStatusChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Second Row of Charts/Tables -->
                <div class="row mt-4">
                    <!-- Jobs by Category/Title -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Jobs by <?php echo $jobs_by_category_check && $jobs_by_category_check->num_rows > 0 ? 'Category' : 'Title'; ?></h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th><?php echo $jobs_by_category_check && $jobs_by_category_check->num_rows > 0 ? 'Category' : 'Job Title'; ?></th>
                                                <th>Number of Jobs</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            if ($jobs_by_category && $jobs_by_category->num_rows > 0) {
                                                while ($row = $jobs_by_category->fetch_assoc()) {
                                                    echo '<tr>
                                                        <td>' . htmlspecialchars($row['category']) . '</td>
                                                        <td>' . $row['count'] . '</td>
                                                    </tr>';
                                                }
                                            } else {
                                                echo '<tr><td colspan="2">No data available</td></tr>';
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top Employers -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Top Employers</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Employer</th>
                                                <th>Job Postings</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            if ($top_employers && $top_employers->num_rows > 0) {
                                                while ($row = $top_employers->fetch_assoc()) {
                                                    echo '<tr>
                                                        <td>' . htmlspecialchars($row['full_name']) . '</td>
                                                        <td>' . $row['job_count'] . '</td>
                                                    </tr>';
                                                }
                                            } else {
                                                echo '<tr><td colspan="2">No data available</td></tr>';
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Hires -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Recent Hires</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Candidate</th>
                                                <th>Job Title</th>
                                                <th>Employer</th>
                                                <th>Hire Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            if ($recent_hires && $recent_hires->num_rows > 0) {
                                                while ($row = $recent_hires->fetch_assoc()) {
                                                    echo '<tr>
                                                        <td>' . htmlspecialchars($row['candidate_name']) . '</td>
                                                        <td>' . htmlspecialchars($row['job_title']) . '</td>
                                                        <td>' . htmlspecialchars($row['employer_name']) . '</td>
                                                        <td>' . date('M d, Y', strtotime($row['hire_date'])) . '</td>
                                                    </tr>';
                                                }
                                            } else {
                                                echo '<tr><td colspan="4">No recent hires</td></tr>';
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Prepare data for monthly trends chart
        const monthlyJobsData = [
            <?php
            $months = [];
            $jobCounts = [];
            
            if ($monthly_jobs && $monthly_jobs->num_rows > 0) {
                while ($row = $monthly_jobs->fetch_assoc()) {
                    $months[] = "'" . date('M Y', strtotime($row['month'] . '-01')) . "'";
                    $jobCounts[] = $row['count'];
                }
            }
            echo implode(',', $jobCounts);
            ?>
        ];

        const monthlyApplicationsData = [
            <?php
            $applicationCounts = [];
            $applicationMonths = [];
            
            if ($monthly_applications && $monthly_applications->num_rows > 0) {
                while ($row = $monthly_applications->fetch_assoc()) {
                    $applicationMonths[] = "'" . date('M Y', strtotime($row['month'] . '-01')) . "'";
                    $applicationCounts[] = $row['count'];
                }
            }
            echo implode(',', $applicationCounts);
            ?>
        ];

        const monthLabels = [<?php echo !empty($months) ? implode(',', $months) : "''"; ?>];

        // Only create charts if we have data
        if (monthLabels.length > 0 && monthLabels[0] !== '') {
            // Monthly trends chart
            const trendsCtx = document.getElementById('monthlyTrendsChart').getContext('2d');
            new Chart(trendsCtx, {
                type: 'line',
                data: {
                    labels: monthLabels,
                    datasets: [{
                        label: 'Job Postings',
                        data: monthlyJobsData,
                        borderColor: '#0a66c2',
                        backgroundColor: 'rgba(10, 102, 194, 0.1)',
                        tension: 0.1,
                        fill: true
                    }, {
                        label: 'Applications',
                        data: monthlyApplicationsData,
                        borderColor: '#2ecc71',
                        backgroundColor: 'rgba(46, 204, 113, 0.1)',
                        tension: 0.1,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Monthly Job Postings & Applications'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        } else {
            document.getElementById('monthlyTrendsChart').innerHTML = '<div class="text-center p-5">No data available for the selected date range</div>';
        }

        // Application status chart
        const statusLabels = [
            <?php
            $statusLabels = [];
            $statusCounts = [];
            
            if ($applications_by_status && $applications_by_status->num_rows > 0) {
                while ($row = $applications_by_status->fetch_assoc()) {
                    $statusLabels[] = "'" . ucfirst($row['status']) . "'";
                    $statusCounts[] = $row['count'];
                }
                echo implode(',', $statusLabels);
            } else {
                echo "''";
            }
            ?>
        ];
        
        const statusData = [
            <?php 
            if (!empty($statusCounts)) {
                echo implode(',', $statusCounts);
            } else {
                echo "0";
            }
            ?>
        ];

        if (statusLabels.length > 0 && statusLabels[0] !== '') {
            const statusCtx = document.getElementById('applicationStatusChart').getContext('2d');
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: statusLabels,
                    datasets: [{
                        data: statusData,
                        backgroundColor: [
                            '#2ecc71', // green
                            '#e74c3c', // red
                            '#f39c12', // orange
                            '#3498db', // blue
                            '#9b59b6', // purple
                            '#1abc9c'  // teal
                        ],
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                        }
                    }
                }
            });
        } else {
            document.getElementById('applicationStatusChart').innerHTML = '<div class="text-center p-5">No application status data available</div>';
        }
    </script>
</body>

</html>