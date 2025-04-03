<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="../css/styles.css" rel="stylesheet">
</head>
<body>
    <?php
    // Get count of pending job approvals
    $pending_jobs_query = "SELECT COUNT(*) as count FROM job_postings WHERE status = 'pending'";
    $pending_result = $conn->query($pending_jobs_query);
    $pending_count = 0;
    if ($pending_result && $pending_result->num_rows > 0) {
        $pending_count = $pending_result->fetch_assoc()['count'];
    }
    
    // Get count of new job applications
    $new_applications_query = "SELECT COUNT(*) as count FROM applications WHERE status = 'applied'";
    $applications_result = $conn->query($new_applications_query);
    $applications_count = 0;
    if ($applications_result && $applications_result->num_rows > 0) {
        $applications_count = $applications_result->fetch_assoc()['count'];
    }
    ?>
    <!-- Top Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <!-- <img src="../images/logo.png" alt="Logo" width="120"> -->
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="active_jobs.php">
                            Jobs
                            <?php if ($pending_count > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?php echo $pending_count; ?>
                                <span class="visually-hidden">pending approvals</span>
                            </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="shortlisted.php">
                            Candidates
                            <?php if ($applications_count > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">
                                <?php echo $applications_count; ?>
                                <span class="visually-hidden">new applications</span>
                            </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="for_interview.php">Interviews</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">Users</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>