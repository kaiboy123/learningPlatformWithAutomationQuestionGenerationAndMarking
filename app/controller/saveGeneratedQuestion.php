<?php
include '../../config/db_connection.php';

// Read JSON payload from the request body
$input = file_get_contents("php://input");
$data = json_decode($input, true);

// Log received payload for debugging
file_put_contents('log.txt', "Received Data: " . print_r($data, true), FILE_APPEND);

// Validate required fields
$assessmentID = $data['assessmentID'] ?? null;
$questionTitle = $data['questionTitle'] ?? null;
$questionContent = $data['questionContent'] ?? null;
$questionOptions = $data['questionOptions'] ?? null;
$questionAnswer = $data['questionAnswer'] ?? null;
$questionType = $data['questionType'] ?? 'open-ended'; // Default to open-ended

if (empty($assessmentID) || empty($questionTitle) || empty($questionAnswer) || empty($questionType)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
    file_put_contents('log.txt', "Validation Failed: Missing fields\n", FILE_APPEND);
    exit;
}

// Generate `AssessmentQuestionID`
$query = "SELECT MAX(AssessmentQuestionID) AS lastID FROM assessmentquestion WHERE AssessmentID = ?";
$stmt = $conn->prepare($query);
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

// For multiple-choice questions, ensure `questionOptions` is valid JSON
if ($questionType === 'multiple-choice') {
    $questionContent = null;
    if (empty($questionOptions)) {
        echo json_encode(['status' => 'error', 'message' => 'Options are required for multiple-choice questions.']);
        file_put_contents('log.txt', "Validation Failed: Missing options for multiple-choice\n", FILE_APPEND);
        exit;
    }

    // Ensure options are valid JSON
    $decodedOptions = json_decode($questionOptions, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($decodedOptions)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON for options.']);
        file_put_contents('log.txt', "Validation Failed: Invalid JSON for options\n", FILE_APPEND);
        exit;
    }

    // Re-encode for storage
    $questionOptions = json_encode($decodedOptions, JSON_UNESCAPED_UNICODE);
}

// Insert question into the database
$query = "INSERT INTO assessmentquestion 
    (AssessmentQuestionID, QuestionTitle, QuestionContent, QuestionOptions, QuestionAnswer, QuestionType, AssessmentID) 
    VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param(
    "sssssss",
    $assessmentQuestionID,
    $questionTitle,
    $questionContent,
    $questionOptions,
    $questionAnswer,
    $questionType,
    $assessmentID
);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Question added successfully.', 'newID' => $assessmentQuestionID]);
    file_put_contents('log.txt', "Insert Successful\n", FILE_APPEND);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to add question.']);
    file_put_contents('log.txt', "Insert Failed: " . $stmt->error . "\n", FILE_APPEND);
}

$stmt->close();
$conn->close();
?>
