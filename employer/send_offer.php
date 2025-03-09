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

    // Insert the job offer into the database
    $sql = "INSERT INTO job_offers (employer_id, seeker_id, job_id, offer_details) 
            VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiis", $employer_id, $seeker_id, $job_id, $offer_details);

    if ($stmt->execute()) {
        $offer_id = $stmt->insert_id; // Get the auto-generated offer ID

        // Notify the job seeker
        $message = "You have received a job offer (Offer ID: $offer_id). Please respond.";
        $sql = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
        $stmt2 = $conn->prepare($sql);
        $stmt2->bind_param("is", $seeker_id, $message);
        $stmt2->execute();

        echo json_encode(["status" => "success", "message" => "Job offer sent successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error sending job offer: " . $conn->error]);
    }

    $stmt->close();
    $conn->close();
}
?>