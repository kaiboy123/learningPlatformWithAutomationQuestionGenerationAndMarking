<?php
include '../../config/db_connection.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $courseID = $_POST['courseID'] ?? null;
    $chapterID = $_POST['chapterID'] ?? null;
    $practicalTitle = $_POST['practicalTitle'] ?? null;
    $practicalContent = $_POST['practicalDescription'] ?? null;
    $practicalAnswer = $_POST['practicalAnswer'] ?? null;

    if (!$courseID || !$chapterID || !$practicalTitle || !$practicalContent || !$practicalAnswer) {
        echo "<script>
            alert('All fields are required.');
            window.history.back();
        </script>";
        exit;
    }

    // Generate a new PracticalTestID
    $query = "SELECT MAX(PracticalTestID) AS lastID FROM practicaltest";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    $lastID = $row['lastID'] ?? null;

    if ($lastID) {
        $num = intval(substr($lastID, 2)) + 1;
        $newID = "PT" . str_pad($num, 3, "0", STR_PAD_LEFT);
    } else {
        $newID = "PT001";
    }

    // Insert the practical question
    $query = "INSERT INTO practicaltest (PracticalTestID, PracticalQuestionTitle, PracticalQuestionContent, PracticalQuestionAnswer) 
              VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param("ssss", $newID, $practicalTitle, $practicalContent, $practicalAnswer);

        if ($stmt->execute()) {
            // Update the PracticalTestID in the coursechapter table
            $updateQuery = "UPDATE coursechapter SET PracticalTestID = ? WHERE CourseChapterID = ?";
            $updateStmt = $conn->prepare($updateQuery);

            if ($updateStmt) {
                $updateStmt->bind_param("ss", $newID, $chapterID);
                if ($updateStmt->execute()) {
                    echo "<script>
                        alert('Practical question saved successfully and linked to the chapter!');
                        window.location.href = 'editPractical.php?courseID=$courseID&chapterID=$chapterID';
                    </script>";
                } else {
                    echo "<script>
                        alert('Failed to update the PracticalTestID in coursechapter.');
                        window.history.back();
                    </script>";
                }
                $updateStmt->close();
            } else {
                echo "<script>
                    alert('Failed to prepare the update query.');
                    window.history.back();
                </script>";
            }
        } else {
            echo "<script>
                alert('Failed to save the practical question.');
                window.history.back();
            </script>";
        }

        $stmt->close();
    } else {
        echo "<script>
            alert('Failed to prepare the insert query.');
            window.history.back();
        </script>";
    }
} else {
    echo "<script>
        alert('Invalid request method.');
        window.history.back();
    </script>";
}

// Close the database connection
$conn->close();
?>
