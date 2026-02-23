<?php
include 'db_connection.php'; // Include database connection
session_start();

// Retrieve the query parameters
$course_id = $_GET['CourseID'] ?? null;
$learnerID = isset($_SESSION['learner']) ? $_SESSION['learner'] : null;

if (!$course_id || !$learnerID) {
    echo "Invalid request.";
    exit();
}

// Check if achievement already exists for this learner and course
$checkQuery = "
    SELECT a.AchievementID 
    FROM achievements a
    JOIN achievement_type at ON a.AchievementTypeID = at.AchievementTypeID
    WHERE a.LearnerID = ? AND at.CourseID = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("ss", $learnerID, $course_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<script>
            alert('Achievement already exists for this course.');
            history.back();
          </script>";
    exit();
}

// Get the Achievement Type for the Course
$achievementTypeQuery = "SELECT AchievementTypeID FROM achievement_type WHERE CourseID = ?";
$stmt = $conn->prepare($achievementTypeQuery);
$stmt->bind_param("s", $course_id);
$stmt->execute();
$typeResult = $stmt->get_result();
$type = $typeResult->fetch_assoc();

if (!$type) {
    echo "No achievement type defined for this course.";
    exit();
}

$achievementTypeId = $type['AchievementTypeID'];
$achievementDate = date("Y-m-d");

// Insert the achievement into the database
$insertQuery = "INSERT INTO achievements (LearnerID, AchievementTypeID, AchievementDate) VALUES (?, ?, ?)";
$stmt = $conn->prepare($insertQuery);
$stmt->bind_param("sis", $learnerID, $achievementTypeId, $achievementDate);

if ($stmt->execute()) {
    echo "Achievement successfully recorded!";
    // Redirect to a success page or achievements page
    header("Location: achievement.php?success=true");
} else {
    echo "Error saving achievement. Please try again.";
}
?>
