<?php
session_start();
include '../db.php';

// Redirect if not logged in as an employer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'employer') {
    header("Location: login.php");
    exit();
}

// Fetch employer details
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE user_id = $user_id";
$result = $conn->query($sql);
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employer Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0a66c2;
            --secondary-color: #f5f5f5;
            --border-color: #e0e0e0;
            --text-dark: #333;
            --text-light: #666;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--secondary-color);
            color: var(--text-dark);
        }
        
        .navbar {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .navbar-brand {
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .dashboard-container {
            max-width: 1200px;
            margin: 30px auto;
        }
        
        .profile-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid var(--primary-color);
        }
        
        .dashboard-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .dashboard-card-header {
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            background-color: var(--primary-color);
            color: white;
        }
        
        .dashboard-card-body {
            padding: 20px;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0b5ed7;
        }
        
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .accordion-button:not(.collapsed) {
            background-color: rgba(10, 102, 194, 0.1);
            color: var(--primary-color);
        }
        
        .accordion-button:focus {
            box-shadow: none;
            border-color: rgba(10, 102, 194, 0.25);
        }
        
        .job-card {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        
        .job-card:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }
        
        .job-card-header {
            padding: 15px;
            border-bottom: 1px solid var(--border-color);
            background-color: rgba(10, 102, 194, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .job-card-body {
            padding: 15px;
        }
        
        .badge {
            font-weight: 500;
            padding: 5px 10px;
        }
        
        .table {
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .table th {
            background-color: rgba(10, 102, 194, 0.05);
            border-color: var(--border-color);
        }
        
        .stats-box {
            background-color: white;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            text-align: center;
            margin-bottom: 20px;
        }
        
        .stats-number {
            font-size: 24px;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .stats-label {
            color: var(--text-light);
            font-size: 14px;
        }
        
        .action-btn {
            border-radius: 20px;
            padding: 5px 15px;
            margin-right: 5px;
        }
        
        .sidebar {
            position: sticky;
            top: 20px;
        }
        
        .sidebar-nav {
            list-style: none;
            padding: 0;
        }
        
        .sidebar-nav li {
            margin-bottom: 10px;
        }
        
        .sidebar-nav a {
            display: block;
            padding: 10px 15px;
            background-color: white;
            border-radius: 5px;
            text-decoration: none;
            color: var(--text-dark);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }
        
        .sidebar-nav a:hover, .sidebar-nav a.active {
            background-color: var(--primary-color);
            color: white;
        }
        
        .sidebar-nav i {
            margin-right: 10px;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container">
            <a class="navbar-brand" href="#">GoSeekr</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#"><i class="fas fa-home"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="post_job.php"><i class="fas fa-plus-circle"></i> Post Job</a>
                    </li>
                    <li class="nav-item">
                        <?php
                        // Fetch the number of unread notifications
                        $sql = "SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = $user_id AND status = 'unread'";
                        $unread_count = $conn->query($sql)->fetch_assoc()['unread_count'];
                        ?>
                        <a class="nav-link position-relative" href="notifications.php">
                            <i class="fas fa-bell"></i> Notifications
                            <?php if ($unread_count > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    <?php echo $unread_count; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle"></i> <?php echo $user['full_name']; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user-cog"></i> Profile Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container dashboard-container">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3">
                <div class="sidebar">
                    <div class="profile-card mb-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary rounded-circle text-white d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                <span style="font-size: 24px;"><?php echo substr($user['full_name'], 0, 1); ?></span>
                            </div>
                            <div>
                                <h5 class="mb-0"><?php echo $user['full_name']; ?></h5>
                                <small class="text-muted"><?php echo $user['email']; ?></small>
                            </div>
                        </div>
                    </div>
                    
                    <ul class="sidebar-nav mb-4">
                        <li><a href="#" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                        <li><a href="post_job.php"><i class="fas fa-plus-circle"></i> Post New Job</a></li>
                        <li><a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
                        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                    
                    <div class="stats-box">
                        <div class="stats-number">
                            <?php
                            // Count active job postings
                            $sql = "SELECT COUNT(*) AS active_count FROM job_postings WHERE employer_id = $user_id AND status = 'approved'";
                            $active_count = $conn->query($sql)->fetch_assoc()['active_count'];
                            echo $active_count;
                            ?>
                        </div>
                        <div class="stats-label">Active Jobs</div>
                    </div>
                    
                    <div class="stats-box">
                        <div class="stats-number">
                            <?php
                            // Count pending job applications
                            $sql = "SELECT COUNT(*) AS pending_count FROM applications a 
                                   JOIN job_postings j ON a.job_id = j.job_id 
                                   WHERE j.employer_id = $user_id AND a.status = 'pending'";
                            $pending_count = $conn->query($sql)->fetch_assoc()['pending_count'];
                            echo $pending_count;
                            ?>
                        </div>
                        <div class="stats-label">Pending Applications</div>
                    </div>
                </div>
            </div>
            
            <!-- Main Content Area -->
            <div class="col-lg-9">
                <!-- Job Postings Section -->
                <div class="dashboard-card">
                    <div class="dashboard-card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fas fa-briefcase me-2"></i> Your Job Postings</h4>
                        <a href="post_job.php" class="btn btn-sm btn-light"><i class="fas fa-plus-circle"></i> Post New Job</a>
                    </div>
                    <div class="dashboard-card-body">
                        <?php
                        // Fetch job postings for this employer
                        $sql = "SELECT * FROM job_postings WHERE employer_id = $user_id ORDER BY created_at DESC";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $job_id = $row['job_id'];
                                $status = $row['status'];
                                $quota = $row['quota'];
                                $quota_badge = ($quota <= 0) ? 'danger' : 'success';
                                $status_badge = ($status === 'approved') ? 'success' : (($status === 'pending') ? 'warning' : (($status === 'rejected') ? 'danger' : 'secondary'));
                                echo "
                                <div class='job-card'>
                                    <div class='job-card-header'>
                                        <h5 class='mb-0'>{$row['title']}</h5>
                                        <div>
                                            <span class='badge bg-{$status_badge} me-1'>{$status}</span>
                                            <span class='badge bg-{$quota_badge}'>Quota: {$quota}</span>
                                        </div>
                                    </div>
                                    <div class='job-card-body'>
                                        <div class='row'>
                                            <div class='col-md-8'>
                                                <p class='mb-1'><strong><i class='fas fa-align-left me-2'></i>Description:</strong> " . substr($row['description'], 0, 100) . "...</p>
                                                <p class='mb-1'><strong><i class='fas fa-list-check me-2'></i>Requirements:</strong> " . substr($row['requirements'], 0, 100) . "...</p>
                                                <p class='mb-0'><strong><i class='fas fa-calendar-alt me-2'></i>Posted On:</strong> " . date('F j, Y', strtotime($row['created_at'])) . "</p>
                                            </div>
                                            <div class='col-md-4 text-md-end mt-3 mt-md-0'>
                                                <button class='btn btn-sm btn-outline-primary' type='button' data-bs-toggle='collapse' data-bs-target='#jobDetails{$job_id}' aria-expanded='false' aria-controls='jobDetails{$job_id}'>
                                                    <i class='fas fa-chevron-down me-1'></i> View Details
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <div class='collapse mt-3' id='jobDetails{$job_id}'>
                                            <div class='card card-body bg-light'>
                                                <ul class='nav nav-tabs' id='jobTabs{$job_id}' role='tablist'>
                                                    <li class='nav-item' role='presentation'>
                                                        <button class='nav-link active' id='details-tab{$job_id}' data-bs-toggle='tab' data-bs-target='#details{$job_id}' type='button' role='tab' aria-controls='details{$job_id}' aria-selected='true'>Full Details</button>
                                                    </li>
                                                    <li class='nav-item' role='presentation'>
                                                        <button class='nav-link' id='shortlisted-tab{$job_id}' data-bs-toggle='tab' data-bs-target='#shortlisted{$job_id}' type='button' role='tab' aria-controls='shortlisted{$job_id}' aria-selected='false'>Shortlisted</button>
                                                    </li>
                                                    <li class='nav-item' role='presentation'>
                                                        <button class='nav-link' id='recommended-tab{$job_id}' data-bs-toggle='tab' data-bs-target='#recommended{$job_id}' type='button' role='tab' aria-controls='recommended{$job_id}' aria-selected='false'>Recommended</button>
                                                    </li>
                                                </ul>
                                                
                                                <div class='tab-content py-3' id='jobTabsContent{$job_id}'>
                                                    <!-- Full Job Details Tab -->
                                                    <div class='tab-pane fade show active' id='details{$job_id}' role='tabpanel' aria-labelledby='details-tab{$job_id}'>
                                                        <div class='row'>
                                                            <div class='col-md-12'>
                                                                <h6><i class='fas fa-align-left me-2'></i>Description:</h6>
                                                                <p>{$row['description']}</p>
                                                                
                                                                <h6><i class='fas fa-list-check me-2'></i>Requirements:</h6>
                                                                <p>{$row['requirements']}</p>
                                                                
                                                                <div class='d-flex flex-wrap gap-3 mt-3'>
                                                                    <div class='badge bg-light text-dark p-2'>
                                                                        <i class='fas fa-calendar-alt me-1'></i> Posted: " . date('F j, Y', strtotime($row['created_at'])) . "
                                                                    </div>
                                                                    <div class='badge bg-{$status_badge} p-2'>
                                                                        <i class='fas fa-info-circle me-1'></i> Status: {$status}
                                                                    </div>
                                                                    <div class='badge bg-{$quota_badge} p-2'>
                                                                        <i class='fas fa-users me-1'></i> Quota: {$quota}
                                                                    </div>
                                                                </div>";
                                                                
                                                                // Show a message if the quota is met
                                                                if ($quota <= 0) {
                                                                    echo "<div class='alert alert-danger mt-3'>
                                                                        <i class='fas fa-exclamation-triangle me-2'></i> This job is no longer accepting applications.
                                                                    </div>";
                                                                }
                                                        echo "</div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Shortlisted Candidates Tab -->
                                                    <div class='tab-pane fade' id='shortlisted{$job_id}' role='tabpanel' aria-labelledby='shortlisted-tab{$job_id}'>
                                                        <h6 class='mb-3'><i class='fas fa-user-check me-2'></i>Shortlisted Candidates</h6>";
                                                        
                                                        // Fetch shortlisted candidates for this job posting
                                                        $sql_candidates = "SELECT applications.application_id, applications.seeker_id, users.full_name, users.email 
                                                                          FROM applications 
                                                                          JOIN users ON applications.seeker_id = users.user_id 
                                                                          WHERE applications.job_id = $job_id AND applications.status = 'shortlisted'";
                                                        $candidates_result = $conn->query($sql_candidates);

                                                        if ($candidates_result->num_rows > 0) {
                                                            echo "<div class='table-responsive'>
                                                                <table class='table table-hover'>
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Name</th>
                                                                            <th>Email</th>
                                                                            <th>Actions</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>";
                                                            while ($candidate = $candidates_result->fetch_assoc()) {
                                                                echo "<tr>
                                                                        <td>
                                                                            <div class='d-flex align-items-center'>
                                                                                <div class='bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2' style='width: 32px; height: 32px;'>
                                                                                    <span>" . substr($candidate['full_name'], 0, 1) . "</span>
                                                                                </div>
                                                                                {$candidate['full_name']}
                                                                            </div>
                                                                        </td>
                                                                        <td>{$candidate['email']}</td>
                                                                        <td>
                                                                            <a href='view_shortlisted_candidate.php?seeker_id={$candidate['seeker_id']}&job_id={$job_id}' class='btn btn-sm btn-primary'>
                                                                                <i class='fas fa-eye me-1'></i> View
                                                                            </a>
                                                                        </td>
                                                                    </tr>";
                                                            }
                                                            echo "</tbody></table>
                                                            </div>";
                                                        } else {
                                                            echo "<div class='alert alert-info'>
                                                                <i class='fas fa-info-circle me-2'></i> No shortlisted candidates for this job yet.
                                                            </div>";
                                                        }
                                                        
                                                    echo "</div>
                                                    
                                                    <!-- Recommended Candidates Tab -->
                                                    <div class='tab-pane fade' id='recommended{$job_id}' role='tabpanel' aria-labelledby='recommended-tab{$job_id}'>
                                                        <h6 class='mb-3'><i class='fas fa-thumbs-up me-2'></i>Interviewed and Recommended Candidates</h6>";
                                                        
                                                        // Fetch interviewed and recommended candidates for this job posting
                                                        $sql_interviewed = "SELECT applications.application_id, applications.seeker_id, users.full_name, users.email 
                                                                          FROM applications 
                                                                          JOIN users ON applications.seeker_id = users.user_id 
                                                                          JOIN interviews ON applications.application_id = interviews.application_id 
                                                                          WHERE applications.job_id = $job_id 
                                                                          AND interviews.status = 'completed' 
                                                                          AND interviews.recommendation = 'recommended'";
                                                        $interviewed_result = $conn->query($sql_interviewed);

                                                        if ($interviewed_result->num_rows > 0) {
                                                            echo "<div class='table-responsive'>
                                                                <table class='table table-hover'>
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Name</th>
                                                                            <th>Email</th>
                                                                            <th>Actions</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>";
                                                            while ($candidate = $interviewed_result->fetch_assoc()) {
                                                                echo "<tr>
                                                                        <td>
                                                                            <div class='d-flex align-items-center'>
                                                                                <div class='bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-2' style='width: 32px; height: 32px;'>
                                                                                    <span>" . substr($candidate['full_name'], 0, 1) . "</span>
                                                                                </div>
                                                                                {$candidate['full_name']}
                                                                            </div>
                                                                        </td>
                                                                        <td>{$candidate['email']}</td>
                                                                        <td>
                                                                            <a href='view_candidate_details.php?seeker_id={$candidate['seeker_id']}&job_id={$job_id}' class='btn btn-sm btn-success'>
                                                                                <i class='fas fa-user-check me-1'></i> View
                                                                            </a>
                                                                        </td>
                                                                    </tr>";
                                                            }
                                                            echo "</tbody></table>
                                                            </div>";
                                                        } else {
                                                            echo "<div class='alert alert-info'>
                                                                <i class='fas fa-info-circle me-2'></i> No interviewed and recommended candidates for this job yet.
                                                            </div>";
                                                        }
                                                        
                                                    echo "</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>";
                            }
                        } else {
                            echo "<div class='alert alert-info'>
                                <i class='fas fa-info-circle me-2'></i> You have not posted any jobs yet. 
                                <a href='post_job.php' class='alert-link'>Click here</a> to post your first job.
                            </div>";
                        }
                        ?>
                    </div>
                </div>

                <!-- Job Offers Section -->
                <div class="dashboard-card mt-4">
                    <div class="dashboard-card-header">
                        <h4 class="mb-0"><i class="fas fa-handshake me-2"></i> Your Job Offers</h4>
                    </div>
                    <div class="dashboard-card-body">
                        <?php
                        // Fetch job offers for this employer
                        $sql_offers = "SELECT job_offers.*, job_postings.title, users.full_name 
                                   FROM job_offers 
                                   JOIN job_postings ON job_offers.job_id = job_postings.job_id 
                                   JOIN users ON job_offers.seeker_id = users.user_id 
                                   WHERE job_offers.employer_id = $user_id 
                                   ORDER BY job_offers.created_at DESC";
                        $offers_result = $conn->query($sql_offers);

                        if ($offers_result->num_rows > 0) {
                            echo "<div class='accordion' id='jobOffersAccordion'>";
                            while ($offer = $offers_result->fetch_assoc()) {
                                $offer_id = $offer['offer_id'];
                                $status = $offer['status'];
                                $status_badge = ($status === 'pending') ? 'warning' : (($status === 'accepted') ? 'success' : (($status === 'declined') ? 'danger' : (($status === 'expired') ? 'secondary' : 'primary')));
                                $status_icon = ($status === 'pending') ? 'clock' : (($status === 'accepted') ? 'check-circle' : (($status === 'declined') ? 'times-circle' : (($status === 'expired') ? 'calendar-xmark' : 'info-circle')));
                                
                                echo "
                                <div class='job-card'>
                                    <div class='job-card-header'>
                                        <h5 class='mb-0'>Offer for {$offer['title']}</h5>
                                        <span class='badge bg-{$status_badge}'><i class='fas fa-{$status_icon} me-1'></i> {$status}</span>
                                    </div>
                                    <div class='job-card-body'>
                                        <div class='row'>
                                            <div class='col-md-8'>
                                                <p class='mb-1'>
                                                    <strong><i class='fas fa-user me-2'></i>Candidate:</strong> 
                                                    <span class='text-primary'>{$offer['full_name']}</span>
                                                </p>
                                                <p class='mb-1'>
                                                    <strong><i class='fas fa-file-contract me-2'></i>Offer Details:</strong> 
                                                    {$offer['offer_details']}
                                                </p>
                                                <p class='mb-1'>
                                                    <strong><i class='fa-solid fa-dollar-sign me-2'></i>Salary: </strong> 
                                                    ₱{$offer['salary']}
                                                </p>
                                                <p class='mb-0'>
                                                    <strong><i class='fas fa-calendar-alt me-2'></i>Created At:</strong> 
                                                    " . date('F j, Y', strtotime($offer['created_at'])) . "
                                                </p>
                                            </div>
                                            <div class='col-md-4 text-md-end mt-3 mt-md-0'>";
                                                
                                                // Show actions based on offer status
                                                if ($status === 'pending') {
                                                    echo "<a href='view_candidate_details.php?seeker_id={$offer['seeker_id']}&job_id={$offer['job_id']}' class='btn btn-sm btn-primary'>
                                                        <i class='fas fa-eye me-1'></i> View Candidate
                                                    </a>";
                                                } elseif ($status === 'accepted') {
                                                    echo "<div class='alert alert-success py-2 px-3 mb-0'>
                                                        <i class='fas fa-check-circle me-1'></i> Offer accepted
                                                    </div>";
                                                } elseif ($status === 'declined') {
                                                    echo "<div class='alert alert-danger py-2 px-3 mb-0'>
                                                        <i class='fas fa-times-circle me-1'></i> Offer declined
                                                    </div>";
                                                } elseif ($status === 'expired') {
                                                    echo "<div class='alert alert-secondary py-2 px-3 mb-0'>
                                                        <i class='fas fa-calendar-times me-1'></i> Offer expired
                                                    </div>";
                                                }
                                                
                                            echo "</div>
                                        </div>
                                    </div>
                                </div>";
                            }
                        } else {
                            echo "<div class='alert alert-info'>
                                <i class='fas fa-info-circle me-2'></i> No job offers found. When you extend offers to candidates, they will appear here.
                            </div>";
                        }
                        ?>
                    </div>
                </div>
                
                <!-- Quick Actions Section -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="dashboard-card h-100">
                            <div class="dashboard-card-header">
                                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i> Quick Actions</h5>
                            </div>
                            <div class="dashboard-card-body">
                                <div class="d-grid gap-2">
                                    <a href="post_job.php" class="btn btn-outline-primary">
                                        <i class="fas fa-plus-circle me-2"></i> Post a New Job
                                    </a>
                                    <a href="notifications.php" class="btn btn-outline-primary position-relative">
                                        <i class="fas fa-bell me-2"></i> View Notifications
                                        <?php if ($unread_count > 0): ?>
                                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                                <?php echo $unread_count; ?>
                                            </span>
                                        <?php endif; ?>
                                    </a>
                                    <a href="logout.php" class="btn btn-outline-danger">
                                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="dashboard-card h-100">
                            <div class="dashboard-card-header">
                                <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i> Activity Overview</h5>
                            </div>
                            <div class="dashboard-card-body">
                                <?php
                                // Get some quick stats
                                $sql_total_jobs = "SELECT COUNT(*) as total FROM job_postings WHERE employer_id = $user_id";
                                $total_jobs = $conn->query($sql_total_jobs)->fetch_assoc()['total'];
                                
                                $sql_total_offers = "SELECT COUNT(*) as total FROM job_offers WHERE employer_id = $user_id";
                                $total_offers = $conn->query($sql_total_offers)->fetch_assoc()['total'];
                                
                                $sql_accepted_offers = "SELECT COUNT(*) as total FROM job_offers WHERE employer_id = $user_id AND status = 'accepted'";
                                $accepted_offers = $conn->query($sql_accepted_offers)->fetch_assoc()['total'];
                                ?>
                                
                                <div class="row text-center">
                                    <div class="col-4">
                                        <div class="p-3">
                                            <h3 class="text-primary mb-0"><?php echo $total_jobs; ?></h3>
                                            <div class="text-muted small">Total Jobs</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="p-3">
                                            <h3 class="text-primary mb-0"><?php echo $total_offers; ?></h3>
                                            <div class="text-muted small">Total Offers</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="p-3">
                                            <h3 class="text-success mb-0"><?php echo $accepted_offers; ?></h3>
                                            <div class="text-muted small">Accepted</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-light mt-5 py-3">
        <div class="container text-center">
            <p class="text-muted mb-0">&copy; <?php echo date('Y'); ?> GoSeekr. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap JS (required for accordion functionality) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>