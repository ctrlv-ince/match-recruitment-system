<?php
session_start();
include '../db.php';

// Redirect if not logged in as an employer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'employer') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['application_id']) && isset($_GET['decision'])) {
    $application_id = $_GET['application_id'];
    $decision = $_GET['decision'];

    // Update employer decision
    $sql = "UPDATE applications SET employer_decision = ? WHERE application_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $decision, $application_id);
    $stmt->execute();

    // Notify the candidate
    $message = ($decision === 'approved') ? "Congratulations! You have been approved for the job." : "We regret to inform you that your application has been rejected.";
    $sql = "INSERT INTO notifications (user_id, message) VALUES ((SELECT seeker_id FROM applications WHERE application_id = ?), ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $application_id, $message);
    $stmt->execute();

    header("Location: employer_dashboard.php");
    exit();
} else {
    header("Location: employer_dashboard.php");
    exit();
}
?>