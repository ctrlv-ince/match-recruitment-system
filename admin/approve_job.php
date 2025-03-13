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

    header("Location: pending_jobs.php");
    exit();
} else {
    header("Location: pending_jobs.php");
    exit();
}
?>