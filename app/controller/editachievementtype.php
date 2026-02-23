<?php
include '../../config/db_connection.php';
session_start();

// Check admin login
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

$achievementID = $_GET['id'];

// Fetch achievement details
$stmt = $conn->prepare("SELECT * FROM achievement_type WHERE AchievementTypeID = ?");
$stmt->bind_param("s", $achievementID);
$stmt->execute();
$result = $stmt->get_result();
$achievement = $result->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $typeName = $_POST['type_name'];
    $description = $_POST['description'];
    $courseID = $_POST['course_id'];
    $imagePath = $achievement['AchievementTypeImage'];

    if (!empty($_FILES['certificate_image']['name'])) {
        $imagePath = 'uploads/' . basename($_FILES['certificate_image']['name']);
        move_uploaded_file($_FILES['certificate_image']['tmp_name'], $imagePath);
    }

    $stmt = $conn->prepare("UPDATE achievement_type SET AchievementTypeName = ?, AchievementTypeDescription = ?, AchievementTypeImage = ?, CourseID = ? WHERE AchievementTypeID = ?");
    $stmt->bind_param("sssss", $typeName, $description, $imagePath, $courseID, $achievementID);
    $stmt->execute();
    $stmt->close();

    header('Location: addachievementtype.php?');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Achievement Type</title>
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
            margin-left: 20px;
            padding: 20px;
            flex-grow: 1;
            background-color: #f8f9fa;
        }
        .main-content h1 {
            font-size: 2em;
            margin-bottom: 20px;
        }
        form {
            background-color: #fff;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        form label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }
        form input, form select, form textarea, form button {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .btn {
            padding: 10px 15px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #0056b3;
        }
    </style>
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
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
        </nav>
    </div>
    <div class="main-content">
        <h1>Edit Achievement Type</h1>
        <form action="" method="POST" enctype="multipart/form-data">
            <h2>Edit Achievement Certificate</h2>
            <label for="type_name">Achievement Type Name:</label>
            <input type="text" id="type_name" name="type_name" value="<?= htmlspecialchars($achievement['AchievementTypeName']); ?>" required>

            <label for="description">Certificate Description:</label>
            <textarea id="description" name="description" rows="4" required><?= htmlspecialchars($achievement['AchievementTypeDescription']); ?></textarea>

            <label for="course">Link to Course:</label>
            <select id="course" name="course_id" required>
                <option value="" disabled>Select Course</option>
                <?php
                $courses = $conn->query("SELECT CourseID, CourseTitle FROM course");
                while ($course = $courses->fetch_assoc()):
                ?>
                    <option value="<?= $course['CourseID']; ?>" <?= $course['CourseID'] === $achievement['CourseID'] ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($course['CourseTitle']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="certificate_image">Current Certificate Image:</label>
            <div class="current-image">
                <img src="<?= htmlspecialchars($achievement['AchievementTypeImage']); ?>" alt="Current Certificate">
            </div>

            <label for="certificate_image">Upload New Certificate Image (Optional):</label>
            <input type="file" id="certificate_image" name="certificate_image" accept="image/*">

            <button type="submit" class="btn">Update Achievement Type</button>
        </form>
    </div>
</body>
</html>
