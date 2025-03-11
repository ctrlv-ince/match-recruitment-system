<?php
session_start();
include '../db.php';

// Redirect if not logged in as an admin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $interview_id = $_POST['interview_id'];
    $status = $_POST['status'];
    $notes = $_POST['notes'];
    $recommendation = $_POST['recommendation'];

    // Update the interview in the database
    $sql = "UPDATE interviews 
            SET status = ?, notes = ?, recommendation = ? 
            WHERE interview_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $status, $notes, $recommendation, $interview_id);

    if ($stmt->execute()) {
        // Notify the employer if the candidate is recommended
        if ($recommendation === 'recommended') {
            // Fetch the application details
            $sql = "SELECT applications.job_id, applications.seeker_id 
                    FROM applications 
                    JOIN interviews ON applications.application_id = interviews.application_id 
                    WHERE interviews.interview_id = ?";
            $stmt2 = $conn->prepare($sql);
            $stmt2->bind_param("i", $interview_id);
            $stmt2->execute();
            $result = $stmt2->get_result();
            $row = $result->fetch_assoc();

            $job_id = $row['job_id'];
            $seeker_id = $row['seeker_id'];

            // Fetch the employer ID
            $sql = "SELECT employer_id FROM job_postings WHERE job_id = ?";
            $stmt3 = $conn->prepare($sql);
            $stmt3->bind_param("i", $job_id);
            $stmt3->execute();
            $result = $stmt3->get_result();
            $row = $result->fetch_assoc();

            $employer_id = $row['employer_id'];

            // Notify the employer and include the interview_id in the notification
            $message = "A candidate has been recommended for your job posting. Please review and decide whether to send a job offer.";
            $sql = "INSERT INTO notifications (user_id, message, interview_id) VALUES (?, ?, ?)";
            $stmt4 = $conn->prepare($sql);
            $stmt4->bind_param("isi", $employer_id, $message, $interview_id);
            $stmt4->execute();
        }

        $_SESSION['message'] = "Interview updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating interview: " . $conn->error;
    }

    $stmt->close();
    $conn->close();

    // Redirect back to the dashboard
    header("Location: dashboard.php");
    exit();
} else {
    header("Location: dashboard.php");
    exit();
}
?>