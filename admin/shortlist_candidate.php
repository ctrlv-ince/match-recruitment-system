<?php
session_start();
include '../db.php';

// Redirect if not logged in as an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $application_id = $_POST['application_id'];

    // Update the application status to 'shortlisted'
    $sql = "UPDATE applications SET status = 'shortlisted' WHERE application_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $application_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Candidate shortlisted successfully!";
    } else {
        $_SESSION['error'] = "Error shortlisting candidate: " . $conn->error;
    }

    $stmt->close();
    $conn->close();

    // Redirect back to the dashboard
    header("Location: active_jobs.php");
    exit();
}
?>