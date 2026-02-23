<?php
include '../../config/db_connection.php';

// Generate CourseID
$query = "SELECT MAX(CAST(SUBSTRING(CourseID, 2) AS UNSIGNED)) AS MaxID FROM course";
$result = $conn->query($query);
$row = $result->fetch_assoc();
$newCourseID = 'C' . str_pad($row['MaxID'] + 1, 3, '0', STR_PAD_LEFT);

// Save Course Data
$courseTitle = $_POST['courseName'];
$courseDescription = $_POST['courseDescription'];

// Handle Course Image Upload
$courseImage = $_FILES['courseImage']['name'];
$courseImagePath = "images/" . basename($courseImage);
if (!move_uploaded_file($_FILES['courseImage']['tmp_name'], $courseImagePath)) {
    die("Failed to upload course image.");
}

// Insert Course into Database
$stmt = $conn->prepare("INSERT INTO course (CourseID, CourseTitle, CourseDescription, CourseImage) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $newCourseID, $courseTitle, $courseDescription, $courseImagePath);
$stmt->execute();

// Extract the numeric part of CourseID for ChapterID generation
$courseNumber = (int)substr($newCourseID, 1); // E.g., if CourseID is C003, $courseNumber = 3

// Save Chapters
foreach ($_POST['chapterTitle'] as $index => $title) {
    $chapterDescription = $_POST['chapterDescription'][$index];
    $chapterContent = $_POST['chapterContent'][$index];

    // Handle Chapter Video Upload
    $chapterVideo = $_FILES['chapterVideo']['name'][$index];
    $chapterVideoPath = "images/" . basename($chapterVideo);
    if (!move_uploaded_file($_FILES['chapterVideo']['tmp_name'][$index], $chapterVideoPath)) {
        die("Failed to upload video for chapter $title.");
    }

    // Handle Chapter Image Upload
    $chapterImage = $_FILES['chapterImage']['name'][$index];
    $chapterImagePath = "images/" . basename($chapterImage);
    if (!move_uploaded_file($_FILES['chapterImage']['tmp_name'][$index], $chapterImagePath)) {
        die("Failed to upload image for chapter $title.");
    }

    // Generate Chapter ID
    $chapterID = 'CH' . $courseNumber . str_pad($index + 1, 2, '0', STR_PAD_LEFT); // E.g., CH301, CH302
    $assessmentID = 'A' . substr($newChapterID, 2);
    // Insert Chapter into Database
    $chapterStmt = $conn->prepare("INSERT INTO coursechapter (CourseChapterID, CourseChapterTitle, CourseChapterDescription, CourseChapterContent, CourseChapterVideoUrl, CourseID, CourseChapterImage,assessmentID) VALUES (?, ?, ?, ?,?, ?, ?, ?)");
    $chapterStmt->bind_param("ssssssss", $chapterID, $title, $chapterDescription, $chapterContent, $chapterVideoPath, $newCourseID, $chapterImagePath,$assessmentID);
    $chapterStmt->execute();
}

// Close the connection
$stmt->close();
$conn->close();

// Redirect
echo "<script>
    alert('Course and chapters saved successfully!');
    window.location.href = 'coursemanagement.php';
</script>";
exit();
?>
