<?php
include '../../config/db_connection.php';
session_start();

// Check if the learner is logged in
$learnerID = $_SESSION['learner'] ?? null;
if (!$learnerID) {
    header("Location: login.php");
    exit();
}

// Get the CourseID from the URL
$courseID = $_GET['CourseID'] ?? null;
if (!$courseID) {
    echo "Invalid course.";
    exit();
}

// Fetch course details
$courseQuery = $conn->prepare("SELECT CourseTitle, CourseDescription FROM course WHERE CourseID = ?");
$courseQuery->bind_param("s", $courseID);
$courseQuery->execute();
$courseResult = $courseQuery->get_result();
$course = $courseResult->fetch_assoc();

if (!$course) {
    echo "Course not found.";
    exit();
}

// Path to the Python script
$pythonScript = '../ai/generatecert.py';

// Command to execute the Python script
$command = escapeshellcmd("python3 $pythonScript $learnerID $courseID");
$output = shell_exec($command);

// Check if the script ran successfully
if ($output) {
    echo "Certificate generated successfully.";
    
} else {
    echo "Failed to generate certificate.";
}
?>