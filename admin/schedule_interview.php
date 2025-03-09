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
    $scheduled_date = $_POST['scheduled_date'];

    // Fetch the job location and job seeker ID
    $sql = "SELECT job_postings.location, applications.seeker_id 
            FROM applications 
            JOIN job_postings ON applications.job_id = job_postings.job_id 
            WHERE applications.application_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $application_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $location = $row['location'];
    $seeker_id = $row['seeker_id'];

    // Insert the interview schedule into the database
    $sql = "INSERT INTO interviews (application_id, scheduled_date) 
            VALUES (?, ?)";
    $stmt2 = $conn->prepare($sql);
    $stmt2->bind_param("is", $application_id, $scheduled_date);

    if ($stmt2->execute()) {
        // Notify the job seeker about the interview schedule
        $message = "Your interview has been scheduled on $scheduled_date at $location.";
        $sql = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
        $stmt3 = $conn->prepare($sql);
        $stmt3->bind_param("is", $seeker_id, $message);
        $stmt3->execute();

        $_SESSION['message'] = "Interview scheduled successfully!";
    } else {
        $_SESSION['error'] = "Error scheduling interview: " . $conn->error;
    }

    $stmt2->close();
    $conn->close();

    // Redirect back to the admin dashboard
    header("Location: dashboard.php");
    exit();
}
?>