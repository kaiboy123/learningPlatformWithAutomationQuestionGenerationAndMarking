<?php
include '../../config/db_connection.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

// Check if the course ID is set in the URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $courseID = $_GET['id'];

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Delete all chapters related to the course
        $stmt = $conn->prepare("DELETE FROM coursechapter WHERE CourseID = ?");
        $stmt->bind_param("s", $courseID);
        $stmt->execute();

        // Delete the course
        $stmt = $conn->prepare("DELETE FROM course WHERE CourseID = ?");
        $stmt->bind_param("s", $courseID);
        $stmt->execute();

        // Commit the transaction
        $conn->commit();

        // Redirect to course management page with success message
        header("Location: coursemanagement.php?message=Course and related chapters deleted successfully");
        exit();
    } catch (Exception $e) {
        // Rollback the transaction on error
        $conn->rollback();

        // Redirect with an error message
        header("Location: coursemanagement.php?error=Failed to delete the course and its chapters");
        exit();
    }
} else {
    // Redirect to course management page if no ID is provided
    header("Location: coursemanagement.php?error=No course ID provided");
    exit();
}
?>
