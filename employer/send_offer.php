<?php
session_start();
include '../db.php';

// Redirect if not logged in as an employer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'employer') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $seeker_id = $_POST['seeker_id'];
    $job_id = $_POST['job_id'];
    $employer_id = $_SESSION['user_id'];
    $offer_details = $_POST['offer_details'];

    // Fetch the application_id for the given seeker_id and job_id
    $sql = "SELECT application_id FROM applications WHERE seeker_id = ? AND job_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        header("Location: view_candidate.php?seeker_id=$seeker_id&job_id=$job_id&status=error&message=" . urlencode("Database error: " . $conn->error));
        exit();
    }

    $stmt->bind_param("ii", $seeker_id, $job_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $application = $result->fetch_assoc();

    if (!$application) {
        // No application found for the given seeker_id and job_id
        header("Location: view_candidate.php?seeker_id=$seeker_id&job_id=$job_id&status=error&message=" . urlencode("No application found for the candidate and job."));
        exit();
    }

    $application_id = $application['application_id'];

    // Insert the job offer into the database
    $sql = "INSERT INTO job_offers (application_id, employer_id, seeker_id, job_id, offer_details, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        header("Location: view_candidate.php?seeker_id=$seeker_id&job_id=$job_id&status=error&message=" . urlencode("Database error: " . $conn->error));
        exit();
    }

    $stmt->bind_param("iiiis", $application_id, $employer_id, $seeker_id, $job_id, $offer_details);

    if ($stmt->execute()) {
        $offer_id = $stmt->insert_id; // Get the auto-generated offer ID

        // Notify the job seeker
        $message = "You have received a job offer (Offer ID: $offer_id). Please respond.";
        $sql = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
        $stmt2 = $conn->prepare($sql);
        if (!$stmt2) {
            header("Location: view_candidate.php?seeker_id=$seeker_id&job_id=$job_id&status=error&message=" . urlencode("Database error: " . $conn->error));
            exit();
        }

        $stmt2->bind_param("is", $seeker_id, $message);
        $stmt2->execute();

        // Redirect with success message
        header("Location: view_candidate.php?seeker_id=$seeker_id&job_id=$job_id&status=success&message=" . urlencode("Job offer sent successfully!"));
    } else {
        // Redirect with error message
        header("Location: view_candidate.php?seeker_id=$seeker_id&job_id=$job_id&status=error&message=" . urlencode("Error sending job offer: " . $stmt->error));
    }

    $stmt->close();
    $conn->close();
}
?>