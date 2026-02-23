<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Platform User and Enrollment Summary</title>

   <!-- Font Awesome CDN link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">

   <!-- Custom CSS -->
   <link rel="stylesheet" href="../../public/pages/css/style.css">
   <?php
include '../../config/db_connection.php'; // Include your database connection
session_start();

// Default date range (last 30 days)
$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : date('Y-m-d', strtotime('-30 days'));
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : date('Y-m-d');

// Fetch total users (no date filter for this example)
$totalUsersQuery = "SELECT COUNT(*) as total_users FROM learner";
$totalUsersResult = $conn->query($totalUsersQuery);
$totalUsers = $totalUsersResult->fetch_assoc()['total_users'];

// Fetch total courses
$totalCoursesQuery = "SELECT COUNT(*) as total_courses FROM course";
$totalCoursesResult = $conn->query($totalCoursesQuery);
$totalCourses = $totalCoursesResult->fetch_assoc()['total_courses'];

// Fetch total enrollments within date range
$totalEnrollmentsQuery = "
    SELECT COUNT(*) as total_enrollments 
    FROM paymentsubscription 
    WHERE SubscriptionStatus = 'active'
    AND PaymentDate BETWEEN ? AND ?";
$totalEnrollmentsStmt = $conn->prepare($totalEnrollmentsQuery);
$totalEnrollmentsStmt->bind_param("ss", $startDate, $endDate);
$totalEnrollmentsStmt->execute();
$totalEnrollmentsResult = $totalEnrollmentsStmt->get_result();
$totalEnrollments = $totalEnrollmentsResult->fetch_assoc()['total_enrollments'];

// Fetch course enrollment details within date range
$courseEnrollmentQuery = "
    SELECT course.CourseTitle, COUNT(paymentsubscription.CourseID) as total_enrollments 
    FROM course 
    LEFT JOIN paymentsubscription 
    ON course.CourseID = paymentsubscription.CourseID 
    AND paymentsubscription.SubscriptionStatus = 'active'
    AND paymentsubscription.PaymentDate BETWEEN ? AND ?
    GROUP BY course.CourseID
";
$courseEnrollmentStmt = $conn->prepare($courseEnrollmentQuery);
$courseEnrollmentStmt->bind_param("ss", $startDate, $endDate);
$courseEnrollmentStmt->execute();
$courseEnrollmentResult = $courseEnrollmentStmt->get_result();

$courseEnrollmentData = [];
while ($row = $courseEnrollmentResult->fetch_assoc()) {
    $courseEnrollmentData[] = $row;
}
?>


   <style>
      body {
         display: flex;
         font-size: 18px;
         background-color: #f8f9fa;
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
         margin-left: 10%;
         padding: 40px;
         width: calc(100% - 270px);
      }
      .main-content h1 {
         margin-bottom: 40px;
         font-size: 2.5em;
      }
      .date-selector {
         margin-bottom: 20px;
         background-color: #fff;
         border-radius: 8px;
         padding: 15px;
         box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      }
      .date-selector label {
         margin-right: 10px;
      }
      .date-selector input[type="date"] {
         margin-right: 10px;
      }
      .date-selector button {
         padding: 10px 15px;
         background-color: #007bff;
         color: #fff;
         border: none;
         border-radius: 5px;
         cursor: pointer;
      }
      .date-selector button:hover {
         background-color: #0056b3;
      }
      .card-container {
         display: flex;
         justify-content: space-between;
         gap: 20px;
         margin-bottom: 40px;
      }
      .card {
         background-color: #fff;
         border: 1px solid #ddd;
         border-radius: 8px;
         padding: 20px;
         width: 100%;
         box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      }
      .card h3 {
         margin-top: 0;
      }
      .card p {
         font-size: 1.2em;
         margin: 0;
      }
      .chart-container {
         margin-bottom: 40px;
      }
      .chart {
         width: 100%;
         height: 400px;
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
   </style>

   <!-- Include Chart.js library -->
   <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<?php

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
      <h1>Platform User and Enrollment Summary</h1>
   </header>

   <!-- Date Selector UI -->
   <div class="date-selector">
      <form method="GET" action="">
         <label for="startDate">Start Date:</label>
         <input type="date" id="startDate" name="startDate" value="<?= htmlspecialchars($startDate); ?>">
         <label for="endDate">End Date:</label>
         <input type="date" id="endDate" name="endDate" value="<?= htmlspecialchars($endDate); ?>">
         <button type="submit">Update Report</button>
      </form>
   </div>

   <!-- Cards for Summary -->
   <div class="card-container">
      <div class="card">
         <h3>Total Users</h3>
         <p id="totalUsers"><?= htmlspecialchars($totalUsers); ?></p>
      </div>
      <div class="card">
         <h3>Total Courses</h3>
         <p id="totalCourses"><?= htmlspecialchars($totalCourses); ?></p>
      </div>
      <div class="card">
         <h3>Total Enrollments</h3>
         <p id="totalEnrollments"><?= htmlspecialchars($totalEnrollments); ?></p>
      </div>
   </div>

   <!-- Chart Container -->
   <div class="chart-container">
      <canvas id="courseEnrollmentChart" class="chart"></canvas>
   </div>

   <!-- Enrollment Table -->
   <div class="table-container">
      <h2>Course Enrollment Details</h2>
      <table id="enrollmentTable">
         <thead>
            <tr>
               <th>Course Name</th>
               <th>Total Enrollments</th>
            </tr>
         </thead>
         <tbody>
            <?php foreach ($courseEnrollmentData as $course): ?>
               <tr>
                  <td><?= htmlspecialchars($course['CourseTitle']); ?></td>
                  <td><?= htmlspecialchars($course['total_enrollments']); ?></td>
               </tr>
            <?php endforeach; ?>
         </tbody>
      </table>
   </div>
</div>

<script>
   // Dynamic data for chart
   const courseTitles = <?= json_encode(array_column($courseEnrollmentData, 'CourseTitle')); ?>;
   const enrollments = <?= json_encode(array_column($courseEnrollmentData, 'total_enrollments')); ?>;

   const ctx = document.getElementById('courseEnrollmentChart').getContext('2d');
   new Chart(ctx, {
       type: 'bar',
       data: {
           labels: courseTitles,
           datasets: [{
               label: 'Total Enrollments',
               data: enrollments,
               backgroundColor: 'rgba(54, 162, 235, 0.5)',
               borderColor: 'rgba(54, 162, 235, 1)',
               borderWidth: 1
           }]
       },
       options: {
           scales: {
               y: {
                   beginAtZero: true
               }
           }
       }
   });
</script>

</body>
</html>