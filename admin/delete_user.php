<?php
session_start();
include '../db.php';

// Redirect if not logged in as an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    $admin_id = $_SESSION['user_id'];

    // Fetch user details
    $sql = "SELECT * FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && $user['status'] === 'rejected') {
        // Log the action in admin_actions before deleting the user
        $action_type = 'delete_user';
        $description = "Admin $admin_id deleted rejected user $user_id.";
        
        $sql = "INSERT INTO admin_actions (admin_id, affected_user_id, action_type, description) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiss", $admin_id, $user_id, $action_type, $description);
        $stmt->execute();

        // Delete related records based on user type
        if ($user['user_type'] === 'employer') {
            $sql = "DELETE FROM employers WHERE employer_id = ?";
        } elseif ($user['user_type'] === 'job_seeker') {
            $sql = "DELETE FROM job_seekers WHERE seeker_id = ?";
        }
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        // Delete the user
        $sql = "DELETE FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        header("Location: dashboard.php");
        exit();
    } else {
        echo "Error: User not found or not rejected.";
    }
} else {
    header("Location: dashboard.php");
    exit();
}
?>