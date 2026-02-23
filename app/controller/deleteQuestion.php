<?php
include '../../config/db_connection.php';

if (isset($_GET['questionID'])) {
    $questionID = $_GET['questionID'];
    $courseID = $_GET['courseID'];
    $chapterID = $_GET['chapterID'];

    try {
        // Prepare and execute the delete query
        $stmt = $conn->prepare("DELETE FROM assessmentquestion WHERE AssessmentQuestionID = ?");
        $stmt->bind_param("s", $questionID);

        if ($stmt->execute()) {
            echo "<script>
                alert('Question deleted successfully!');
                window.location.href = 'assessmentEdit.php?courseID=$courseID&chapterID=$chapterID';
            </script>";
        } else {
            echo "<script>
                alert('Failed to delete the question. Please try again.');
                window.history.back();
            </script>";
        }
    } catch (Exception $e) {
        echo "<script>
            alert('An error occurred: {$e->getMessage()}');
            window.history.back();
        </script>";
    }
} else {
    echo "<script>
        alert('Question ID not provided.');
        window.history.back();
    </script>";
}
?>
