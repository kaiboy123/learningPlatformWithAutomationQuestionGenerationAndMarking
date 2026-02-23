<?php
include '../../config/db_connection.php'; // Include your database connection
session_start();

// Check if admin is logged in
// if (!isset($_SESSION['admin'])) {
//     header('Location: adminlogin.php');
//     exit();
// }

// Fetch users and their registered courses
$sql = "
    SELECT l.LearnerID, l.LearnerName, l.LearnerEmail, l.ProfileImg,
           GROUP_CONCAT(DISTINCT c.CourseTitle SEPARATOR ', ') AS RegisteredCourses
    FROM learner l
    LEFT JOIN paymentsubscription ps ON l.LearnerID = ps.LearnerID AND ps.SubscriptionStatus = 'active'
    LEFT JOIN course c ON ps.CourseID = c.CourseID
    GROUP BY l.LearnerID;
";
$result = $conn->query($sql);

$users = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

// Fetch course titles for the filter dropdown
$courseResult = $conn->query("SELECT DISTINCT CourseTitle FROM course");
$courses = [];
if ($courseResult && $courseResult->num_rows > 0) {
    while ($row = $courseResult->fetch_assoc()) {
        $courses[] = $row['CourseTitle'];
    }
}

if (isset($_GET['delete'])) {
   $learnerId = $_GET['delete'];
   $deleteQuery = "DELETE FROM learner WHERE LearnerID = ?";
   $stmt = $conn->prepare($deleteQuery);
   $stmt->bind_param("s", $learnerId);
   if ($stmt->execute()) {
       echo "<script>alert('User deleted successfully'); window.location.href='usermanagement.php';</script>";
   } else {
       echo "<script>alert('Error deleting user');</script>";
   }
   $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>User Management</title>

   <!-- Font Awesome CDN link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">

   <!-- Custom CSS file link -->
   <link rel="stylesheet" href="css/style.css">

   <style>
      body {
         display: flex;
         font-size: 18px;
      }
      .side-bar {
         width: 250px;
         background-color: #343a40;
         color: #fff;
         min-height: 100vh;
         position: fixed;
      }
      .side-bar .profile {
         padding: 20px;
         text-align: center;
      }
      .side-bar .profile img {
         width: 100px;
         height: 100px;
         border-radius: 50%;
         margin-bottom: 10px;
      }
      .side-bar nav a {
         display: block;
         color: #fff;
         padding: 15px;
         text-decoration: none;
         border-bottom: 1px solid #495057;
      }
      .side-bar nav a:hover {
         background-color: #495057;
      }
      .main-content {
         margin-left: 20px;
         padding: 40px;
         background-color: #f8f9fa;
      }
      .main-content h1 {
         margin-bottom: 40px;
         font-size: 2.5em;
      }
      .table-container {
         overflow-x: auto;
         margin-bottom: 40px;
      }
      table {
         width: 100%;
         border-collapse: collapse;
      }
      table, th, td {
         border: 1px solid #ddd;
      }
      th, td {
         padding: 15px;
         text-align: left;
      }
      th {
         background-color: #343a40;
         color: #fff;
      }
      tr:nth-child(even) {
         background-color: #f2f2f2;
      }
      .btn {
         padding: 10px 20px;
         background-color: #007bff;
         color: #fff;
         border: none;
         border-radius: 5px;
         cursor: pointer;
         text-decoration: none;
         display: inline-block;
      }
      .btn:hover {
         background-color: #0056b3;
      }
      .search-container {
         display: flex;
         align-items: center;
         margin-bottom: 20px;
      }
      .search-container input[type="text"] {
         padding: 10px;
         font-size: 16px;
         margin-left: 10px;
         border: 1px solid black;
         border-radius: 5px;
         width: 30%;
      }
      .search-container select {
         padding: 10px;
         font-size: 16px;
         border: 1px solid black;
         border-radius: 5px;
         margin-left: 15px;
      }
   </style>
</head>
<body>
   <?php

// Check if admin is logged in
if (!isset($_SESSION['admin'])) {
   header('Location: ../controller/login.php'); // Redirect to login page if not logged in
   exit();
}

// Get the logged-in admin's role and details
$adminRole = $_SESSION['admin']['AdminRole'];
$adminName = $_SESSION['admin']['AdminName'];
?>

<div class="side-bar">

<div class="profile">
      <img src="images/pic-1.jpg" class="image" alt="Admin Picture">
      <h1 class="name" style="color:#ddd"><?= htmlspecialchars($_SESSION['admin']['AdminName']); ?></h1>
      <p class="role"><?= htmlspecialchars($_SESSION['admin']['AdminRole']); ?></p>
   </div>

   <nav class="navbar" style="max-height: 70vh; overflow-y: auto;">
      <a href="adminhome.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
      <?php if ($adminRole === 'Manager'): ?>
         <a href="adminmanagement.php"><i class="fas fa-user-shield"></i><span>Manage Admin</span></a>
      <?php endif; ?>
      <a href="usermanagement.php"><i class="fas fa-users"></i><span>Manage Users</span></a>
      <a href="coursemanagement.php"><i class="fas fa-book"></i><span>Manage Course</span></a>
      <a href="subscripmanage.php"><i class="fas fa-cogs"></i><span>Manage Plans</span></a>
      <a href="manageachievement.php"><i class="fas fa-trophy"></i><span>Manage Cert</span></a>
      <a href="managefeedback.php"><i class="fas fa-comments"></i><span>Manage Feedback</span></a>
      <a href="report.php"><i class="fas fa-chart-bar"></i><span>Reports</span></a>
      <a href="../controller/logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
   </nav>

</div>

<div class="main-content">
   <header>
      <h1>User Management</h1>
   </header>

   <div class="search-container">
      <i class="fas fa-search"></i>
      <input type="text" id="searchInput" onkeyup="searchFunction()" placeholder="Search for users..">
      <i class="fas fa-filter" style="margin-left: 5%;"></i>  
      <select id="filterSelect" style="margin-left: 15px;" onchange="filterFunction()">
         <option value="">Filter by course</option>
         <?php foreach ($courses as $course): ?>
            <option value="<?= htmlspecialchars($course); ?>"><?= htmlspecialchars($course); ?></option>
         <?php endforeach; ?>
      </select>
   </div>

   <div class="table-container">
      <table id="userTable">
         <thead>
         <tr>
         <th>Username</th>
   <th>Profile Image</th>
   
   <th>Email</th>
   <th>Courses Registered</th>
   <th>Actions</th>
</tr>
</thead>
<tbody>
   <?php foreach ($users as $user): ?>
      <tr>
      <td><?= htmlspecialchars($user['LearnerName']); ?></td>
         <td>
            <?php if (!empty($user['ProfileImg'])): ?>
               <img src="<?= htmlspecialchars($user['ProfileImg']); ?>" alt="Profile Image" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; margin-left:30%;">
            <?php else: ?>
               <img src="images/emptyprofile.jpg" alt="Default Profile" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;">
            <?php endif; ?>
         </td>
         
         <td><?= htmlspecialchars($user['LearnerEmail']); ?></td>
         <td><?= htmlspecialchars($user['RegisteredCourses'] ?? 'None'); ?></td>
         <td>
            <a href="../controller/usereditpage.php?id=<?= $user['LearnerID']; ?>" class="btn">Edit</a>
            <a href="?delete=<?= $user['LearnerID']; ?>" class="btn delete-btn" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
         </td>
      </tr>
   <?php endforeach; ?>
</tbody>
      </table>
   </div>
</div>

<script>
   function searchFunction() {
      var input, filter, table, tr, td, i, j, txtValue;
      input = document.getElementById("searchInput");
      filter = input.value.toUpperCase();
      table = document.getElementById("userTable");
      tr = table.getElementsByTagName("tr");

      for (i = 1; i < tr.length; i++) {
         tr[i].style.display = "none";
         td = tr[i].getElementsByTagName("td");
         for (j = 0; j < td.length; j++) {
            if (td[j]) {
               txtValue = td[j].textContent || td[j].innerText;
               if (txtValue.toUpperCase().indexOf(filter) > -1) {
                  tr[i].style.display = "";
                  break;
               }
            }
         }
      }
   }

   function filterFunction() {
      var input, filter, table, tr, td, i, txtValue;
      input = document.getElementById("filterSelect");
      filter = input.value.toUpperCase();
      table = document.getElementById("userTable");
      tr = table.getElementsByTagName("tr");

      for (i = 1; i < tr.length; i++) {
         tr[i].style.display = "none";
         td = tr[i].getElementsByTagName("td")[3];
         if (td) {
            txtValue = td.textContent || td.innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1 || filter == "") {
               tr[i].style.display = "";
            }
         }
      }
   }
</script>

</body>
</html>
