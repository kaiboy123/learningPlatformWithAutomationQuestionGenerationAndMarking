<?php
include '../../config/db_connection.php';
session_start();

// Check admin login
if (!isset($_SESSION['admin'])) {
    header('Location: ../controller/login.php');
    exit();
}

// Fetch courses for the dropdown
$courseQuery = "SELECT CourseID, CourseTitle FROM course";
$courseResult = $conn->query($courseQuery);
$courses = [];
if ($courseResult && $courseResult->num_rows > 0) {
    while ($row = $courseResult->fetch_assoc()) {
        $courses[] = $row;
    }
}

// Handle course selection and fetch ratings
$selectedCourse = isset($_GET['course']) ? $_GET['course'] : null;
$ratingsData = [];
$userFeedbacks = [];
if ($selectedCourse) {
    // Fetch aggregated ratings data
    $ratingsQuery = "
        SELECT Rating, COUNT(*) AS Count
        FROM course_feedback
        WHERE CourseID = ?
        GROUP BY Rating
        ORDER BY Rating ASC";
    $stmt = $conn->prepare($ratingsQuery);
    $stmt->bind_param("s", $selectedCourse);
    $stmt->execute();
    $ratingsResult = $stmt->get_result();
    while ($row = $ratingsResult->fetch_assoc()) {
        $ratingsData[$row['Rating']] = $row['Count'];
    }

    // Fetch individual user feedback
    $userFeedbackQuery = "
        SELECT l.LearnerName, l.LearnerEmail, cf.Rating, cf.Comment, cf.FeedbackDate
        FROM course_feedback cf
        JOIN learner l ON cf.LearnerID = l.LearnerID
        WHERE cf.CourseID = ?
        ORDER BY cf.FeedbackDate DESC";
    $stmt = $conn->prepare($userFeedbackQuery);
    $stmt->bind_param("s", $selectedCourse);
    $stmt->execute();
    $userFeedbackResult = $stmt->get_result();
    while ($row = $userFeedbackResult->fetch_assoc()) {
        $userFeedbacks[] = $row;
    }
}

// Fill missing ratings with zero counts (1 to 5 stars)
for ($i = 1; $i <= 5; $i++) {
    if (!isset($ratingsData[$i])) {
        $ratingsData[$i] = 0;
    }
}

$sortOrder = isset($_GET['sort']) && $_GET['sort'] === 'asc' ? 'ASC' : 'DESC';

$sql = "
    SELECT l.LearnerName, l.LearnerEmail, cf.Rating, cf.Comment, cf.FeedbackDate
    FROM course_feedback cf
    JOIN learner l ON cf.LearnerID = l.LearnerID
    WHERE cf.CourseID = ?
    ORDER BY cf.Rating $sortOrder, cf.FeedbackDate DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $selectedCourse);
$stmt->execute();
$result = $stmt->get_result();
$userFeedbacks = [];
while ($row = $result->fetch_assoc()) {
    $userFeedbacks[] = $row;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rating Report</title>
    <link rel="stylesheet" href="css/style.css">
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
            margin-left: 250px;
            padding: 40px;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 900px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .course-select {
            margin: 20px 0;
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        select {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        canvas {
            margin: 20px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #343a40;
            color: #fff;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="side-bar">
    <div class="profile">
        <img src="images/pic-1.jpg" class="image" alt="Admin Picture">
        <h1 class="name" style="color:#ddd"><?= htmlspecialchars($_SESSION['admin']['AdminName']); ?></h1>
        <p class="role"><?= htmlspecialchars($_SESSION['admin']['AdminRole']); ?></p>
    </div>
    <nav class="navbar" style="max-height: 70vh; overflow-y: auto;">
        <a href="adminhome.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
        <?php if ($_SESSION['admin']['AdminRole'] === 'Manager'): ?>
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
        <h1>Rating Report</h1>

        <!-- Course Selection -->
        <div class="course-select">
            <form method="GET" action="ratingreport.php">
                <select name="course" required>
                    <option value="">Select a Course</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?= htmlspecialchars($course['CourseID']); ?>" <?= $selectedCourse == $course['CourseID'] ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($course['CourseTitle']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">View Report</button>
            </form>
        </div>

        <!-- Pie Chart -->
        <?php if ($selectedCourse): ?>
            <canvas id="ratingChart" width="400" height="400"></canvas>
            <script>
                const ctx = document.getElementById('ratingChart').getContext('2d');
                const data = {
                    labels: ['1 Star', '2 Stars', '3 Stars', '4 Stars', '5 Stars'],
                    datasets: [{
                        label: 'Ratings Distribution',
                        data: <?= json_encode(array_values($ratingsData)); ?>,
                        backgroundColor: ['#ff6384', '#ff9f40', '#ffcd56', '#4bc0c0', '#36a2eb'],
                        hoverOffset: 4
                    }]
                };
                const config = {
                    type: 'pie',
                    data: data,
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { position: 'top' },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.raw || 0;
                                        return `${label}: ${value} ratings`;
                                    }
                                }
                            }
                        }
                    }
                };
                new Chart(ctx, config);
            </script>
        <?php endif; ?>

        <!-- User Feedback Table -->
        <?php if ($selectedCourse && count($userFeedbacks) > 0): ?>
            <table>
    <thead>
        <tr>
            <th>Username</th>
            <th>Email</th>
            <th>
                <a href="?course=<?= htmlspecialchars($selectedCourse); ?>&sort=<?= $sortOrder === 'ASC' ? 'desc' : 'asc'; ?>" style="color:#ddd;">
                    Rating <i class="fas fa-sort<?= $sortOrder === 'ASC' ? '-up' : '-down'; ?>"></i>
                </a>
            </th>
            <th>Comment</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($userFeedbacks as $feedback): ?>
            <tr>
                <td><?= htmlspecialchars($feedback['LearnerName']); ?></td>
                <td><?= htmlspecialchars($feedback['LearnerEmail']); ?></td>
                <td><?= htmlspecialchars($feedback['Rating']); ?>/5</td>
                <td><?= htmlspecialchars($feedback['Comment']); ?></td>
                <td><?= htmlspecialchars($feedback['FeedbackDate']); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
        <?php elseif ($selectedCourse): ?>
            <p>No feedback found for this course.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
