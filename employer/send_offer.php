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
    $salary = $_POST['salary'];

    // Fetch the application_id for the given seeker_id and job_id
    $sql = "SELECT application_id FROM applications WHERE seeker_id = ? AND job_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        header("Location: view_candidate_details.php?seeker_id=$seeker_id&job_id=$job_id&status=error&message=" . urlencode("Database error: " . $conn->error));
        exit();
    }

    $stmt->bind_param("ii", $seeker_id, $job_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $application = $result->fetch_assoc();

    if (!$application) {
        // No application found for the given seeker_id and job_id
        header("Location: view_candidate_details.php?seeker_id=$seeker_id&job_id=$job_id&status=error&message=" . urlencode("No application found for the candidate and job."));
        exit();
    }

    $application_id = $application['application_id'];

    // Update the employer_decision status in the applications table to "approved"
    $sql_update = "UPDATE applications SET employer_decision = 'approved' WHERE application_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    if (!$stmt_update) {
        header("Location: view_candidate_details.php?seeker_id=$seeker_id&job_id=$job_id&status=error&message=" . urlencode("Database error: " . $conn->error));
        exit();
    }

    $stmt_update->bind_param("i", $application_id);
    $stmt_update->execute();

    // Insert the job offer into the database
    $sql = "INSERT INTO job_offers (application_id, employer_id, seeker_id, job_id, offer_details, salary, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        header("Location: view_candidate_details.php?seeker_id=$seeker_id&job_id=$job_id&status=error&message=" . urlencode("Database error: " . $conn->error));
        exit();
    }

    $stmt->bind_param("iiiisd", $application_id, $employer_id, $seeker_id, $job_id, $offer_details, $salary);

    if ($stmt->execute()) {
        $offer_id = $stmt->insert_id; // Get the auto-generated offer ID

        // Fetch company name and job title for the notification message
        $sql_fetch_details = "
            SELECT e.company_name, jp.title 
            FROM job_postings jp
            JOIN employers e ON jp.employer_id = e.employer_id
            WHERE jp.job_id = ?
        ";
        $stmt_fetch = $conn->prepare($sql_fetch_details);
        if (!$stmt_fetch) {
            header("Location: view_candidate_details.php?seeker_id=$seeker_id&job_id=$job_id&status=error&message=" . urlencode("Database error: " . $conn->error));
            exit();
        }

        $stmt_fetch->bind_param("i", $job_id);
        $stmt_fetch->execute();
        $result_fetch = $stmt_fetch->get_result();
        $details = $result_fetch->fetch_assoc();

        if ($details) {
            $company_name = $details['company_name'];
            $job_title = $details['title'];

            // Notify the job seeker with company name and job title
            $message = "You have received a job offer from $company_name for the position: $job_title. Please respond.";
            $sql_notify = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
            $stmt_notify = $conn->prepare($sql_notify);
            if (!$stmt_notify) {
                header("Location: view_candidate_details.php?seeker_id=$seeker_id&job_id=$job_id&status=error&message=" . urlencode("Database error: " . $conn->error));
                exit();
            }

            $stmt_notify->bind_param("is", $seeker_id, $message);
            $stmt_notify->execute();
        } else {
            // If company name or job title cannot be fetched, use a generic message
            $message = "You have received a job offer. Please respond.";
            $sql_notify = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
            $stmt_notify = $conn->prepare($sql_notify);
            if (!$stmt_notify) {
                header("Location: view_candidate_details.php?seeker_id=$seeker_id&job_id=$job_id&status=error&message=" . urlencode("Database error: " . $conn->error));
                exit();
            }

            $stmt_notify->bind_param("is", $seeker_id, $message);
            $stmt_notify->execute();
        }

        // Redirect with success message
        header("Location: view_candidate_details.php?seeker_id=$seeker_id&job_id=$job_id&status=success&message=" . urlencode("Job offer sent successfully!"));
        exit();
    } else {
        // Redirect with error message
        header("Location: view_candidate_details.php?seeker_id=$seeker_id&job_id=$job_id&status=error&message=" . urlencode("Error sending job offer: " . $stmt->error));
        exit();
    }

    $stmt->close();
    $stmt_update->close();
    $stmt_fetch->close();
    $stmt_notify->close();
    $conn->close();
}
?>