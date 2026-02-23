<?php
include '../../config/db_connection.php';

// Check if the questionID is provided
if (isset($_GET['questionID'])) {
    $questionID = $_GET['questionID'];
    $courseID = $_GET['courseID'];
    $chapterID = $_GET['chapterID'];
    // Prepare the DELETE query
    $query = "DELETE FROM practicaltest WHERE PracticalTestID = ?";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param("s", $questionID);

        if ($stmt->execute()) {
            echo "<script>
                alert('Practical question deleted successfully!');
                window.location.href = 'editPractical.php?courseID=$courseID&chapterID=$chapterID';
            </script>";
        } else {
            echo "<script>
                alert('Failed to delete the practical question.');
                window.history.back();
            </script>";
        }

        $stmt->close();
    } else {
        echo "<script>
            alert('Failed to prepare the query.');
            window.history.back();
        </script>";
    }
} else {
    echo "<script>
        alert('Question ID not provided.');
        window.history.back();
    </script>";
}

// Close the database connection
$conn->close();
?>