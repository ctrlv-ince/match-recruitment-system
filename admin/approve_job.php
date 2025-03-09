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

    // Update job status to approved
    $sql = "UPDATE job_postings SET status = 'approved' WHERE job_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $job_id);
    $stmt->execute();

    // Log the action in admin_actions
    $action_type = 'approve_job';
    $description = "Admin $admin_id approved job posting $job_id.";
    
    $sql = "INSERT INTO admin_actions (admin_id, affected_job_id, action_type, description) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiss", $admin_id, $job_id, $action_type, $description);
    $stmt->execute();

    header("Location: dashboard.php");
    exit();
} else {
    header("Location: dashboard.php");
    exit();
}
?>