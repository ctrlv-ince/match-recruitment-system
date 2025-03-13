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

// Fetch the number of unread notifications
$sql = "SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = $user_id AND status = 'unread'";
$unread_count = $conn->query($sql)->fetch_assoc()['unread_count'];

// Count active job postings
$sql = "SELECT COUNT(*) AS active_count FROM job_postings WHERE employer_id = $user_id AND status = 'approved'";
$active_count = $conn->query($sql)->fetch_assoc()['active_count'];

// Count pending applications
$sql = "SELECT COUNT(*) AS pending_count FROM applications 
        JOIN job_postings ON applications.job_id = job_postings.job_id 
        WHERE job_postings.employer_id = $user_id AND applications.status = 'pending'";
$pending_count = $conn->query($sql)->fetch_assoc()['pending_count'];

// Count shortlisted candidates
$sql = "SELECT COUNT(*) AS shortlisted_count FROM applications 
        JOIN job_postings ON applications.job_id = job_postings.job_id 
        WHERE job_postings.employer_id = $user_id AND applications.status = 'shortlisted'";
$shortlisted_count = $conn->query($sql)->fetch_assoc()['shortlisted_count'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employer Dashboard | GoSeekr</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #0a66c2;
            --secondary-color: #f3f2ef;
            --dark-color: #191919;
            --light-color: #ffffff;
            --border-color: #e0e0e0;
            --success-color: #057642;
            --warning-color: #f5c400;
            --danger-color: #cc1016;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            background-color: var(--secondary-color);
            color: var(--dark-color);
        }
        
        .navbar {
            background-color: var(--light-color);
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 0;
            height: 52px;
        }
        
        .navbar-brand {
            font-weight: bold;
            font-size: 24px;
            color: var(--primary-color);
        }
        
        .nav-link {
            color: #666;
            padding: 0 12px;
            height: 52px;
            display: flex;
            align-items: center;
            border-bottom: 2px solid transparent;
        }
        
        .nav-link:hover, .nav-link.active {
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
        }
        
        .nav-icon {
            font-size: 1.4rem;
            display: block;
            text-align: center;
            margin-bottom: 2px;
        }
        
        .nav-text {
            font-size: 12px;
            white-space: nowrap;
        }
        
        .card {
            border-radius: 8px;
            border: 1px solid var(--border-color);
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            background-color: var(--light-color);
        }
        
        .profile-card {
            padding: 20px;
        }
        
        .profile-avatar {
            width: 72px;
            height: 72px;
            background-color: var(--primary-color);
            color: white;
            font-size: 32px;
            font-weight: bold;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .stats-card {
            border-top: none;
            padding: 12px 20px;
        }
        
        .stat-item {
            padding: 8px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .stat-item:last-child {
            border-bottom: none;
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .accordion-button:not(.collapsed) {
            background-color: rgba(10, 102, 194, 0.1);
            color: var(--primary-color);
        }
        
        .accordion-button:focus {
            box-shadow: none;
            border-color: rgba(10, 102, 194, 0.25);
        }
        
        .job-posting {
            border-bottom: 1px solid var(--border-color);
            padding: 16px;
        }
        
        .job-posting:last-child {
            border-bottom: none;
        }
        
        .badge-approved {
            background-color: var(--success-color);
        }
        
        .badge-pending {
            background-color: var(--warning-color);
        }
        
        .badge-rejected {
            background-color: var(--danger-color);
        }
        
        .job-actions {
            display: flex;
            gap: 8px;
        }
        
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .tab-content {
            padding: 20px 0;
        }
        
        .nav-tabs .nav-link {
            color: #666;
            border: none;
            padding: 10px 16px;
            font-weight: 500;
        }
        
        .nav-tabs .nav-link.active {
            color: var(--primary-color);
            border-bottom: 2px solid var(--primary-color);
            background-color: transparent;
        }
        
        .notification-item {
            padding: 12px 16px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .notification-item:last-child {
            border-bottom: none;
        }
        
        .notification-item.unread {
            background-color: rgba(10, 102, 194, 0.05);
        }
        
        .notification-time {
            font-size: 12px;
            color: #666;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }
        
        .candidate-item {
            padding: 12px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
        }
        
        .candidate-avatar {
            width: 48px;
            height: 48px;
            background-color: #e0e0e0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 12px;
        }
        
        .candidate-info {
            flex-grow: 1;
        }
        
        .footer {
            color: #666;
            font-size: 12px;
            padding: 20px 0;
            border-top: 1px solid var(--border-color);
            margin-top: 40px;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                GoSeekr
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <span class="nav-icon"><i class="bi bi-house-door"></i></span>
                            <span class="nav-text">Home</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="post_job.php">
                            <span class="nav-icon"><i class="bi bi-briefcase"></i></span>
                            <span class="nav-text">Post a Job</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="notifications.php">
                            <span class="nav-icon"><i class="bi bi-bell"></i></span>
                            <span class="nav-text">Notifications</span>
                            <?php if ($unread_count > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    <?php echo $unread_count; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <!-- <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <span class="nav-icon"><i class="bi bi-person-circle"></i></span>
                            <span class="nav-text">Me</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header"><?php //echo $user['full_name']; ?></h6></li>
                            <li><a class="dropdown-item" href="profile.php">View Profile</a></li>
                            <li><a class="dropdown-item" href="settings.php">Account Settings</a></li>
                            <li><hr class="dropdown-divider"></li> -->
                            <li><a class="dropdown-item" href="logout.php">Sign Out</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container my-4">
        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-3">
                <!-- Profile Card -->
                <div class="card profile-card">
                    <div class="text-center mb-3">
                        <div class="profile-avatar mx-auto">
                            <?php echo substr($user['full_name'], 0, 1); ?>
                        </div>
                        <h5 class="mt-3 mb-0"><?php echo $user['full_name']; ?></h5>
                        <p class="text-muted"><?php echo $user['email']; ?></p>
                    </div>
                    <div class="d-grid gap-2">
                        <!-- <a href="profile.php" class="btn btn-outline-primary">View Profile</a> -->
                        <a href="post_job.php" class="btn btn-primary">Post a New Job</a>
                    </div>
                </div>

                <!-- Dashboard Stats -->
                <div class="card stats-card">
                    <h5 class="card-title mb-3">Dashboard</h5>
                    
                    <div class="stat-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-number"><?php echo $active_count; ?></div>
                                <div class="text-muted">Active Jobs</div>
                            </div>
                            <i class="bi bi-briefcase text-primary fs-4"></i>
                        </div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-number"><?php echo $pending_count; ?></div>
                                <div class="text-muted">Pending Applications</div>
                            </div>
                            <i class="bi bi-file-earmark-person text-primary fs-4"></i>
                        </div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="stat-number"><?php echo $shortlisted_count; ?></div>
                                <div class="text-muted">Shortlisted</div>
                            </div>
                            <i class="bi bi-star text-primary fs-4"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Notifications -->
                <div class="card">
                    <div class="card-header bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Notifications</h5>
                            <a href="notifications.php" class="text-primary small">View all</a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php
                        // Fetch recent notifications for this employer
                        $sql = "SELECT * FROM notifications WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5";
                        $notif_result = $conn->query($sql);

                        if ($notif_result->num_rows > 0) {
                            while ($notif = $notif_result->fetch_assoc()) {
                                $time_ago = date("M d, H:i", strtotime($notif['created_at']));
                                $is_unread = $notif['status'] === 'unread' ? 'unread' : '';
                                
                                echo "<div class='notification-item {$is_unread}'>
                                        <p class='mb-1'>{$notif['message']}</p>
                                        <span class='notification-time'>{$time_ago}</span>
                                      </div>";
                            }
                        } else {
                            echo "<div class='notification-item'>
                                    <p class='mb-0 text-center'>No new notifications</p>
                                  </div>";
                        }
                        ?>
                    </div>
                </div>
            </div>
            
            <!-- Right Column - Main Content -->
            <div class="col-lg-9">
                <!-- Job Postings Section -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <div class="section-header">
                            <h5 class="mb-0">Your Job Postings</h5>
                            <a href="post_job.php" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-lg"></i> Post New Job
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <ul class="nav nav-tabs">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#all-jobs">All Jobs</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#active-jobs">Active</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#pending-jobs">Pending</a>
                            </li>
                        </ul>
                        
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="all-jobs">
                                <?php
                                // Fetch job postings for this employer
                                $sql = "SELECT * FROM job_postings WHERE employer_id = $user_id ORDER BY created_at DESC";
                                $result = $conn->query($sql);

                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        $job_id = $row['job_id'];
                                        $status = $row['status'];
                                        $quota = $row['quota'];
                                        $status_badge = ($status === 'approved') ? 'badge-approved' : (($status === 'pending') ? 'badge-pending' : 'badge-rejected');
                                        
                                        // Get application count
                                        $sql_count = "SELECT COUNT(*) as count FROM applications WHERE job_id = $job_id";
                                        $count_result = $conn->query($sql_count);
                                        $application_count = $count_result->fetch_assoc()['count'];
                                        
                                        echo "<div class='job-posting'>
                                                <div class='d-flex justify-content-between align-items-start mb-2'>
                                                    <h5 class='mb-0'>{$row['title']}</h5>
                                                    <span class='badge {$status_badge}'>{$status}</span>
                                                </div>
                                                <p class='mb-2'>{$row['description']}</p>
                                                <div class='d-flex justify-content-between align-items-center'>
                                                    <div>
                                                        <span class='me-3'><i class='bi bi-calendar'></i> " . date("M d, Y", strtotime($row['created_at'])) . "</span>
                                                        <span><i class='bi bi-people'></i> {$application_count} applications</span>
                                                    </div>
                                                    <div class='job-actions'>
                                                        <a href='view_job.php?job_id={$job_id}' class='btn btn-outline-primary btn-sm'>View</a>
                                                        <a href='edit_job.php?job_id={$job_id}' class='btn btn-outline-primary btn-sm'>Edit</a>
                                                    </div>
                                                </div>
                                              </div>";
                                    }
                                } else {
                                    echo "<div class='p-4 text-center'>
                                            <p>You haven't posted any jobs yet.</p>
                                            <a href='post_job.php' class='btn btn-primary'>Post Your First Job</a>
                                          </div>";
                                }
                                ?>
                            </div>
                            
                            <div class="tab-pane fade" id="active-jobs">
                                <?php
                                // Fetch active job postings
                                $sql = "SELECT * FROM job_postings WHERE employer_id = $user_id AND status = 'approved' ORDER BY created_at DESC";
                                $result = $conn->query($sql);

                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        $job_id = $row['job_id'];
                                        $quota = $row['quota'];
                                        
                                        // Get application count
                                        $sql_count = "SELECT COUNT(*) as count FROM applications WHERE job_id = $job_id";
                                        $count_result = $conn->query($sql_count);
                                        $application_count = $count_result->fetch_assoc()['count'];
                                        
                                        echo "<div class='job-posting'>
                                                <h5 class='mb-2'>{$row['title']}</h5>
                                                <p class='mb-2'>{$row['description']}</p>
                                                <div class='d-flex justify-content-between align-items-center'>
                                                    <div>
                                                        <span class='me-3'><i class='bi bi-calendar'></i> " . date("M d, Y", strtotime($row['created_at'])) . "</span>
                                                        <span><i class='bi bi-people'></i> {$application_count} applications</span>
                                                    </div>
                                                    <div class='job-actions'>
                                                        <a href='view_job.php?job_id={$job_id}' class='btn btn-outline-primary btn-sm'>View</a>
                                                        <a href='edit_job.php?job_id={$job_id}' class='btn btn-outline-primary btn-sm'>Edit</a>
                                                    </div>
                                                </div>
                                              </div>";
                                    }
                                } else {
                                    echo "<div class='p-4 text-center'>
                                            <p>You don't have any active job postings.</p>
                                          </div>";
                                }
                                ?>
                            </div>
                            
                            <div class="tab-pane fade" id="pending-jobs">
                                <?php
                                // Fetch pending job postings
                                $sql = "SELECT * FROM job_postings WHERE employer_id = $user_id AND status = 'pending' ORDER BY created_at DESC";
                                $result = $conn->query($sql);

                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        $job_id = $row['job_id'];
                                        
                                        echo "<div class='job-posting'>
                                                <div class='d-flex justify-content-between align-items-start mb-2'>
                                                    <h5 class='mb-0'>{$row['title']}</h5>
                                                    <span class='badge badge-pending'>pending</span>
                                                </div>
                                                <p class='mb-2'>{$row['description']}</p>
                                                <div class='d-flex justify-content-between align-items-center'>
                                                    <div>
                                                        <span class='me-3'><i class='bi bi-calendar'></i> " . date("M d, Y", strtotime($row['created_at'])) . "</span>
                                                        <span class='text-muted'><i class='bi bi-info-circle'></i> Awaiting approval</span>
                                                    </div>
                                                    <div class='job-actions'>
                                                        <a href='view_job.php?job_id={$job_id}' class='btn btn-outline-primary btn-sm'>View</a>
                                                        <a href='edit_job.php?job_id={$job_id}' class='btn btn-outline-primary btn-sm'>Edit</a>
                                                    </div>
                                                </div>
                                              </div>";
                                    }
                                } else {
                                    echo "<div class='p-4 text-center'>
                                            <p>You don't have any pending job postings.</p>
                                          </div>";
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Shortlisted Candidates Section -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <div class="section-header">
                            <h5 class="mb-0">Shortlisted Candidates</h5>
                            <a href="shortlisted.php" class="btn btn-outline-primary btn-sm">View All</a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php
                        // Fetch shortlisted candidates for this employer
                        $sql = "SELECT applications.*, users.full_name, users.email, job_postings.title as job_title 
                                FROM applications 
                                JOIN users ON applications.seeker_id = users.user_id 
                                JOIN job_postings ON applications.job_id = job_postings.job_id 
                                WHERE job_postings.employer_id = $user_id 
                                AND applications.status = 'shortlisted' 
                                ORDER BY applications.updated_at DESC 
                                LIMIT 5";
                        $shortlisted_result = $conn->query($sql);

                        if ($shortlisted_result->num_rows > 0) {
                            while ($candidate = $shortlisted_result->fetch_assoc()) {
                                echo "<div class='candidate-item'>
                                        <div class='candidate-avatar'>" . substr($candidate['full_name'], 0, 1) . "</div>
                                        <div class='candidate-info'>
                                            <h6 class='mb-1'>{$candidate['full_name']}</h6>
                                            <p class='mb-1 small text-muted'>{$candidate['email']}</p>
                                            <span class='badge bg-light text-dark'>{$candidate['job_title']}</span>
                                        </div>
                                        <a href='view_shortlisted_candidate.php?seeker_id={$candidate['seeker_id']}&job_id={$candidate['job_id']}' class='btn btn-outline-primary btn-sm'>View</a>
                                      </div>";
                            }
                        } else {
                            echo "<div class='p-4 text-center'>
                                    <p>No shortlisted candidates yet.</p>
                                  </div>";
                        }
                        ?>
                    </div>
                </div>
                
                <!-- Job Offers Section -->
                <div class="card">
                    <div class="card-header bg-white">
                        <div class="section-header">
                            <h5 class="mb-0">Job Offers</h5>
                            <a href="job_offers.php" class="btn btn-outline-primary btn-sm">View All</a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php
                        // Fetch job offers for this employer
                        $sql_offers = "SELECT job_offers.*, job_postings.title, users.full_name 
                                      FROM job_offers 
                                      JOIN job_postings ON job_offers.job_id = job_postings.job_id 
                                      JOIN users ON job_offers.seeker_id = users.user_id 
                                      WHERE job_offers.employer_id = $user_id 
                                      ORDER BY job_offers.created_at DESC
                                      LIMIT 5";
                        $offers_result = $conn->query($sql_offers);

                        if ($offers_result->num_rows > 0) {
                            while ($offer = $offers_result->fetch_assoc()) {
                                $offer_id = $offer['offer_id'];
                                $status = $offer['status'];
                                $status_badge = ($status === 'pending') ? 'badge-pending' : (($status === 'accepted') ? 'badge-approved' : 'badge-rejected');
                                
                                echo "<div class='job-posting'>
                                        <div class='d-flex justify-content-between align-items-start mb-2'>
                                            <h5 class='mb-0'>Offer to {$offer['full_name']}</h5>
                                            <span class='badge {$status_badge}'>{$status}</span>
                                        </div>
                                        <p class='mb-2'>
                                            <strong>Position:</strong> {$offer['title']}<br>
                                            <strong>Details:</strong> {$offer['offer_details']}
                                        </p>
                                        <div class='d-flex justify-content-between align-items-center'>
                                            <span><i class='bi bi-calendar'></i> " . date("M d, Y", strtotime($offer['created_at'])) . "</span>
                                            <a href='view_offer.php?offer_id={$offer_id}' class='btn btn-outline-primary btn-sm'>View Details</a>
                                        </div>
                                      </div>";
                            }
                        } else {
                            echo "<div class='p-4 text-center'>
                                    <p>You haven't made any job offers yet.</p>
                                  </div>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p>&copy; <?php echo date("Y"); ?> GoSeekr. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>