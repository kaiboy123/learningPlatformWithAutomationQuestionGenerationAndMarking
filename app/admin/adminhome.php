<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin Dashboard</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="../../public/pages/css/style.css">

   <style>
      body {
         display: flex;
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
         width: 100px; /* Increased size */
         height: 100px; /* Increased size */
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
         margin-left: 10%; /* Adjusted margin for larger elements */
         padding: 40px; /* Increased padding */
         width: calc(100% - 270px); /* Adjusted width */
         background-color: #f8f9fa;
      }
      .main-content h1 {
         margin-bottom: 40px; /* Increased margin */
         font-size: 2em; /* Increased font size */
      }
      .main-content p {
        font-size: large;
        font-weight: bold;
      }
      .cards {
         display: grid;
         grid-template-columns: repeat(2, 1fr);
         gap: 30px; /* Increased gap */
      }
      .card {
         background: #fff;
         border: 1px solid #ddd;
         border-radius: 5px;
         padding: 30px; /* Increased padding */
         display: flex;
         flex-direction: column;
         align-items: center;
         text-align: center;
         font-size: 1.2em; /* Increased font size */
      }
      .card h3 {
         margin-bottom: 20px; /* Increased margin */
         font-size: 1.5em; /* Increased font size */
      }
      .card img {
         width: 80px; /* Increased size */
         height: 80px; /* Increased size */
         margin-bottom: 20px; /* Increased margin */
      }
      
   </style>

</head>
<body>
<?php
include '../../config/db_connection.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin'])) {
   header('Location: ../controller/login.php');
   exit();
}

// Fetch Total Users (from learner table)
$totalUsersQuery = "SELECT COUNT(*) AS total_users FROM learner";
$totalUsersResult = $conn->query($totalUsersQuery);
$totalUsers = $totalUsersResult->fetch_assoc()['total_users'] ?? 0;

// Fetch New Enrollments (e.g., last 30 days, from learner table)
$newEnrollmentsQuery = "
    SELECT COUNT(DISTINCT l.LearnerID) AS new_enrollments
    FROM learner l
    JOIN paymentsubscription ps ON l.LearnerID = ps.LearnerID
    WHERE ps.PaymentDate >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
$newEnrollmentsResult = $conn->query($newEnrollmentsQuery);
$newEnrollments = $newEnrollmentsResult->fetch_assoc()['new_enrollments'] ?? 0;

// Fetch Total Courses (from course table)
$totalCoursesQuery = "SELECT COUNT(*) AS total_courses FROM course";
$totalCoursesResult = $conn->query($totalCoursesQuery);
$totalCourses = $totalCoursesResult->fetch_assoc()['total_courses'] ?? 0;

$feedbackQuery = "
   SELECT COUNT(*) AS feedback_count 
   FROM course_feedback 
   WHERE FeedbackDate >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
$feedbackResult = $conn->query($feedbackQuery);
$feedbackCount = $feedbackResult->fetch_assoc()['feedback_count'] ?? 0;

$adminRole = $_SESSION['admin']['AdminRole'];
$adminName = $_SESSION['admin']['AdminName'];
?>

<div class="side-bar">

<div class="profile">
      <img src="../../public/pages/images/pic-1.jpg" class="image" alt="Admin Picture">
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
      <h1 style="font-size:32px;">Admin Dashboard</h1>
   </header>

   <section class="cards">
      <!-- Total Users -->
      <div class="card">
         <img src="../../public/pages/images/usermanage.jpg" alt="Total Users Icon">
         <h3>Total Users</h3>
         <p><?= htmlspecialchars($totalUsers); ?></p>
      </div>
      
      <!-- Total Courses -->
      <div class="card">
         <img src="../../public/pages/images/coursemanage.png" alt="Total Courses Icon">
         <h3>Total Courses</h3>
         <p><?= htmlspecialchars($totalCourses); ?></p>
      </div>
      
      <!-- New Enrollments -->
      <div class="card">
         <img src="../../public/pages/images/userenrollment.png" alt="New Enrollments Icon">
         <h3>New Enrollments</h3>
         <p><?= htmlspecialchars($newEnrollments); ?></p>
      </div>
      
      <!-- Optional Replacement for "Reports Generated" -->
      <div class="card">
         <img src="../../public/pages/images/feedback.jpg" alt="Feedback Icon">
         <h3>Feedback Received</h3>
         <p><?= htmlspecialchars($feedbackCount); ?></p>
      </div>
   </section>
</div>

</body>
</html>