<?php
include '../../config/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update course details
    $courseID = $_POST['courseID'];
    $courseTitle = $_POST['courseName'];
    $courseDescription = $_POST['courseDescription'];

    // Handle course image upload
    if (!empty($_FILES['courseImage']['name'])) {
        $courseImage = "images/" . basename($_FILES['courseImage']['name']);
        if (!move_uploaded_file($_FILES['courseImage']['tmp_name'], $courseImage)) {
            die("Failed to upload course image.");
        }

        // Update course with new image
        $stmt = $conn->prepare("UPDATE course SET CourseTitle = ?, CourseDescription = ?, CourseImage = ? WHERE CourseID = ?");
        $stmt->bind_param("ssss", $courseTitle, $courseDescription, $courseImage, $courseID);
    } else {
        // Update course without changing image
        $stmt = $conn->prepare("UPDATE course SET CourseTitle = ?, CourseDescription = ? WHERE CourseID = ?");
        $stmt->bind_param("sss", $courseTitle, $courseDescription, $courseID);
    }
    $stmt->execute();

    // Handle deleted chapters
    if (!empty($_POST['deleted_chapters'])) {
        $deletedChapters = explode(',', $_POST['deleted_chapters']); // Get chapter IDs
        foreach ($deletedChapters as $chapterID) {
            if (!empty($chapterID)) { // Ensure the chapter ID is not empty
                $stmt = $conn->prepare("DELETE FROM coursechapter WHERE CourseChapterID = ?");
                if (!$stmt) {
                    die("Prepare failed: " . $conn->error); // Debugging info
                }
                $stmt->bind_param("s", $chapterID); // Bind the chapter ID
                if (!$stmt->execute()) {
                    die("Execution failed: " . $stmt->error); // Debugging info
                }
                $stmt->close();
            }
        }
    }

    // Handle chapters
    if (isset($_POST['chapters']) && is_array($_POST['chapters'])) {
        // Extract numeric part of CourseID for ChapterID generation
        $courseNumber = (int)substr($courseID, 1); // E.g., C003 -> 3

        foreach ($_POST['chapters'] as $index => $chapter) {
            $chapterTitle = $chapter['title'];
            $chapterDescription = $chapter['description'];
            $chapterContent = $chapter['content'];
            $chapterID = $chapter['id'];
            $chapterVideoPath = null;
            $chapterImagePath = null;

            // Handle video upload
            if (isset($_FILES['chapters']['name'][$index]['video']) && $_FILES['chapters']['error'][$index]['video'] === 0) {
                $videoName = basename($_FILES['chapters']['name'][$index]['video']);
                $chapterVideoPath = "images/" . $videoName;
                if (!move_uploaded_file($_FILES['chapters']['tmp_name'][$index]['video'], $chapterVideoPath)) {
                    die("Failed to upload video for chapter: $chapterTitle");
                }
            }

            // Handle image upload
            if (isset($_FILES['chapters']['name'][$index]['image']) && $_FILES['chapters']['error'][$index]['image'] === 0) {
                $imageName = basename($_FILES['chapters']['name'][$index]['image']);
                $chapterImagePath = "images/" . $imageName;
                if (!move_uploaded_file($_FILES['chapters']['tmp_name'][$index]['image'], $chapterImagePath)) {
                    die("Failed to upload image for chapter: $chapterTitle");
                }
            }

            if (!empty($chapterID)) {
                // Update existing chapter
                if ($chapterImagePath || $chapterVideoPath) {
                    $updateChapterQuery = "UPDATE coursechapter SET CourseChapterTitle = ?, CourseChapterDescription = ?, CourseChapterContent = ?, CourseChapterImage = ?, CourseChapterVideoUrl = ? WHERE CourseChapterID = ?";
                    $chapterStmt = $conn->prepare($updateChapterQuery);
                    $chapterStmt->bind_param("ssssss", $chapterTitle, $chapterDescription, $chapterContent, $chapterImagePath, $chapterVideoPath, $chapterID);
                } else {
                    $updateChapterQuery = "UPDATE coursechapter SET CourseChapterTitle = ?, CourseChapterDescription = ?, CourseChapterContent = ? WHERE CourseChapterID = ?";
                    $chapterStmt = $conn->prepare($updateChapterQuery);
                    $chapterStmt->bind_param("ssss", $chapterTitle, $chapterDescription, $chapterContent, $chapterID);
                }
                $chapterStmt->execute();
            }
             else {
                // Insert new chapter
                $newChapterID = 'CH' . $courseNumber . str_pad($index + 1, 2, '0', STR_PAD_LEFT); // Generate ChapterID (e.g., CH301, CH302)
                $assessmentID = 'A' . substr($newChapterID, 2);

                $insertChapterQuery = "INSERT INTO coursechapter (CourseChapterID, CourseChapterTitle, CourseChapterDescription, CourseChapterContent, CourseChapterImage, CourseChapterVideoUrl, CourseID,AssessmentID) VALUES (?, ?, ?, ?, ?, ?, ?,?)";
                $insertChapterStmt = $conn->prepare($insertChapterQuery);
                $insertChapterStmt->bind_param("ssssssss", $newChapterID, $chapterTitle, $chapterDescription, $chapterContent, $chapterImagePath, $chapterVideoPath, $courseID,$assessmentID);
                $insertChapterStmt->execute();
            }
        }
    }

    // Redirect with success message
    echo "<script>
        alert('Course and chapters updated successfully!');
        window.location.href = 'coursemanagement.php';
    </script>";
    exit();
}
?>
