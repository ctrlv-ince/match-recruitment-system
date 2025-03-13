<?php
session_start();
include '../db.php';

// Redirect if not logged in as an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id']) && isset($_GET['action'])) {
    $user_id = $_GET['id'];
    $action = $_GET['action'];

    // Validate action
    if ($action === 'verify' || $action === 'reject') {
        // Update user status
        $status = ($action === 'verify') ? 'verified' : 'rejected';
        $sql = "UPDATE users SET status = '$status' WHERE user_id = $user_id";
        if ($conn->query($sql) === TRUE) {
            // Additional actions for rejection
            if ($action === 'reject') {
                // Example: Delete related records (optional)
                // $conn->query("DELETE FROM job_seekers WHERE seeker_id = $user_id");
                // $conn->query("DELETE FROM employers WHERE employer_id = $user_id");

                // Example: Send a notification to the user (optional)
                $message = "Your account has been rejected. Please contact support for more details.";
                $conn->query("INSERT INTO notifications (user_id, message) VALUES ($user_id, '$message')");
            }

            // Redirect back to the dashboard
            header("Location: job_seeker_verifications.php");
            exit();
        } else {
            echo "Error: " . $conn->error;
        }
    } else {
        echo "Invalid action.";
    }
} else {
    header("Location: job_seeker_verifications.php");
    exit();
}
?>