<?php
header('Content-Type: application/json');

// Allow CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$uploadDir = __DIR__ . '/uploads/';
$filePath = null;
$content = $_POST['content'] ?? null; // Get content from the form
$difficulty = $_POST['difficulty'] ?? 'normal';
$questionType = $_POST['questionType'] ?? 'open-ended';

// Ensure upload directory exists
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Handle uploaded file if present
if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
    $fileName = basename($_FILES['document']['name']);
    $filePath = $uploadDir . $fileName;

    if (!move_uploaded_file($_FILES['document']['tmp_name'], $filePath)) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to upload file.']);
        exit;
    }
}

// Determine whether to use content or file
if ($filePath) {
    $command = escapeshellcmd(
        "python3 automatic_qa_app.py --file " . escapeshellarg($filePath) .
        " --difficulty " . escapeshellarg($difficulty) .
        " --type " . escapeshellarg($questionType)
    );
} elseif ($content) {
    $command = escapeshellcmd(
        "python3 automatic_qa_app.py --content " . escapeshellarg($content) .
        " --difficulty " . escapeshellarg($difficulty) .
        " --type " . escapeshellarg($questionType)
    );
} else {
    http_response_code(400);
    echo json_encode(['error' => 'No content or file provided.']);
    exit;
}

// Execute Python script
$output = shell_exec($command . " 2>&1");
$decodedOutput = json_decode($output, true);

if (json_last_error() === JSON_ERROR_NONE) {
    echo json_encode($decodedOutput); // Return the JSON output
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to generate questions. Output: ' . $output]);
}
?>