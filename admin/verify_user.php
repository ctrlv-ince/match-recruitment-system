<?php
session_start();
include '../db.php';

// Redirect if not logged in as an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id']) && isset($_GET['action'])) {
    $employer_id = $_GET['id'];
    $action = $_GET['action'];

    // Update employer verification status
    $status = ($action === 'verify') ? 'verified' : 'rejected';
    $sql = "UPDATE employers SET verification_status = '$status' WHERE employer_id = $employer_id";
    if ($conn->query($sql) === TRUE) {
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    header("Location: dashboard.php");
    exit();
}
?>