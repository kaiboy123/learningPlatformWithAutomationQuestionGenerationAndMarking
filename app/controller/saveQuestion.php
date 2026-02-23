<?php
// Include the database connection
include '../../config/db_connection.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize the submitted data
    $assessmentID = $_POST['assessmentID'] ?? null;
    $courseID = $_POST['courseID'] ?? null;
    $chapterID = $_POST['chapterID'] ?? null;
    $questionTitle = $_POST['questionTitle'] ?? null;
    $questionType = $_POST['questionType'] ?? null;
    $questionContent = $_POST['questionContent'] ?? null;
    $questionOptions = $_POST['questionOptions'] ?? null;
    $questionAnswer = $_POST['questionAnswer'] ?? null;

    // Validate the input
    if (!$assessmentID || !$questionTitle || !$questionType || !$questionAnswer) {
        echo "<script>alert('Error: Required fields are missing.'); window.history.back();</script>";
        exit;
    }


    // Generate `AssessmentQuestionID`
    $query = "SELECT MAX(AssessmentQuestionID) AS lastID FROM assessmentquestion WHERE AssessmentID = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        echo "<script>alert('Error: Failed to prepare the query for generating AssessmentQuestionID.'); window.history.back();</script>";
        exit;
    }
    $stmt->bind_param("s", $assessmentID);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $lastID = $row['lastID'] ?? null;

    // Generate the new ID
    if ($lastID) {
        // Extract the numeric part and increment it
        $num = intval(substr($lastID, 2)) + 1;
        $assessmentQuestionID = "AQ" . str_pad($num, 3, "0", STR_PAD_LEFT);
    } else {
        // Start with AQ001 if no IDs exist
        $assessmentQuestionID = "AQ001";
    }

    // Prepare the query to insert the new question
    $query = "INSERT INTO assessmentquestion (AssessmentQuestionID, AssessmentID, QuestionTitle, QuestionType, QuestionContent, QuestionOptions, QuestionAnswer) 
              VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        echo "<script>alert('Error: Failed to prepare the query to save the question.'); window.history.back();</script>";
        exit;
    }

    // Bind the parameters
    $stmt->bind_param(
        "sssssss",
        $assessmentQuestionID,
        $assessmentID,
        $questionTitle,
        $questionType,
        $questionContent,
        $questionOptions,
        $questionAnswer
    );

    // Execute the query
    if ($stmt->execute()) {
        // Success: Show alert and redirect back to the previous page
        echo "<script>alert('Question saved successfully!');
         window.location.href = 'assessmentEdit.php?courseID=$courseID&chapterID=$chapterID';
         </script>";
    } else {
        // Failure: Show alert and redirect back
        echo "<script>alert('Error: Failed to save the question.'); window.history.back();</script>";
    }

    // Close the statement
    $stmt->close();
} else {
    // Handle invalid request methods
    echo "<script>alert('Error: Invalid request method.'); window.history.back();</script>";
}

// Close the database connection
$conn->close();
?>
