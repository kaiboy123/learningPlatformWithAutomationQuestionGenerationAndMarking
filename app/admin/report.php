<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Selection</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            display: flex;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
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
            background-color: #212529;
        }
        .side-bar .profile img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-bottom: 10px;
            border: 3px solid #495057;
            transition: transform 0.3s;
        }
        .side-bar .profile img:hover {
            transform: scale(1.1);
        }
        .side-bar nav a {
            display: block;
            color: #fff;
            padding: 15px;
            text-decoration: none;
            border-bottom: 1px solid #495057;
            transition: background-color 0.3s, padding-left 0.3s;
        }
        .side-bar nav a:hover {
            background-color: #495057;
            padding-left: 30px;
        }
        .main-content {
            margin-left: 250px;
            padding: 40px;
            width: calc(100% - 250px);
            background-color: #f8f9fa;
        }
        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            max-width: 1200px;
            padding: 20px;
        }
        .report-header {
            font-size: 24px;
            color: #333;
            margin-bottom: 30px;
        }
        .report-options {
            display: flex;
            justify-content: space-between;
            width: 100%;
            gap: 20px; /* Added space between the report options */
        }
        .report-option {
            flex: 1;
            text-align: center;
            text-decoration: none;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .report-option:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 12px rgba(0,0,0,0.2);
        }
        .report-option img {
            width: 80%;
            height: auto;
            border-radius: 8px 8px 0 0;
            max-width: 400px; /* Maximum width for larger screens */
        }
        .report-option .title {
            margin: 10px;
            font-size: 20px;
            color: #333;
        }
    </style>
</head>
<body>

<?php
session_start();
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
    <div class="container">
        <h1 class="report-header">Choose a Report</h1>
        <div class="report-options">
            <a href="reportsummary.php" class="report-option">
                <img src="../../public/pages/images/summary.png" alt="Platform User and Enrollment Summary">
                <div class="title">Platform User and Enrollment Summary</div>
            </a>
            <a href="reportdetail.php" class="report-option">
                <img src="../../public/pages/images/detail.png" alt="Course Enrollment Report">
                <div class="title">Performance and Completion Report</div>
            </a>
            <a href="ratingreport.php" class="report-option">
                <img src="../../public/pages/images/ratereport.png" alt="Rating Report">
                <div class="title">Rating Report</div>
            </a>
        </div>
    </div>
</div>

</body>
</html>