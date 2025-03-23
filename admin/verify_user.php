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
        // Get user email
        $user_query = "SELECT email, full_name FROM users WHERE user_id = $user_id";
        $user_result = $conn->query($user_query);
        
        if ($user_result->num_rows > 0) {
            $user_data = $user_result->fetch_assoc();
            $user_email = $user_data['email'];
            $user_name = $user_data['full_name'];
            
            // Update user status
            $status = ($action === 'verify') ? 'verified' : 'rejected';
            $sql = "UPDATE users SET status = '$status' WHERE user_id = $user_id";
            
            if ($conn->query($sql) === TRUE) {
                // Send email notification
                $mail_subject = ($action === 'verify') ? 
                    "Your Account Has Been Verified" : 
                    "Your Account Application Status";
                
                $mail_body = ($action === 'verify') ? 
                    "Dear $user_name,\n\nCongratulations! Your account has been verified. You can now access all features of our platform.\n\nThank you for joining us!\n\nBest regards,\nThe Recruitment System Team" : 
                    "Dear $user_name,\n\nWe regret to inform you that your account application has been rejected. If you believe this is an error, please contact our support team for assistance.\n\nBest regards,\nThe Recruitment System Team";
                
                // Configure email headers
                $to = $user_email;
                $headers = "From: vinceerolborja@gmail.com\r\n";
                $headers .= "Reply-To: vinceerolborja@gmail.com\r\n";
                $headers .= "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
                
                // Send the email using the configured sendmail
                mail($to, $mail_subject, $mail_body, $headers);
                
                // Additional actions for rejection
                if ($action === 'reject') {
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
            echo "User not found.";
        }
    } else {
        echo "Invalid action.";
    }
} else {
    header("Location: job_seeker_verifications.php");
    exit();
}
?>