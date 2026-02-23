<?php
include '../../config/db_connection.php';
$chapterID = $_POST['chapter_id'] ?? '';
$assessmentID = $_POST['assessmentID'] ?? '';
$learnerID = $_POST['learnerID'] ?? '';

if ($assessmentID && $learnerID) {
    $completedAssessmentID = uniqid('CA'); // Generate a unique ID
    $completionDate = date('Y-m-d'); // Get the current date

    // Insert into the `completedassessment` table
    $query = "INSERT INTO completedassessment (CompletedAssessmentID, CompletedLearner, AssessmentID, CompletionDate) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $completedAssessmentID, $learnerID, $assessmentID, $completionDate);

    if ($stmt->execute()) {
        // Redirect to a success page or home page
        header("Location: ../../public/pages/htmlChapter1.php?chapter_id=$chapterID");
    } else {
        // Handle insertion failure
        echo "Failed to mark assessment as completed. Please try again.";
    }
} else {
    echo "Invalid request. Missing assessment or learner information.";
}
?>