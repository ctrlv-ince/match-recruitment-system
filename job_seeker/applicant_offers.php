<?php
session_start();
include '../db.php';

// Redirect if not logged in as a job seeker
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'job_seeker') {
    header("Location: login.php");
    exit();
}

$seeker_id = $_SESSION['user_id'];

// Fetch all job offers received by this applicant with verified fields
$sql = "SELECT 
            jo.offer_id, 
            jo.job_id, 
            jo.offer_details, 
            jo.status, 
            jo.salary, 
            jo.created_at,
            jp.title, 
            jp.description as job_description,
            e.company_name,
            u.full_name as employer_name,
            u.email as employer_email
        FROM job_offers jo
        JOIN job_postings jp ON jo.job_id = jp.job_id
        JOIN employers e ON jo.employer_id = e.employer_id
        JOIN users u ON e.employer_id = u.user_id
        WHERE jo.seeker_id = ?
        ORDER BY jo.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $seeker_id);
$stmt->execute();
$result = $stmt->get_result();
$offers = $result->fetch_all(MYSQLI_ASSOC);

// Fetch unread notifications count
$sql = "SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = $seeker_id AND status = 'unread'";
$unread_count = $conn->query($sql)->fetch_assoc()['unread_count'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Job Offers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0073b1;
            --secondary-color: #0a66c2;
            --accent-color: #f5f5f5;
            --text-muted: #666;
            --border-color: #e0e0e0;
        }

        body {
            background-color: #f3f2ef;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 30px;
        }

        h2 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .card-body {
            padding: 20px;
        }

        .profile-img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--primary-color);
            color: white;
            font-weight: bold;
            font-size: 24px;
        }

        .offer-details {
            background-color: var(--accent-color);
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }

        .offer-detail-item {
            margin-bottom: 10px;
            display: flex;
            align-items: flex-start;
        }

        .offer-detail-item i {
            margin-right: 10px;
            color: var(--primary-color);
            min-width: 20px;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-accepted {
            background-color: #d4edda;
            color: #155724;
        }

        .status-declined {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-expired {
            background-color: #e2e3e5;
            color: #383d41;
        }

        .btn-action {
            border-radius: 8px;
            padding: 8px 15px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: var(--text-muted);
        }

        .empty-state i {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .badge-expiring {
            background-color: #ffc107;
            color: #212529;
        }
    </style>
</head>

<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-briefcase"></i> GoSeekr
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="search_jobs.php">
                            <i class="fas fa-search"></i> Jobs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="view_interview.php">
                            <i class="fas fa-calendar-check"></i> Interviews
                        </a>
                    </li>
                    <li class="nav-item nav-notification">
                        <a class="nav-link position-relative" href="notifications.php">
                            <i class="fas fa-bell"></i> Notifications
                            <?php if ($unread_count > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    <?php echo $unread_count; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="update_profile.php">
                            <i class="fas fa-user-edit"></i> Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="view_applications.php">
                            <i class="fas fa-file-alt"></i> Applications
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2><i class="fas fa-handshake"></i> My Job Offers</h2>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $_SESSION['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <?php if (empty($offers)): ?>
            <div class="empty-state">
                <i class="fas fa-envelope-open-text"></i>
                <h3>No Job Offers Yet</h3>
                <p>You haven't received any job offers yet. When you do, they'll appear here.</p>
                <a href="search_jobs.php" class="btn btn-primary">
                    <i class="fas fa-search"></i> Search Jobs
                </a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($offers as $offer): 
                    $created_at = new DateTime($offer['created_at']);
                    $now = new DateTime();
                    $expires_at = $created_at->add(new DateInterval('P3D')); // 3 days expiration
                    $is_expiring = ($now->diff($expires_at)->days <= 1 && $offer['status'] == 'pending');
                    $employer_initials = strtoupper(substr($offer['employer_name'], 0, 1));
                ?>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-start mb-3">
                                    <div class="profile-img me-3">
                                        <?php echo $employer_initials; ?>
                                    </div>
                                    <div>
                                        <h5 class="mb-1"><?php echo htmlspecialchars($offer['title']); ?></h5>
                                        <p class="mb-1"><strong><?php echo htmlspecialchars($offer['company_name']); ?></strong></p>
                                        <p class="text-muted small mb-2">
                                            <i class="fas fa-clock"></i> Received: <?php echo date('M j, Y g:i a', strtotime($offer['created_at'])); ?>
                                        </p>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="status-badge status-<?php echo $offer['status']; ?>">
                                                <?php echo ucfirst($offer['status']); ?>
                                            </span>
                                            <?php if ($is_expiring && $offer['status'] == 'pending'): ?>
                                                <span class="badge badge-expiring">
                                                    <i class="fas fa-hourglass-end"></i> Expiring soon
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="offer-details">
                                    <div class="offer-detail-item">
                                        <i class="fas fa-file-alt"></i>
                                        <div>
                                            <strong>Job Description:</strong>
                                            <p><?php echo nl2br(htmlspecialchars(substr($offer['job_description'], 0, 150) . '...')); ?></p>
                                        </div>
                                    </div>
                                    
                                    <div class="offer-detail-item">
                                        <i class="fas fa-file-contract"></i>
                                        <div>
                                            <strong>Offer Details:</strong>
                                            <p><?php echo nl2br(htmlspecialchars($offer['offer_details'])); ?></p>
                                        </div>
                                    </div>
                                    
                                    <div class="offer-detail-item">
                                        <i class="fas fa-money-bill-wave"></i>
                                        <div>
                                            <strong>Salary:</strong> ₱<?php echo number_format($offer['salary'], 2); ?>
                                        </div>
                                    </div>
                                    
                                    <div class="offer-detail-item">
                                        <i class="fas fa-envelope"></i>
                                        <div>
                                            <strong>Employer Contact:</strong> <?php echo htmlspecialchars($offer['employer_email']); ?>
                                            <?php if (!empty($offer['employer_phone'])): ?>
                                                <br><i class="fas fa-phone"></i> <?php echo htmlspecialchars($offer['employer_phone']); ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between mt-3">
                                    <a href="respond_offer.php?offer_id=<?php echo $offer['offer_id']; ?>" 
                                       class="btn btn-primary btn-action">
                                        <i class="fas fa-eye"></i> View Offer
                                    </a>
                                    <a href="view_job.php?job_id=<?php echo $offer['job_id']; ?>" 
                                       class="btn btn-outline-secondary btn-action">
                                        <i class="fas fa-briefcase"></i> View Job
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>

<footer class="footer mt-auto py-3 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <p class="mb-0">&copy; 2025 GoSeekr. All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</html>