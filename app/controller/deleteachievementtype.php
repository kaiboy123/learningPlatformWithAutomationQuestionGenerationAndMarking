<?php
include '../../config/db_connection.php';
session_start();

// Check admin login
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

if (isset($_GET['id'])) {
    $achievementID = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM achievement_type WHERE AchievementTypeID = ?");
    $stmt->bind_param("s", $achievementID);
    $stmt->execute();
    $stmt->close();

    header('Location: addachievementtype.php?');
    exit();
} else {
    header('Location: addachievementtype.php');
    exit();
}
?>
