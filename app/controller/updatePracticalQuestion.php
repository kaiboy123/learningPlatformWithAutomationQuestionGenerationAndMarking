<?php
include '../../config/db_connection.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $questionID = $_POST['questionID'] ?? null;
    $practicalTitle = $_POST['practicalTitle'] ?? null;
    $practicalContent = $_POST['practicalContent'] ?? null;
    $practicalAnswer = $_POST['practicalAnswer'] ?? null;

    if (!$questionID || !$practicalTitle || !$practicalContent || !$practicalAnswer) {
        echo "<script>
            alert('All fields are required.');
            window.history.back();
        </script>";
        exit;
    }

    // Update the practical question
    $query = "UPDATE practicaltest SET PracticalQuestionTitle = ?, PracticalQuestionContent = ?, PracticalQuestionAnswer = ? WHERE PracticalTestID = ?";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param("ssss", $practicalTitle, $practicalContent, $practicalAnswer, $questionID);

        if ($stmt->execute()) {
            echo "<script>
                alert('Practical question updated successfully!');
                window.history.back();
            </script>";
        } else {
            echo "<script>
                alert('Failed to update the practical question.');
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
        alert('Invalid request method.');
        window.history.back();
    </script>";
}

// Close the database connection
$conn->close();
?>
