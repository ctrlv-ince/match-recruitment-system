<?php
session_start();
include '../db.php';

// Redirect if not logged in as an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $job_id = $_GET['id'];
    $admin_id = $_SESSION['user_id'];

    // Fetch job posting details
    $sql = "SELECT * FROM job_postings WHERE job_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $job_id);
    $stmt->execute();
    $job = $stmt->get_result()->fetch_assoc();

    if ($job && $job['status'] === 'approved') {
        // Update job status to inactive
        $sql = "UPDATE job_postings SET status = 'inactive' WHERE job_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $job_id);
        $stmt->execute();

        // Log the action in admin_actions
        $action_type = 'deactivate_job';
        $description = "Admin $admin_id deactivated job posting $job_id.";
        
        $sql = "INSERT INTO admin_actions (admin_id, affected_job_id, action_type, description) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiss", $admin_id, $job_id, $action_type, $description);
        $stmt->execute();

        header("Location: dashboard.php");
        exit();
    } else {
        echo "Error: Job posting not found or not active.";
    }
} else {
    header("Location: dashboard.php");
    exit();
}
?>