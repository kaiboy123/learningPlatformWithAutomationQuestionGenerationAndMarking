<!DOCTYPE html>
<html lang="en">
<?php
include '../../config/db_connection.php';

// Check if the required parameters are provided
if (isset($_GET['courseID'], $_GET['chapterID'], $_GET['questionID'])) {
    $courseID = $_GET['courseID'];
    $chapterID = $_GET['chapterID'];
    $questionID = $_GET['questionID'];

    // Fetch practical question details
    $query = "SELECT * FROM practicaltest WHERE PracticalTestID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $questionID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $question = $result->fetch_assoc();
    } else {
        echo "<script>
            alert('Practical question not found.');
            window.history.back();
        </script>";
        exit;
    }
} else {
    echo "<script>
        alert('Required parameters not provided.');
        window.history.back();
    </script>";
    exit;
}
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Practical Question</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* General styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 50px auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            font-size: 2.5rem;
            color: #007bff;
            text-align: center;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            font-size: 1.2rem;
            color: #333;
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px 15px;
            font-size: 1rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .form-group textarea {
            resize: vertical;
            height: 120px;
        }

        .btn {
            display: inline-block;
            padding: 12px 20px;
            font-size: 1.2rem;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
                margin: 20px;
            }

            h1 {
                font-size: 2rem;
            }

            .form-group input,
            .form-group textarea {
                font-size: 0.9rem;
            }

            .btn {
                font-size: 1rem;
                padding: 10px 15px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Edit Practical Question</h1>
    <form action="updatePracticalQuestion.php" method="post">
        <input type="hidden" name="courseID" value="<?php echo htmlspecialchars($courseID); ?>">
        <input type="hidden" name="chapterID" value="<?php echo htmlspecialchars($chapterID); ?>">
        <input type="hidden" name="questionID" value="<?php echo htmlspecialchars($questionID); ?>">

        <div class="form-group">
            <label for="practicalTitle">Question Title</label>
            <input type="text" id="practicalTitle" name="practicalTitle" value="<?php echo htmlspecialchars($question['PracticalQuestionTitle']); ?>" required>
        </div>
        <div class="form-group">
            <label for="practicalContent">Question Content</label>
            <textarea id="practicalContent" name="practicalContent" required><?php echo htmlspecialchars($question['PracticalQuestionContent']); ?></textarea>
        </div>
        <div class="form-group">
            <label for="practicalAnswer">Correct Answer</label>
            <textarea id="practicalAnswer" name="practicalAnswer" required><?php echo htmlspecialchars($question['PracticalQuestionAnswer']); ?></textarea>
        </div>

        <div class="form-group">
            <button type="submit" class="btn">Save Changes</button>
            <!-- Return Button -->
            <a href="editPractical.php?courseID=<?php echo urlencode($courseID); ?>&chapterID=<?php echo urlencode($chapterID); ?>" class="btn" style="background-color: #6c757d; margin-left: 10px;">Return</a>
        </div>
    </form>
</div>
</body>

</html>
