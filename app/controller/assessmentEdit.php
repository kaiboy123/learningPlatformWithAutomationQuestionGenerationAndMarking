<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Assessment</title>
    <link rel="stylesheet" href="../../public/pages/css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 900px;
            margin: 40px auto;
            background: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        h1, h2 {
            text-align: center;
            color: #333;
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
            margin-top: 10px;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .back-btn {
            margin-bottom: 20px;
            background-color: #6c757d;
        }
        .back-btn:hover {
            background-color: #5a6268;
        }
        .question-card {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .form-group textarea {
            resize: vertical;
        }
        .question-title {
            font-size: 18px;
            font-weight: bold;
            color: #444;
        }
        .question-content {
            margin: 10px 0;
            font-size: 16px;
            color: #555;
        }
        .section-title {
            font-size: 22px;
            margin-top: 30px;
            text-align: center;
            color: #007bff;
            border-bottom: 2px solid #007bff;
            padding-bottom: 5px;
            display: inline-block;
        }
        .no-questions {
            text-align: center;
            font-style: italic;
            color: #666;
            margin: 20px 0;
        }
         /* General button styles */
   
    /* Default gray background for unselected buttons */
    .toggle-btn {
        background-color: #e0e0e0;
        color: #333;
        transition: background-color 0.3s ease, color 0.3s ease;
        padding: 10px 15px;
        font-size: 16px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        text-align: center;
        width: 20%; /* Smaller width */
    }

    /* Blue background for selected buttons */
    .toggle-btn.active {
        background-color: #007bff;
        color: #fff;
        padding: 10px 15px;
        font-size: 16px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        text-align: center;
        width: 20%; /* Smaller width */
    }

    /* Hover effect */
    .toggle-btn:hover {
        background-color: #0056b3;
        color: #fff;
    }

    /* Alignment for the container */
    div[style*="display: flex"] {
        margin: 20px 0;
        width: 100%;
    }
    .btn.loading {
    background-color: #6c757d;
    cursor: not-allowed;
}
.btn.loading::after {
    content: ' Loading...';
    animation: loading-dots 1s infinite;
}

@keyframes loading-dots {
    0% { content: ' Loading'; }
    33% { content: ' Loading.'; }
    66% { content: ' Loading..'; }
    100% { content: ' Loading...'; }
}
.clear-upload {
    position: absolute;
    top: 50%;
    right: 10px;
    transform: translateY(-50%);
    font-size: 18px;
    color: #999;
    cursor: pointer;
    display: none; /* Hidden by default */
}

.clear-upload:hover {
    color: #ff0000; /* Change color on hover */
}
.btn-small {
    padding: 5px 10px; /* Smaller padding */
    font-size: 14px; /* Smaller font size */
    border-radius: 4px; /* Smaller border radius */
    display: inline-block;
}

.btn.edit-btn {
    background-color: #28a745; /* Green for Edit */
    color: #fff;
}

.btn.edit-btn:hover {
    background-color: #218838;
}

.btn.delete-btn {
    background-color: #dc3545; /* Red for Delete */
    color: #fff;
}

.btn.delete-btn:hover {
    background-color: #c82333;
}

/* Ensure buttons align on the left */
.question-card div:first-child {
    display: flex;
    align-items: center;
    gap: 5px;
    margin-bottom: 10px;
}
    </style>
</head>
<body>
<?php
include '../../config/db_connection.php';

// Retrieve course and chapter ID from URL
if (isset($_GET['courseID']) && isset($_GET['chapterID'])) {
    $courseID = $_GET['courseID'];
    $chapterID = $_GET['chapterID'];

    // Fetch course and chapter details
    $courseQuery = "SELECT CourseTitle FROM course WHERE CourseID = ?";
    $stmt = $conn->prepare($courseQuery);
    $stmt->bind_param("s", $courseID);
    $stmt->execute();
    $courseResult = $stmt->get_result();
    $course = $courseResult->fetch_assoc();

    $chapterQuery = "SELECT CourseChapterTitle, AssessmentID FROM coursechapter WHERE CourseChapterID = ?";
    $stmt = $conn->prepare($chapterQuery);
    $stmt->bind_param("s", $chapterID);
    $stmt->execute();
    $chapterResult = $stmt->get_result();
    $chapter = $chapterResult->fetch_assoc();

    $assessmentID = $chapter['AssessmentID'];
      
    // Fetch existing questions for the assessment
    $questionsQuery = "SELECT * FROM assessmentquestion WHERE AssessmentID = ?";
    $stmt = $conn->prepare($questionsQuery);
    $stmt->bind_param("s", $assessmentID);
    $stmt->execute();
    $questionsResult = $stmt->get_result();
    $questions = $questionsResult->fetch_all(MYSQLI_ASSOC);

} else {
    echo "Course ID or Chapter ID not provided!";
    exit;
}
?>

<div class="container">
    <!-- Backward Button -->
    <a href="courseedit.php?id=<?php echo $courseID; ?>" class="btn back-btn">Back to Course Edit</a>

    <!-- Display Course and Chapter Info -->
    <h1>Edit Assessment</h1>
    <h2><?php echo htmlspecialchars($course['CourseTitle']); ?> - <?php echo htmlspecialchars($chapter['CourseChapterTitle']); ?></h2>

    <!-- Display Existing Questions -->
    <div>
        <h2 class="section-title">Existing Questions</h2>
        <?php if (!empty($questions)): ?>
            <?php foreach ($questions as $question): ?>
                <div class="question-card">
                    <div class="question-title"><?php echo htmlspecialchars($question['QuestionTitle']); ?></div>
                    <?php if (!empty($question['QuestionOptions'])): ?>
                        <div class="question-content">
                            <strong>Options:</strong>
                            <?php
                            $options = json_decode($question['QuestionOptions'], true);
                            if (is_array($options)) {
                                foreach ($options as $key => $value) {
                                    echo '<p>' . htmlspecialchars($key) . '. ' . htmlspecialchars($value) . '</p>';
                                }
                            } else {
                                echo '<p>No options available</p>';
                            }
                            ?>
                        </div>
                    <?php else: ?>
                        <div class="question-content">
                            <strong>Content:</strong>
                            <?php echo htmlspecialchars($question['QuestionContent']); ?>
                        </div>
                    <?php endif; ?>
                    <div class="question-content">
                        <strong>Correct Answer:</strong>
                        <?php echo htmlspecialchars($question['QuestionAnswer']); ?>
                    </div>
                    <div style="display: flex; justify-content: flex-start; gap: 10px;">
                        <form action="editQuestion.php?courseID=<?php echo urlencode($courseID); ?>&chapterID=<?php echo urlencode($chapterID); ?>&questionID=<?php echo urlencode($question['AssessmentQuestionID']); ?>&assessmentID=<?php echo urlencode($assessmentID);?>"  method="post" style="display: inline-block;">
                            <input type="hidden" name="questionID" value="<?php echo htmlspecialchars($question['AssessmentQuestionID']); ?>">
                            <button type="submit" style="flex: 0.1;" class="btn edit-btn">Edit</button>
                        </form>
                        <button type="button" class="btn delete-btn" style="flex: 0.1;" onclick="confirmDelete('<?php echo htmlspecialchars($question['AssessmentQuestionID']); ?>', '<?php echo htmlspecialchars($courseID); ?>', '<?php echo htmlspecialchars($chapterID); ?>')">Delete</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-questions">No questions created yet for this assessment.</p>
        <?php endif; ?>
    </div>

<!-- Toggle Buttons -->
<div style="display: flex; justify-content: space-between; align-items: center;">
    <button type="button" class="btn toggle-btn active" id="selfCreateBtn">Self Create</button>
    <button type="button" class="btn toggle-btn" id="autoGenerateBtn">Auto Generate</button>
</div>

<!-- Self Create Section -->
<div class="question-section active" id="selfCreateSection">
    <h2 class="section-title">Create New Question</h2>
    <form action="saveQuestion.php" method="post">
        <input type="hidden" name="assessmentID" value="<?php echo htmlspecialchars($assessmentID); ?>">
        <input type="hidden" name="courseID" value="<?php echo htmlspecialchars($courseID); ?>">
        <input type="hidden" name="chapterID" value="<?php echo htmlspecialchars($chapterID); ?>">

        <div class="form-group">
            <label for="newQuestionTitle">Question Title</label>
            <input type="text" id="newQuestionTitle" name="questionTitle" placeholder="Enter question title" required>
        </div>

        <div class="form-group">
            <label for="newQuestionType">Question Type</label>
            <select id="newQuestionType" name="questionType" required>
                <option value="open-ended">Open-Ended</option>
                <option value="multiple-choice">Multiple Choice</option>
            </select>
        </div>

        <!-- Open-Ended Fields -->
        <div id="openEndedFields">
            <div class="form-group">
                <label for="newQuestionContent">Question Content</label>
                <textarea id="newQuestionContent" name="questionContent" placeholder="Enter question content" style="height:120px;"></textarea>
            </div>
        </div>

        <!-- Multiple-Choice Fields -->
        <div id="multipleChoiceFields" style="display: none;">
    <h3>Question Options</h3>
    <div id="optionsContainer">
        <div class="form-group">
            <label for="optionA">Option A</label>
            <input type="text" id="optionA" placeholder="Enter option for A" >
        </div>
        <div class="form-group">
            <label for="optionB">Option B</label>
            <input type="text" id="optionB" placeholder="Enter option for B" >
        </div>
        <div class="form-group">
            <label for="optionC">Option C</label>
            <input type="text" id="optionC" placeholder="Enter option for C" >
        </div>
        <div class="form-group">
            <label for="optionD">Option D</label>
            <input type="text" id="optionD" placeholder="Enter option for D" >
        </div>
    </div>
    <!-- Hidden input to store the final JSON -->
    <input type="hidden" id="questionOptions" name="questionOptions">
</div>

<script>
    // Function to update the hidden input with JSON
    function updateQuestionOptions() {
        // Get values from the input fields
        const options = {
            A: document.getElementById('optionA').value.trim(),
            B: document.getElementById('optionB').value.trim(),
            C: document.getElementById('optionC').value.trim(),
            D: document.getElementById('optionD').value.trim(),
        };

        // Convert to JSON string
        const optionsJSON = JSON.stringify(options);

        // Set the JSON to the hidden input field
        document.getElementById('questionOptions').value = optionsJSON;

        // For debugging: Log the generated JSON
        console.log('Generated Question Options:', optionsJSON);
    }

    // Attach event listeners to each option input field
    document.getElementById('optionA').addEventListener('input', updateQuestionOptions);
    document.getElementById('optionB').addEventListener('input', updateQuestionOptions);
    document.getElementById('optionC').addEventListener('input', updateQuestionOptions);
    document.getElementById('optionD').addEventListener('input', updateQuestionOptions);
</script>

        <div class="form-group">
            <label for="newQuestionAnswer">Correct Answer</label>
            <textarea id="newQuestionAnswer" name="questionAnswer" placeholder="Enter correct answer" required></textarea>
        </div>

        <button type="submit" class="btn">Add Question</button>
    </form>
</div>
<!-- Auto Generate Section -->
<div class="question-section" id="autoGenerateSection" style="display: none;">
        <h2 class="section-title">Auto Generate Questions</h2>
        <form id="autoGenerateForm" enctype="multipart/form-data">
    <input type="hidden" name="assessmentID" value="<?php echo htmlspecialchars($assessmentID); ?>">

    <div class="form-group">
        <label for="difficultyLevel">Select Difficulty Level</label>
        <select id="difficultyLevel" name="difficultyLevel" required>
            <option value="easy">Easy</option>
            <option value="normal">Normal</option>
            <option value="hard">Hard</option>
        </select>
    </div>
    <div class="form-group">
        <label for="questionType">Select Question Type</label>
        <select id="questionType" name="questionType" required>
            <option value="open-ended">Open-Ended</option>
            <option value="multiple-choice">Multiple Choice</option>
        </select>
    </div>
    <div class="form-group" style="display: none;">
    <label for="autoContent">Enter Content for Auto-Generation</label>
    <textarea id="autoContent" name="autoContent" placeholder="Enter content for generating questions" rows="6"></textarea>
</div>
    <div class="form-group" style="position: relative;">
        <label for="uploadFile">Upload PDF File </label>
        <input type="file" id="uploadFile" name="document" accept="application/pdf" style="padding-right: 30px;">
        <span class="clear-upload" id="clearUploadBtn" title="Clear File">&times;</span>
    </div>
    <button type="button" class="btn" id="generateQuestionsBtn">Generate Questions</button>
</form>

        <!-- Hidden fields to store generated question details -->
        <div id="generatedFields" style="display: none; margin-top: 20px;">
            <h3>Generated Question Details</h3>
            <div class="form-group">
                <label for="generatedQuestionTitle">Question:</label>
                <input type="text" id="generatedQuestionTitle" name="generatedQuestionTitle" readonly>
            </div>
            <div class="form-group">
                <label for="generatedQuestionContent">Content:</label>
                <textarea id="generatedQuestionContent" name="generatedQuestionContent" readonly></textarea>
            </div>
            <div class="form-group">
                <label for="generatedCorrectAnswer">Answer:</label>
                <textarea id="generatedCorrectAnswer" name="generatedCorrectAnswer" readonly></textarea>
            </div>
            <button type="button" class="btn" id="addGeneratedQuestionBtn">Add Question</button>
        </div>
    </div>
</div>
<script>
    
function confirmDelete(questionID, courseID, chapterID) {
    if (confirm('Are you sure you want to delete this question?')) {
        // Redirect to delete script with the question ID, courseID, and chapterID
        window.location.href = `deleteQuestion.php?questionID=${questionID}&courseID=${courseID}&chapterID=${chapterID}`;
    }
}
document.getElementById('newQuestionType').addEventListener('change', function () {
    const questionType = this.value;
    const openEndedFields = document.getElementById('openEndedFields');
    const multipleChoiceFields = document.getElementById('multipleChoiceFields');
    const correctAnswerField = document.getElementById('newQuestionAnswer');

    if (questionType === 'open-ended') {
        // Show Open-Ended fields and hide Multiple-Choice fields
        openEndedFields.style.display = 'block';
        multipleChoiceFields.style.display = 'none';

        // Update placeholder for Open-Ended
        correctAnswerField.placeholder = 'Enter the correct answer (text)';
    } else if (questionType === 'multiple-choice') {
        // Show Multiple-Choice fields and hide Open-Ended fields
        openEndedFields.style.display = 'none';
        multipleChoiceFields.style.display = 'block';

        // Update placeholder for Multiple-Choice
        correctAnswerField.placeholder = 'Enter the correct answer (e.g., A, B, C, or D)';
    }
});

// Set the initial placeholder based on the default question type
document.addEventListener('DOMContentLoaded', function () {
    const questionType = document.getElementById('newQuestionType').value;
    const correctAnswerField = document.getElementById('newQuestionAnswer');

    if (questionType === 'open-ended') {
        correctAnswerField.placeholder = 'Enter the correct answer (text)';
    } else if (questionType === 'multiple-choice') {
        correctAnswerField.placeholder = 'Enter the correct answer (e.g., A, B, C, or D)';
    }
});
</script>
<script>
  const uploadFileInput = document.getElementById('uploadFile');
const clearUploadBtn = document.getElementById('clearUploadBtn');
const generateQuestionsBtn = document.getElementById('generateQuestionsBtn');

// Show "x" icon when a file is selected
uploadFileInput.addEventListener('change', () => {
    if (uploadFileInput.value) {
        clearUploadBtn.style.display = 'inline';
    }
});

// Clear file input and hide "x" icon
clearUploadBtn.addEventListener('click', () => {
    uploadFileInput.value = '';
    clearUploadBtn.style.display = 'none';
    alert('File upload cleared.');
});

// Handle Generate Questions button click
document.getElementById('generateQuestionsBtn').addEventListener('click', async function () {
    const button = this;
    button.classList.add('loading');
    button.disabled = true;

    const formData = new FormData();
    const content = document.getElementById('autoContent').value.trim();
    const fileInput = document.getElementById('uploadFile').files[0];

    // Append content or file to the form data
    if (content) {
        formData.append('content', content);
    } else if (fileInput) {
        formData.append('document', fileInput);
    } else {
        alert('Please enter content or upload a PDF file.');
        button.classList.remove('loading');
        button.disabled = false;
        return;
    }

    const difficultyLevel = document.getElementById('difficultyLevel').value;
    const questionType = document.getElementById('questionType').value;

    formData.append('difficulty', difficultyLevel);
    formData.append('questionType', questionType);

    try {
        const response = await fetch('app/ai/process_generate_questions.php', {
            method: 'POST',
            body: formData,
        });

        if (response.ok) {
            const result = await response.json();

            if (result.options) {
                // Multiple-choice question
                document.getElementById('generatedQuestionTitle').value = result.question;
                document.getElementById('generatedQuestionContent').value = JSON.stringify(result.options, null, 2);
                document.getElementById('generatedCorrectAnswer').value = result.answer;
            } else {
                // Open-ended question
                document.getElementById('generatedQuestionTitle').value = result.question;
                document.getElementById('generatedQuestionContent').value = result.content;
                document.getElementById('generatedCorrectAnswer').value = result.answer;
            }

            document.getElementById('generatedFields').style.display = 'block';
        } else {
            alert('Failed to generate questions. Please try again.');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An unexpected error occurred.');
    } finally {
        button.classList.remove('loading');
        button.disabled = false;
    }
});

document.getElementById('addGeneratedQuestionBtn').addEventListener('click', async function () {
    const button = this;
    button.disabled = true;

    const assessmentID = document.querySelector('input[name="assessmentID"]').value;
    const questionTitle = document.getElementById('generatedQuestionTitle').value.trim();
    const questionContent = document.getElementById('generatedQuestionContent').value.trim();
    const questionAnswer = document.getElementById('generatedCorrectAnswer').value.trim();
    const questionType = document.getElementById('questionType').value;
    const questionOptions = questionType === 'multiple-choice' ? questionContent : null;

    if (!assessmentID || !questionTitle || !questionAnswer) {
        alert('Missing required fields!');
        button.disabled = false;
        return;
    }

    const payload = {
        assessmentID,
        questionTitle,
        questionContent,
        questionOptions,
        questionAnswer,
        questionType,
    };

    try {
        const response = await fetch('app/controller/saveGeneratedQuestion.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });

        const result = await response.json();
        if (result.status === 'success') {
            window.location.reload();
            alert(result.message);
            document.getElementById('generatedFields').style.display = 'none';
        } else {
            alert(result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An unexpected error occurred.');
    } finally {
        button.disabled = false;
    }
});


</script>
<script>
    // Toggle Button Behavior
// Toggle Button Behavior
const selfCreateBtn = document.getElementById('selfCreateBtn');
const autoGenerateBtn = document.getElementById('autoGenerateBtn');
const selfCreateSection = document.getElementById('selfCreateSection');
const autoGenerateSection = document.getElementById('autoGenerateSection');

selfCreateBtn.addEventListener('click', () => {
    // Add active class to Self Create, remove from Auto Generate
    selfCreateBtn.classList.add('active');
    autoGenerateBtn.classList.remove('active');

    // Show Self Create section, hide Auto Generate
    selfCreateSection.style.display = 'block';
    autoGenerateSection.style.display = 'none';
});

autoGenerateBtn.addEventListener('click', () => {
    // Add active class to Auto Generate, remove from Self Create
    autoGenerateBtn.classList.add('active');
    selfCreateBtn.classList.remove('active');

    // Show Auto Generate section, hide Self Create
    autoGenerateSection.style.display = 'block';
    selfCreateSection.style.display = 'none';
});


</script>
</body>
</html>
