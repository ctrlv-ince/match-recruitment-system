<!-- Sidebar -->
<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="bi bi-house-door"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'active_jobs.php' ? 'active' : ''; ?>" href="active_jobs.php">
                    <i class="bi bi-briefcase"></i> Active Jobs
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'shortlisted.php' ? 'active' : ''; ?>" href="shortlisted.php">
                    <i class="bi bi-people"></i> Shortlisted Candidates
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'for_interview.php' ? 'active' : ''; ?>" href="for_interview.php">
                    <i class="bi bi-calendar-event"></i> Scheduled Interviews
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'pending_jobs.php' ? 'active' : ''; ?>" href="pending_jobs.php">
                    <i class="bi bi-hourglass-split"></i> Pending Jobs
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'rejected_jobs.php' ? 'active' : ''; ?>" href="rejected_jobs.php">
                    <i class="bi bi-x-circle"></i> Rejected Jobs
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>" href="users.php">
                    <i class="bi bi-person-lines-fill"></i> Users
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'job_seeker_verifications.php' ? 'active' : ''; ?>" href="job_seeker_verifications.php">
                    <i class="bi bi-file-earmark-check"></i> Job Seeker Verifications
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'employer_verifications.php' ? 'active' : ''; ?>" href="employer_verifications.php">
                    <i class="bi bi-building-check"></i> Employer Verifications
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'feedbacks.php' ? 'active' : ''; ?>" href="feedbacks.php">
                    <i class="bi bi-chat-left-text"></i> Feedback
                </a>
            </li>
        </ul>
    </div>
</nav>