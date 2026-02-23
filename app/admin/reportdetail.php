<?php
include '../../config/db_connection.php';
session_start();

if (!isset($_SESSION['admin'])) {
    header('Location: ../controller/login.php');
    exit();
}

// Default registration period (last 1 month)
$registrationPeriod = isset($_GET['period']) ? intval($_GET['period']) : 1;

// Calculate date range
$startDate = date('Y-m-d', strtotime("-{$registrationPeriod} months"));
$endDate = date('Y-m-d');

// Fetch data for courses and completion
$courseQuery = "
    SELECT 
        c.CourseTitle,
        COUNT(DISTINCT ps.LearnerID) AS TotalStudents,
        COUNT(DISTINCT CASE 
            WHEN (
                SELECT COUNT(cc.CourseChapterID) 
                FROM coursechapter cc
                WHERE cc.CourseID = c.CourseID
            ) = (
                SELECT COUNT(DISTINCT cc.CourseChapterID) 
                FROM coursechapter cc
                LEFT JOIN completedassessment ca 
                    ON cc.AssessmentID = ca.AssessmentID AND ca.CompletedLearner = ps.LearnerID
                LEFT JOIN completedpracticaltest cp 
                    ON cc.PracticalTestID = cp.PracticalTestID AND cp.CompletedLearner = ps.LearnerID
                WHERE cc.CourseID = c.CourseID
                  AND (ca.AssessmentID IS NOT NULL OR cc.AssessmentID IS NULL)
                  AND (cp.PracticalTestID IS NOT NULL OR cc.PracticalTestID IS NULL)
            )
            THEN ps.LearnerID END
        ) AS Completed,
        COUNT(DISTINCT ps.LearnerID) - COUNT(DISTINCT CASE 
            WHEN (
                SELECT COUNT(cc.CourseChapterID) 
                FROM coursechapter cc
                WHERE cc.CourseID = c.CourseID
            ) = (
                SELECT COUNT(DISTINCT cc.CourseChapterID) 
                FROM coursechapter cc
                LEFT JOIN completedassessment ca 
                    ON cc.AssessmentID = ca.AssessmentID AND ca.CompletedLearner = ps.LearnerID
                LEFT JOIN completedpracticaltest cp 
                    ON cc.PracticalTestID = cp.PracticalTestID AND cp.CompletedLearner = ps.LearnerID
                WHERE cc.CourseID = c.CourseID
                  AND (ca.AssessmentID IS NOT NULL OR cc.AssessmentID IS NULL)
                  AND (cp.PracticalTestID IS NOT NULL OR cc.PracticalTestID IS NULL)
            )
            THEN ps.LearnerID END
        ) AS NotCompleted
    FROM course c
    LEFT JOIN paymentsubscription ps 
        ON ps.CourseID = c.CourseID AND ps.PaymentDate BETWEEN ? AND ?
    GROUP BY c.CourseID;
";


$stmt = $conn->prepare($courseQuery);
$stmt->bind_param("ss", $startDate, $endDate);
$stmt->execute();
$result = $stmt->get_result();

$courses = [];
while ($row = $result->fetch_assoc()) {
    $courses[] = $row;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance and Completion Report</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        }
        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .date-range {
            margin-bottom: 20px;
            text-align: center;
        }
        .date-range label {
            margin-right: 10px;
            font-weight: bold;
        }
        .date-range select, .date-range button {
            padding: 8px;
            margin-right: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .date-range button {
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
        }
        .date-range button:hover {
            background-color: #0056b3;
        }
        canvas {
            width: 100%;
            height: 400px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f4f4f4;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
    </style>
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
    <div class="container">
        <h1>Performance and Completion Report</h1>

        <!-- Date Selector -->
        <div class="date-range">
            <form method="get" action="">
                <label for="registration-period">Registration Period:</label>
                <select id="registration-period" name="period" onchange="this.form.submit()">
                    <option value="1" <?= $registrationPeriod == 1 ? 'selected' : ''; ?>>Last 1 Month</option>
                    <option value="3" <?= $registrationPeriod == 3 ? 'selected' : ''; ?>>Last 3 Months</option>
                    <option value="6" <?= $registrationPeriod == 6 ? 'selected' : ''; ?>>Last 6 Months</option>
                    <option value="12" <?= $registrationPeriod == 12 ? 'selected' : ''; ?>>Last 12 Months</option>
                </select>
            </form>
        </div>

        <!-- Graph -->
        <canvas id="progressChart"></canvas>

        <!-- Table -->
        <table>
            <thead>
                <tr>
                    <th>Course Name</th>
                    <th>Total Students</th>
                    <th>Completed</th>
                    <th>Not Completed</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($courses as $course): ?>
                    <tr>
                        <td><?= htmlspecialchars($course['CourseTitle']); ?></td>
                        <td><?= htmlspecialchars($course['TotalStudents']); ?></td>
                        <td><?= htmlspecialchars($course['Completed']); ?></td>
                        <td><?= htmlspecialchars($course['NotCompleted']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    const courses = <?= json_encode($courses); ?>;
    const ctx = document.getElementById('progressChart').getContext('2d');
    const labels = courses.map(course => course.CourseTitle);
    const completed = courses.map(course => course.Completed);
    const notCompleted = courses.map(course => course.NotCompleted);

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Completed',
                    data: completed,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Not Completed',
                    data: notCompleted,
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>
</body>
</html>
