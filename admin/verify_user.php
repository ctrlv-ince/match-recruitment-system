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
                
                // Create HTML email content
                $mail_body = '
                <!DOCTYPE html>
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; }
                        .container { 
                            background-color: #f9f9f9;
                            padding: 20px;
                            margin: 20px 0;
                        }
                        .status {
                            color: #ffffff;
                            padding: 5px 10px;
                            border-radius: 4px;
                            display: inline-block;
                        }
                        .status-verified { background-color: #28a745; }
                        .status-rejected { background-color: #ff4444; }
                    </style>
                </head>
                <body>
                    <h2>Account Status Update</h2>
                    <p>Dear ' . $user_name . ',</p>';
                
                if ($action === 'verify') {
                    $mail_body .= '
                    <p>Congratulations! Your account has been <span class="status status-verified">Verified</span>.</p>
                    <div class="container">
                        <p>You can now access all features of our platform.</p>
                        <p>Thank you for joining us!</p>
                    </div>';
                } else {
                    $mail_body .= '
                    <p>We regret to inform you that your account application has been <span class="status status-rejected">Rejected</span>.</p>
                    <div class="container">
                        <p>If you believe this is an error, please contact our support team for assistance.</p>
                    </div>';
                }
                
                $mail_body .= '
                    <p>If you have any questions, please contact us:</p>
                    <p>Email: vinceerolborja@gmail.com</p>
                        
                    <p>Best regards,<br>The Recruitment System Team</p>
                </body>
                </html>';
                
                // Configure email headers for HTML content
                $to = $user_email;
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= 'From: Recruitment System <vinceerolborja@gmail.com>' . "\r\n";
                
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