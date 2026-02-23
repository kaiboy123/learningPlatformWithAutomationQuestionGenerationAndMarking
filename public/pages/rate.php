<?php
// Include database connection
include '../../config/db_connection.php';

// Simulate session for learner ID
session_start();
if (!isset($_SESSION['learner'])) {
    header("Location: login.php");
    exit();
}

$learnerID = $_SESSION['learner'];
$courseID = $_GET['CourseID'] ?? null;

if (!$courseID) {
    echo "<script>alert('Course ID is missing.'); history.back();</script>";
    exit();
}

// Check if the learner has already rated the course
$checkQuery = "SELECT COUNT(*) AS has_rated FROM course_feedback WHERE CourseID = ? AND LearnerID = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("ss", $courseID, $learnerID);
$stmt->execute();
$result = $stmt->get_result();
$ratingData = $result->fetch_assoc();
$hasRated = $ratingData['has_rated'] > 0;

// If the record exists, show alert and redirect back
if ($hasRated) {
    echo "<script>alert('You have already rated this course. Thank you for your feedback!'); history.back();</script>";
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = $_POST['Rating'];
    $comment = $_POST['Comment'];

    // Insert the feedback into the database
    $insertQuery = "INSERT INTO course_feedback (CourseID, LearnerID, Rating, Comment, FeedbackDate)
                    VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("ssis", $courseID, $learnerID, $rating, $comment);

    if ($stmt->execute()) {
        echo "<script>alert('Thank you for your feedback!'); window.location.href = 'playlist.php?CourseID=" . htmlspecialchars($courseID) . "';</script>";
        exit();
    } else {
        echo "<script>alert('Error submitting your feedback. Please try again.'); history.back();</script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rate Course</title>
    <style>
        /* Same CSS as provided */
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background-color: #f9f9f9;
            font-family: Arial, sans-serif;
        }

        .rate-form {
            width: 60%;
            max-width: 700px;
            padding: 30px;
            background: #fff;
            border: 1px solid #ccc;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .rate-form h1 {
            font-size: 2.5rem;
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        .rate-form label {
            font-size: 1.2rem;
            display: block;
            margin: 10px 0 5px;
            color: #555;
        }

        .rate-form select,
        .rate-form textarea,
        .rate-form button {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            font-size: 1.1rem;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .rate-form button {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .rate-form button:hover {
            background-color: #0056b3;
        }

        .go-back {
            display: block;
            margin: 10px 0;
            text-align: center;
        }

        .go-back button {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .go-back button:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="rate-form">
        <h1>Rate This Course</h1>
        <form method="POST">
            <label for="rating">Your Rating (1-5):</label>
            <select name="Rating" id="rating" required>
                <option value="" disabled selected>Select Rating</option>
                <option value="1">1 - Poor</option>
                <option value="2">2 - Fair</option>
                <option value="3">3 - Good</option>
                <option value="4">4 - Very Good</option>
                <option value="5">5 - Excellent</option>
            </select>

            <label for="comment">Your Feedback:</label>
            <textarea name="Comment" id="comment" rows="5" placeholder="Write your feedback..." required></textarea>

            <button type="submit">Submit Rating</button>
        </form>
        <div class="go-back">
            <button onclick="history.back()">Go Back</button>
        </div>
    </div>
</body>
</html>
