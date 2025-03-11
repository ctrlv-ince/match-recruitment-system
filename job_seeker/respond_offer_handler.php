<?php
session_start();
include '../db.php';

// Redirect if not logged in as a job seeker
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'job_seeker') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $offer_id = $_POST['offer_id'];
    $response = $_POST['response']; // 'accepted' or 'declined'

    // Update the job offer status
    $sql = "UPDATE job_offers SET status = ? WHERE offer_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $response, $offer_id);

    if ($stmt->execute()) {
        // Notify the employer
        $sql = "SELECT employer_id FROM job_offers WHERE offer_id = ?";
        $stmt2 = $conn->prepare($sql);
        $stmt2->bind_param("i", $offer_id);
        $stmt2->execute();
        $result = $stmt2->get_result();
        $row = $result->fetch_assoc();

        $employer_id = $row['employer_id'];
        $message = "Your job offer has been $response by the candidate.";
        $sql = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
        $stmt3 = $conn->prepare($sql);
        $stmt3->bind_param("is", $employer_id, $message);
        $stmt3->execute();

        $_SESSION['message'] = "Response submitted successfully!";
    } else {
        $_SESSION['error'] = "Error submitting response: " . $conn->error;
    }

    $stmt->close();
    $conn->close();

    // Redirect back to the job seeker dashboard
    header("Location: dashboard.php");
    exit();
} else {
    header("Location: dashboard.php");
    exit();
}
?>