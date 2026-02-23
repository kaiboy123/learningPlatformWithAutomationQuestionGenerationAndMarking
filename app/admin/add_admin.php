<?php
include '../../config/db_connection.php'; // Include your database connection
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adminName = $_POST['AdminName'];
    $adminEmail = $_POST['AdminEmail'];
    $adminContact = $_POST['AdminContactInfo'];
    $adminRole = $_POST['AdminRole'];
    $accountStatus = $_POST['AccountStatus'];
    $password = password_hash($_POST['Password'], PASSWORD_DEFAULT);

    // Generate unique AdminID
    $result = $conn->query("SELECT MAX(CAST(SUBSTRING(AdminID, 3) AS UNSIGNED)) AS MaxID FROM administrator");
    $row = $result->fetch_assoc();
    $newAdminID = 'AD' . str_pad(($row['MaxID'] + 1), 3, '0', STR_PAD_LEFT);

    // Insert new admin
    $stmt = $conn->prepare("INSERT INTO administrator (AdminID, AdminName, AdminEmail, AdminContactInfo, AdminRole, AccountStatus, Password) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $newAdminID, $adminName, $adminEmail, $adminContact, $adminRole, $accountStatus, $password);
    if ($stmt->execute()) {
        header("Location: adminmanagement.php?message=Admin added successfully");
        exit();
    } else {
        $error = "Error adding admin: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Add New Admin</title>
   <!-- Font Awesome CDN for icons -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
   <!-- Custom CSS -->
   <link rel="stylesheet" href="../../public/pages/css/style.css">
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
      <h1>Add New Admin</h1>
      <form action="add_admin.php" method="post">
         <label for="AdminName">Name:</label>
         <input type="text" id="AdminName" name="AdminName" placeholder="Enter admin name" required>
         
         <label for="AdminEmail">Email:</label>
         <input type="email" id="AdminEmail" name="AdminEmail" placeholder="Enter admin email" required>
         
         <label for="AdminContactInfo">Contact:</label>
         <input type="text" id="AdminContactInfo" name="AdminContactInfo" placeholder="Enter contact number" required>
         
         <label for="Password">Password:</label>
         <input type="password" id="Password" name="Password" placeholder="Enter password" required>
         
         <label for="AdminRole">Role:</label>
         <select id="AdminRole" name="AdminRole" required>
            <option value="">Select Role</option>
            <option value="Manager">Manager</option>
            <option value="Staff">Staff</option>
         </select>
         
         <label for="AccountStatus">Status:</label>
         <select id="AccountStatus" name="AccountStatus" required>
            <option value="">Select Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
         </select>
         
         <button type="submit">Add Admin</button>
      </form>
      <div class="back-link">
         <a href="adminmanagement.php"><i class="fas fa-arrow-left"></i> Back to Admin Management</a>
      </div>
   </div>
</body>
</html>