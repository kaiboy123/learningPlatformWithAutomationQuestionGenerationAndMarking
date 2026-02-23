<?php
include '../../config/db_connection.php';

$questionID = $_GET['questionID'] ?? null;
$assessmentID = $_GET['assessmentID'];
$courseID = $_GET['courseID'] ?? null;
$chapterID = $_GET['chapterID'] ?? null;

if (!$questionID || !$courseID || !$chapterID) {
    die("Missing required parameters.");
}

// Fetch the question details
$query = "SELECT * FROM assessmentquestion WHERE AssessmentQuestionID = ? AND AssessmentID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $questionID, $assessmentID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Question not found.");
}

$question = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Question</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 700px;
            margin: 50px auto;
            background: #fff;
            padding: 20px 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #007bff;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .form-group textarea {
            resize: vertical;
            height: 100px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .btn-back {
            background-color: #6c757d;
            margin-right: 10px;
        }
        .btn-back:hover {
            background-color: #5a6268;
        }
        .success-message {
            display: none;
            margin-top: 15px;
            padding: 10px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Edit Question</h1>
    <form id="editForm" action="saveEditedQuestion.php" method="post">
        <input type="hidden" name="questionID" value="<?php echo htmlspecialchars($question['AssessmentQuestionID']); ?>">
        <input type="hidden" name="courseID" value="<?php echo htmlspecialchars($courseID); ?>">
        <input type="hidden" name="chapterID" value="<?php echo htmlspecialchars($chapterID); ?>">

        <div class="form-group">
            <label for="questionTitle">Question Title</label>
            <input type="text" id="questionTitle" name="questionTitle" value="<?php echo htmlspecialchars($question['QuestionTitle']); ?>" required>
        </div>

        <div class="form-group">
    <label for="questionContent">
        <?php echo ($question['QuestionType'] === 'multiple-choice') ? 'Question Options (JSON Format)' : 'Question Content'; ?>
    </label>
    <?php if ($question['QuestionType'] === 'multiple-choice'): ?>
        <textarea id="questionContent" name="questionOptions" required><?php echo htmlspecialchars($question['QuestionOptions']); ?></textarea>
        <small style="color: #666;">Provide options in JSON format, e.g., {"A": "Option 1", "B": "Option 2"}</small>
    <?php else: ?>
        <textarea id="questionContent" name="questionContent" required><?php echo htmlspecialchars($question['QuestionContent']); ?></textarea>
    <?php endif; ?>
</div>

        <div class="form-group">
            <label for="questionAnswer">Correct Answer</label>
            <textarea id="questionAnswer" name="questionAnswer" required><?php echo htmlspecialchars($question['QuestionAnswer']); ?></textarea>
        </div>

        <div class="form-group">
            <label for="learnerAnswer">Learner Answer (Optional)</label>
            <textarea id="learnerAnswer" name="learnerAnswer"><?php echo htmlspecialchars($question['LearnerAnswer']); ?></textarea>
        </div>

        <button type="submit" class="btn">Save Changes</button>
        <a href="assessmentEdit.php?courseID=<?php echo urlencode($courseID); ?>&chapterID=<?php echo urlencode($chapterID); ?>" class="btn btn-back">Cancel</a>
    </form>
    <div class="success-message" id="successMessage">Your changes have been saved successfully! Will Redirect back to assessment edit page...</div>
</div>
<script>
    const editForm = document.getElementById('editForm');
    const successMessage = document.getElementById('successMessage');

    editForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        const formData = new FormData(editForm);

        try {
            const response = await fetch('saveEditedQuestion.php', {
                method: 'POST',
                body: formData
            });

            if (response.ok) {
                successMessage.style.display = 'block';
                setTimeout(() => {
                    window.location.href = `assessmentEdit.php?courseID=${encodeURIComponent('<?php echo $courseID; ?>')}&chapterID=${encodeURIComponent('<?php echo $chapterID; ?>')}`;
                }, 2000); // Redirect after 2 seconds
            } else {
                alert('Failed to save changes. Please try again.');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An unexpected error occurred.');
        }
    });
</script>
</body>
</html>
