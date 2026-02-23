<?php
include '../../config/db_connection.php';
session_start();

// Check admin login
if (!isset($_SESSION['admin'])) {
    header('Location: ../controller/login.php');
    exit();
}

// Fetch all achievements
$sql = "SELECT a.AchievementID, a.AchievementDate, l.LearnerName, l.LearnerEmail, at.AchievementTypeName, at.AchievementTypeImage, c.CourseTitle
        FROM achievements a
        JOIN learner l ON a.LearnerID = l.LearnerID
        JOIN achievement_type at ON a.AchievementTypeID = at.AchievementTypeID
        JOIN course c ON at.CourseID = c.CourseID
        ORDER BY a.AchievementDate DESC";
$result = $conn->query($sql);

$achievements = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $achievements[] = $row;
    }
}

// Fetch course names for the filter dropdown
$courseResult = $conn->query("SELECT DISTINCT CourseTitle FROM course");
$courses = [];
if ($courseResult && $courseResult->num_rows > 0) {
    while ($row = $courseResult->fetch_assoc()) {
        $courses[] = $row['CourseTitle'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Achievements</title>
    <link rel="stylesheet" href="../../public/pages/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
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
      <h1>Manage Achievements</h1>
   </header>

   <div class="search-container">
      <input type="text" id="searchInput" placeholder="Search Achievements..." onkeyup="searchFunction()">
      <select id="filterCourse" onchange="filterFunction()">
         <option value="">Filter by Course</option>
         <?php foreach ($courses as $course): ?>
            <option value="<?= htmlspecialchars($course); ?>"><?= htmlspecialchars($course); ?></option>
         <?php endforeach; ?>
      </select>
      
      <a href="../controller/addachievementtype.php" class="btn" style="width: 30%; margin-left:5%;"><i class="fas fa-trophy" style="margin-right:10px;"></i>Add Achievement Type</a>
   </div>
   <div class="table-container">
      <table id="achievementTable">
         <thead>
            <tr>
               <th>Learner</th>
               <th>Email</th>
               <th>Achievement Type</th>
               <th>Course</th>
               <th>Certificate</th>
               <th>Date</th>
               <th>Actions</th>
            </tr>
         </thead>
         <tbody>
            <?php foreach ($achievements as $achievement): ?>
               <tr>
                  <td><?= htmlspecialchars($achievement['LearnerName']); ?></td>
                  <td><?= htmlspecialchars($achievement['LearnerEmail']); ?></td>
                  <td><?= htmlspecialchars($achievement['AchievementTypeName']); ?></td>
                  <td><?= htmlspecialchars($achievement['CourseTitle']); ?></td>
                  <td>
                     <img src="<?= htmlspecialchars($achievement['AchievementTypeImage']); ?>" alt="Certificate" style="width: 100px; height: auto;">
                  </td>
                  <td><?= htmlspecialchars($achievement['AchievementDate']); ?></td>
                  <td>
                     <a href="../controller/deleteachievement.php?AchievementID=<?= $achievement['AchievementID']; ?>" class="btn">Delete</a>
                  </td>
               </tr>
            <?php endforeach; ?>
         </tbody>
      </table>
   </div>
</div>

<script>
   // Search Functionality
   function searchFunction() {
      const input = document.getElementById('searchInput').value.toUpperCase();
      const table = document.getElementById('achievementTable');
      const rows = table.getElementsByTagName('tr');

      for (let i = 1; i < rows.length; i++) {
         const cells = rows[i].getElementsByTagName('td');
         let match = false;
         for (let j = 0; j < cells.length; j++) {
            if (cells[j] && cells[j].innerText.toUpperCase().indexOf(input) > -1) {
               match = true;
               break;
            }
         }
         rows[i].style.display = match ? '' : 'none';
      }
   }

   // Filter by Course Functionality
   function filterFunction() {
    const courseFilter = document.getElementById('filterCourse').value.toUpperCase();
    const table = document.getElementById('achievementTable');
    const rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) {
        const courseCell = rows[i].getElementsByTagName('td')[3]; // Course is in the 4th column (index 3)
        const course = courseCell ? courseCell.innerText.toUpperCase() : '';
        rows[i].style.display = (!courseFilter || course === courseFilter) ? '' : 'none';
    }
}
</script>
</body>
</html>
