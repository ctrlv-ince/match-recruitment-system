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

    // Fetch the job offer details
    $sql = "SELECT job_offers.*, job_postings.quota 
            FROM job_offers 
            JOIN job_postings ON job_offers.job_id = job_postings.job_id 
            WHERE job_offers.offer_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $offer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $offer = $result->fetch_assoc();

    if (!$offer) {
        die("No offer found for ID: $offer_id");
    }

    $job_id = $offer['job_id'];
    $quota = $offer['quota'];

    // Update the job offer status
    $sql = "UPDATE job_offers SET status = ? WHERE offer_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $response, $offer_id);

    if ($stmt->execute()) {
        if ($response === 'accepted') {
            // Update the application status to 'hired'
            $sql = "UPDATE applications 
                    SET status = 'hired', employer_decision = 'approved' 
                    WHERE application_id = (
                        SELECT application_id FROM job_offers WHERE offer_id = ?
                    )";
            $stmt2 = $conn->prepare($sql);
            $stmt2->bind_param("i", $offer_id);
            $stmt2->execute();

            // Decrement the job quota
            $new_quota = $quota - 1;
            $sql = "UPDATE job_postings SET quota = ? WHERE job_id = ?";
            $stmt3 = $conn->prepare($sql);
            $stmt3->bind_param("ii", $new_quota, $job_id);
            $stmt3->execute();

            // If quota is met, mark the job as unavailable
            if ($new_quota <= 0) {
                $sql = "UPDATE job_postings SET status = 'closed' WHERE job_id = ?";
                $stmt4 = $conn->prepare($sql);
                $stmt4->bind_param("i", $job_id);
                $stmt4->execute();
            }
        }

        // Notify the employer
        $employer_id = $offer['employer_id'];
        $message = "Your job offer has been $response by the candidate.";
        $sql = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
        $stmt5 = $conn->prepare($sql);
        $stmt5->bind_param("is", $employer_id, $message);
        $stmt5->execute();

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