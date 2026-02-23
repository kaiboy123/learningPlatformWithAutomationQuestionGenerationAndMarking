<?php
include '../../config/db_connection.php';

// Retrieve course and chapter ID from URL
if (isset($_GET['courseID']) && isset($_GET['chapterID'])) {
    $courseID = $_GET['courseID'];
    $chapterID = $_GET['chapterID'];
    
    // Fetch existing assessment data
    $query = "SELECT * FROM assessment ca, coursechapter cc WHERE cc.CourseChapterID = ? AND cc.AssessmentID = ca.AssessmentID";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $chapterID);
    $stmt->execute();
    $result = $stmt->get_result();
    $assessment = $result->fetch_assoc();
    
    if($assessment){
    $assessmentID = $assessment['AssessmentID'];

    $query = "SELECT * FROM assessmentquestion WHERE AssessmentID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $assessmentID);
        $stmt->execute();
        $result = $stmt->get_result();
        $question = $result->fetch_assoc();
    }
    // Fetch existing practical data
    $query = "SELECT * FROM practicaltest cp,coursechapter cc WHERE cc.CourseChapterID = ? AND cc.PracticalTestID = cp.PracticalTestID";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $chapterID);
    $stmt->execute();
    $result = $stmt->get_result();
    $practical = $result->fetch_assoc();
} else {
    echo "Course ID or Chapter ID not provided!";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Assessment and Practical</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: var(--white);
            border-radius: .5rem;
            box-shadow: 0 0 1rem rgba(0, 0, 0, 0.1);
            color: var(--black);
            font-size: 1.1rem;
        }
        h1 {
            font-size: 2.5rem;
            color: var(--black);
            margin-bottom: 1.5rem;
            text-align: center;
        }
        h2 {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: var(--black);
            border-bottom: 2px solid #f39c12;
            padding-bottom: .5rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            font-size: 1.2rem;
            margin-bottom: .5rem;
            color: var(--black);
        }
        .form-group input[type="text"], 
        .form-group textarea {
            width: 100%;
            padding: .8rem;
            font-size: 1.1rem;
            border: 1px solid #ddd;
            border-radius: .5rem;
            box-sizing: border-box;
        }
        .form-group input[type="file"] {
            font-size: 1.1rem;
        }
        .btn {
            padding: .75rem 1.5rem;
            font-size: 1.3rem;
            color: #fff;
            background-color: #f39c12;
            border: none;
            border-radius: .5rem;
            cursor: pointer;
            transition: background-color .3s;
        }
        .btn:hover {
            background-color: #e67e22;
        }
        .form-group p {
            font-size: 1rem;
            margin-top: .5rem;
        }
        .form-group a {
            color: #007bff;
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Edit Assessment and Practical</h1>
    <form action="saveAssessmentPractical.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="courseID" value="<?php echo $courseID; ?>">
        <input type="hidden" name="chapterID" value="<?php echo $chapterID; ?>">

        <!-- Assessment Section -->
        <h2>Assessment</h2>

<!-- <div class="form-group">
    <label for="assessmentFile">Upload Assessment File</label>
    <input type="file" id="assessmentFile" name="assessmentFile">
    <?php if (!empty($assessment['AssessmentFile'])): ?>
        <p>Current File: <a href="<?php echo $assessment['AssessmentFile']; ?>" target="_blank">View File</a></p>
    <?php endif; ?>
</div> -->
<div class="form-group">
    <label for="courseContent">Course Content</label>
    <textarea id="courseContent" name="courseContent" placeholder="Enter the course content here..." required></textarea>
</div>

<div class="form-group">
    <button type="button" id="generateQuestionButton" class="btn">Generate Question</button>
</div>

<!-- Display Generated Question -->
<div id="generatedQuestionSection" class="form-group" style="display: none;">
    <label for="generatedQuestion">Generated Question</label>
    <textarea id="generatedQuestion" name="generatedQuestion" readonly></textarea>
</div>

<!-- JavaScript for Question Generation -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const generateQuestionButton = document.getElementById('generateQuestionButton');
        const courseContentInput = document.getElementById('courseContent');
        const generatedQuestionSection = document.getElementById('generatedQuestionSection');
        const generatedQuestionInput = document.getElementById('generatedQuestion');

        // Event listener for Generate Question button
        generateQuestionButton.addEventListener('click', async () => {
            const courseContent = courseContentInput.value.trim();

            if (!courseContent) {
                alert("Please enter course content to generate a question.");
                return;
            }

            try {
                // Call the Flask API to generate the question
                const response = await fetch('/generate-question', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ content: courseContent }),
                });

                if (!response.ok) {
                    throw new Error("Failed to generate question. Please try again.");
                }

                const data = await response.json();
                const generatedQuestion = data.question || "No question generated.";

                // Display the generated question
                generatedQuestionInput.value = generatedQuestion;
                generatedQuestionSection.style.display = 'block';
            } catch (error) {
                alert(`Error: ${error.message}`);
            }
        });
    });
</script>
<!-- Question Format Selector -->
<div class="form-group">
    <label for="questionType">Question Type</label>
    <select id="questionType" name="questionType">
        <option value="multiple-choice">Multiple Choice</option>
        <option value="open-ended">Open-ended</option>
    </select>
</div>
<!-- Open-ended Question Section -->
<div id="openEndedSection" class="form-group question-type" style="display: none;">
    <label for="openEndedQuestion">Open-ended Question</label>
    <div class="form-group">
    <label for="assessmentTitle">Question Title</label>
    <input type="text" id="assessmentTitle" name="assessmentTitle" value="<?php echo $assessment['AssessmentTitle'] ?? ''; ?>" required>
</div>

    <textarea id="openEndedQuestion" name="openEndedQuestion" placeholder="Describe the question" required></textarea>
</div>
<!-- Multiple Choice Section -->
<div id="multipleChoiceSection" class="form-group question-type" style="display: none;">
    <label>Multiple Choice Questions</label>
    <div class="form-group">
    <label for="assessmentTitle">Question Title</label>
    <input type="text" id="assessmentTitle" name="assessmentTitle" value="<?php echo $assessment['AssessmentTitle'] ?? ''; ?>" required>
</div>

    <div id="multipleChoiceContainer">
        <!-- Options will be dynamically added here -->
        <div class="choice-option">
            <label>Option 1</label>
            <input type="text" name="options[]" placeholder="Enter Option 1" required>
            <button type="button" class="removeOptionButton btn">Remove</button>
        </div>
    </div>
    <button type="button" id="addOptionButton" class="btn">Add Option</button>
</div>

<!-- JavaScript to Add/Remove Options -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const questionTypeSelector = document.getElementById('questionType');
        const multipleChoiceSection = document.getElementById('multipleChoiceSection');
        const openEndedSection = document.getElementById('openEndedSection');
        const addOptionButton = document.getElementById('addOptionButton');
        const multipleChoiceContainer = document.getElementById('multipleChoiceContainer');

        // Show/Hide sections based on selected type
        questionTypeSelector.addEventListener('change', () => {
            const selectedType = questionTypeSelector.value;
            if (selectedType === 'multiple-choice') {
                multipleChoiceSection.style.display = 'block';
                openEndedSection.style.display = 'none';
            } else if (selectedType === 'open-ended') {
                multipleChoiceSection.style.display = 'none';
                openEndedSection.style.display = 'block';
            }
        });

        // Add new option for multiple-choice
        addOptionButton.addEventListener('click', () => {
            const optionCount = multipleChoiceContainer.querySelectorAll('.choice-option').length + 1;
            const newOption = document.createElement('div');
            newOption.classList.add('choice-option');
            newOption.innerHTML = `
                <label>Option ${optionCount}</label>
                <input type="text" name="options[]" placeholder="Enter Option ${optionCount}" required>
                <button type="button" class="removeOptionButton btn">Remove</button>
            `;
            multipleChoiceContainer.appendChild(newOption);

            // Attach remove event to the new button
            newOption.querySelector('.removeOptionButton').addEventListener('click', () => {
                newOption.remove();
                updateOptionLabels();
            });
        });

        // Update option labels after removal
        const updateOptionLabels = () => {
            const options = multipleChoiceContainer.querySelectorAll('.choice-option');
            options.forEach((option, index) => {
                const label = option.querySelector('label');
                label.textContent = `Option ${index + 1}`;
            });
        };

        // Remove option button functionality for existing options
        multipleChoiceContainer.addEventListener('click', (e) => {
            if (e.target.classList.contains('removeOptionButton')) {
                e.target.closest('.choice-option').remove();
                updateOptionLabels();
            }
        });

        // Default to open-ended on load
        questionTypeSelector.dispatchEvent(new Event('change'));
    });
</script>

        <!-- Practical Section -->
        <h2>Practical</h2>
        <div class="form-group">
            <label for="practicalTitle">Title</label>
            <input type="text" id="practicalTitle" name="practicalTitle" value="<?php echo $practical['PracticalTitle'] ?? ''; ?>" required>
        </div>
        <div class="form-group">
            <label for="practicalDescription">Description</label>
            <textarea id="practicalDescription" name="practicalDescription" required><?php echo $practical['PracticalDescription'] ?? ''; ?></textarea>
        </div>
        <div class="form-group">
            <label for="practicalFile">Upload Practical File</label>
            <input type="file" id="practicalFile" name="practicalFile">
            <?php if (!empty($practical['PracticalFile'])): ?>
                <p>Current File: <a href="<?php echo $practical['PracticalFile']; ?>" target="_blank">View File</a></p>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn">Save Changes</button>
    </form>
</div>

</body>
</html>