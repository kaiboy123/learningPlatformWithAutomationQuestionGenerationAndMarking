<?php
include '../../config/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $questionID = $_POST['questionID'] ?? null;
    $courseID = $_POST['courseID'] ?? null;
    $chapterID = $_POST['chapterID'] ?? null;
    $questionTitle = $_POST['questionTitle'] ?? null;
    $questionContent = $_POST['questionContent'] ?? null;
    $questionAnswer = $_POST['questionAnswer'] ?? null;
    $learnerAnswer = $_POST['learnerAnswer'] ?? null;

    if (!$questionID || !$questionTitle || !$questionContent || !$questionAnswer) {
        die("Missing required fields.");
    }

    // Update the question in the database
    $query = "UPDATE assessmentquestion 
              SET QuestionTitle = ?, QuestionContent = ?, QuestionAnswer = ?, LearnerAnswer = ? 
              WHERE AssessmentQuestionID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssss", $questionTitle, $questionContent, $questionAnswer, $learnerAnswer, $questionID);

    if ($stmt->execute()) {
        header("Location: assessmentEdit.php?courseID=" . urlencode($courseID) . "&chapterID=" . urlencode($chapterID));
        exit;
    } else {
        echo "Failed to update the question.";
    }
} else {
    echo "Invalid request method.";
}
?>
