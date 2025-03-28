<?php
session_start();
include '../db.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Check if user ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid user ID";
    header("Location: users.php");
    exit();
}

$user_id = $_GET['id'];

// First, check if user exists
$check_sql = "SELECT * FROM users WHERE user_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("i", $user_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "User not found";
    header("Location: users.php");
    exit();
}

// Delete user
try {
    // Start transaction in case there are related records to delete
    $conn->begin_transaction();

    // First, delete any dependent records (adjust these based on your database schema)
    // Example: delete applications, interviews, etc. associated with this user
    $delete_dependents_sql = "DELETE FROM applications WHERE seeker_id = ?";
    $delete_dependents_stmt = $conn->prepare($delete_dependents_sql);
    $delete_dependents_stmt->bind_param("i", $user_id);
    $delete_dependents_stmt->execute();

    // Then delete the user
    $delete_sql = "DELETE FROM users WHERE user_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $user_id);
    $delete_stmt->execute();

    $conn->commit();
    
    $_SESSION['success'] = "User deleted successfully";
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = "Error deleting user: " . $e->getMessage();
}

header("Location: users.php");
exit();
?>