<?php
include '../../config/db_connection.php';
session_start();

$adminID = $_GET['id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adminName = $_POST['AdminName'];
    $adminEmail = $_POST['AdminEmail'];
    $adminContact = $_POST['AdminContactInfo'];
    $adminRole = $_POST['AdminRole'];
    $accountStatus = $_POST['AccountStatus'];

    // Update admin details
    $stmt = $conn->prepare("UPDATE administrator SET AdminName = ?, AdminEmail = ?, AdminContactInfo = ?, AdminRole = ?, AccountStatus = ? WHERE AdminID = ?");
    $stmt->bind_param("ssssss", $adminName, $adminEmail, $adminContact, $adminRole, $accountStatus, $adminID);
    if ($stmt->execute()) {
        header("Location: adminmanagement.php?message=Admin updated successfully");
        exit();
    } else {
        $error = "Error updating admin: " . $conn->error;
    }
} else {
    $result = $conn->query("SELECT * FROM administrator WHERE AdminID = '$adminID'");
    $admin = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Edit Admin</title>
   <!-- Font Awesome for icons -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
   <!-- Custom CSS -->
   <link rel="stylesheet" href="css/style.css">
   <style>
      body {
         font-family: Arial, sans-serif;
         background-color: #f8f9fa;
         display: flex;
         justify-content: center;
         align-items: center;
         height: 100vh;
         margin: 0;
      }
      .container {
         background-color: #fff;
         padding: 20px;
         border-radius: 10px;
         box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
         max-width: 500px;
         width: 100%;
      }
      h1 {
         text-align: center;
         color: #333;
         margin-bottom: 20px;
      }
      form {
         display: flex;
         flex-direction: column;
         gap: 15px;
      }
      label {
         font-weight: bold;
         color: #555;
      }
      input, select, button {
         padding: 10px;
         font-size: 16px;
         border: 1px solid #ddd;
         border-radius: 5px;
      }
      select {
         cursor: pointer;
      }
      button {
         background-color: #007bff;
         color: #fff;
         border: none;
         cursor: pointer;
         font-size: 16px;
         transition: background-color 0.3s ease;
      }
      button:hover {
         background-color: #0056b3;
      }
      .back-link {
         text-align: center;
         margin-top: 10px;
      }
      .back-link a {
         text-decoration: none;
         color: #007bff;
         font-weight: bold;
      }
      .back-link a:hover {
         color: #0056b3;
      }
   </style>
</head>
<body>
   <div class="container">
      <h1>Edit Admin</h1>
      <form action="admin_edit.php?id=<?= $adminID ?>" method="post">
         <label for="AdminName">Name:</label>
         <input type="text" id="AdminName" name="AdminName" value="<?= htmlspecialchars($admin['AdminName']); ?>" required>
         
         <label for="AdminEmail">Email:</label>
         <input type="email" id="AdminEmail" name="AdminEmail" value="<?= htmlspecialchars($admin['AdminEmail']); ?>" required>
         
         <label for="AdminContactInfo">Contact:</label>
         <input type="text" id="AdminContactInfo" name="AdminContactInfo" value="<?= htmlspecialchars($admin['AdminContactInfo']); ?>" required>
         
         <label for="AdminRole">Role:</label>
         <select id="AdminRole" name="AdminRole" required>
            <option value="Manager" <?= $admin['AdminRole'] == 'Manager' ? 'selected' : ''; ?>>Manager</option>
            <option value="Normal Staff" <?= $admin['AdminRole'] == 'Normal Staff' ? 'selected' : ''; ?>>Normal Staff</option>
         </select>
         
         <label for="AccountStatus">Status:</label>
         <select id="AccountStatus" name="AccountStatus" required>
            <option value="active" <?= $admin['AccountStatus'] == 'active' ? 'selected' : ''; ?>>Active</option>
            <option value="inactive" <?= $admin['AccountStatus'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
         </select>
         
         <button type="submit">Save Changes</button>
      </form>
      <div class="back-link">
         <a href="adminmanagement.php"><i class="fas fa-arrow-left"></i> Back to Admin Management</a>
      </div>
   </div>
</body>
</html>
