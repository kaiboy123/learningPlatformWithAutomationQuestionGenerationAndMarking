<?php
include '../../config/db_connection.php';

$adminID = $_GET['id'];

// Delete the admin
$stmt = $conn->prepare("DELETE FROM administrator WHERE AdminID = ?");
$stmt->bind_param("s", $adminID);
if ($stmt->execute()) {
    header("Location: adminmanagement.php?message=Admin deleted successfully");
    exit();
} else {
    echo "Error deleting admin: " . $conn->error;
}
?>
