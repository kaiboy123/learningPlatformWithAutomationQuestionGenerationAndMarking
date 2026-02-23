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

    $chapterQuery = "SELECT CourseChapterTitle, PracticalTestID FROM coursechapter WHERE CourseChapterID = ?";
    $stmt = $conn->prepare($chapterQuery);
    $stmt->bind_param("s", $chapterID);
    $stmt->execute();
    $chapterResult = $stmt->get_result();
    $chapter = $chapterResult->fetch_assoc();

    $practicalTestID = $chapter['PracticalTestID'];

    // Fetch existing practical test questions
    $practicalQuery = "SELECT * FROM practicaltest WHERE PracticalTestID = ?";
    $stmt = $conn->prepare($practicalQuery);
    $stmt->bind_param("s", $practicalTestID);
    $stmt->execute();
    $practicalResult = $stmt->get_result();
    $practicalQuestions = $practicalResult->fetch_all(MYSQLI_ASSOC);
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
    <title>Edit Practical</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Add styling similar to editAssessment.php */
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
        .delete-btn {
            padding: 5px 15px;
            width: 10%;
        }
    </style>
</head>
<body>

<div class="container">
    <a href="courseedit.php?id=<?php echo $courseID; ?>" class="btn">Back to Course Edit</a>
    <h1>Edit Practical</h1>
    <h2><?php echo htmlspecialchars($course['CourseTitle']); ?> - <?php echo htmlspecialchars($chapter['CourseChapterTitle']); ?></h2>

    <div>
        <h2 class="section-title">Practical Questions</h2>
        <?php if (!empty($practicalQuestions)): ?>
            <?php foreach ($practicalQuestions as $question): ?>
                <div class="question-card">
                    <div class="form-group">
                        <label>Question Title:</label>
                        <p><?php echo htmlspecialchars($question['PracticalQuestionTitle']); ?></p>
                    </div>
                    <div class="form-group">
                        <label>Question Content:</label>
                        <p><?php echo htmlspecialchars($question['PracticalQuestionContent']); ?></p>
                    </div>
                    <div class="form-group">
                        <label>Correct Answer:</label>
                        <p><?php echo htmlspecialchars($question['PracticalQuestionAnswer']); ?></p>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <form action="editPracticalQuestion.php?courseID=<?php echo urlencode($courseID); ?>&chapterID=<?php echo urlencode($chapterID); ?>&questionID=<?php echo urlencode($question['PracticalTestID']); ?>" method="post">
                            <button type="submit" class="btn">Edit</button>
                        </form>
                        <button type="button" class="btn delete-btn" onclick="confirmDelete('<?php echo htmlspecialchars($question['PracticalTestID']); ?>')">Delete</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-questions">No practical questions available.</p>
        <?php endif; ?>
    </div>

    <!-- Self Create Practical Test -->
    <<div class="question-card">
        <h2 class="section-title">Create New Practical Question</h2>
        <form action="savePracticalQuestion.php" method="post">
            <input type="hidden" name="courseID" value="<?php echo $courseID; ?>">
            <input type="hidden" name="chapterID" value="<?php echo $chapterID; ?>">

            <!-- Question Title -->
            <div class="form-group">
                <label for="practicalTitle">Question Title</label>
                <input type="text" id="practicalTitle" name="practicalTitle" placeholder="Enter the title of the question" required>
            </div>

            <!-- Question Content -->
            <div class="form-group">
                <label for="practicalDescription">Question Content</label>
                <textarea id="practicalDescription" name="practicalDescription" placeholder="Describe the task clearly." style="height:130px;" required></textarea>
                <button type="button" class="btn" onclick="insertTemplate()">Insert Template</button>
            </div>

            <!-- Correct Answer -->
            <div class="form-group">
                <label for="practicalAnswer">Correct Answer</label>
                <textarea id="practicalAnswer" name="practicalAnswer" placeholder="Provide the expected solution code." style="height:130px;" required></textarea>
            </div>

            <button type="submit" class="btn">Add Question</button>
        </form>
    </div>
</div>

<script>
    function insertTemplate() {
        const template = 
                         `1. Create a file named "index.html".\n` +
                         `2. Include the following in the file:\n` +
                         `   - A title: "My First Webpage".\n` +
                         `   - A heading: "Welcome to HTML".\n` +
                         `   - A paragraph describing the purpose of the page.`;
        document.getElementById('practicalDescription').value = template;
    }
</script>
</div>

<script>
function confirmDelete(questionID) {
    if (confirm('Are you sure you want to delete this question?')) {
        const courseID = '<?php echo $courseID; ?>';
        const chapterID = '<?php echo $chapterID; ?>';
        window.location.href = `deletePracticalQuestion.php?questionID=${questionID}&courseID=${courseID}&chapterID=${chapterID}`;
    }
}
</script>

</body>
</html>
