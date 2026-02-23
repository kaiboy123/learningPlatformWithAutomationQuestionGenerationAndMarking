<?php
include '../../config/db_connection.php';
session_start();

// Check admin login
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

// Handle form submission for adding a new achievement type
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $achievementTypeName = $_POST['type_name'];
    $description = $_POST['description'];
    $courseID = $_POST['course_id'];
    $imagePath = '';

    // Upload certificate image
    if (!empty($_FILES['certificate_image']['name'])) {
        $imagePath = 'uploads/' . basename($_FILES['certificate_image']['name']);
        move_uploaded_file($_FILES['certificate_image']['tmp_name'], $imagePath);
    }

    $stmt = $conn->prepare("INSERT INTO achievement_type (AchievementTypeName, AchievementTypeDescription, AchievementTypeImage, CourseID) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $achievementTypeName, $description, $imagePath, $courseID);
    $stmt->execute();
    $stmt->close();
    ?>

<?php ?>
    <script>
        alert('Achievement type created successfully!');
    </script>

<?php
    header('Location: addachievementtype.php');
    exit();
}

// Fetch all achievement types
$result = $conn->query("SELECT at.AchievementTypeID, at.AchievementTypeName, at.AchievementTypeDescription, at.AchievementTypeImage, c.CourseTitle 
                        FROM achievement_type at 
                        JOIN course c ON at.CourseID = c.CourseID");
$achievementTypes = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $achievementTypes[] = $row;
    }
}

// Fetch course options for dropdown
$courseResult = $conn->query("
    SELECT CourseID, CourseTitle 
    FROM course 
    WHERE CourseID NOT IN (SELECT CourseID FROM achievement_type)
");
$courses = [];
if ($courseResult && $courseResult->num_rows > 0) {
    while ($row = $courseResult->fetch_assoc()) {
        $courses[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Achievement Types</title>
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #343a40;
            color: #fff;
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
        .btn-danger {
            background-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
<?php

if (!isset($_SESSION['admin'])) {
    header('Location: login.php'); // Redirect to login page if not logged in
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
      <a href="logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
   </nav>

</div>
<div class="main-content">
    <h1>Manage Achievement Types</h1>
    <form action="addachievementtype.php" method="POST" enctype="multipart/form-data">
        <h2>Add New Achievement Certificate</h2>
        <label for="type_name">Achievement Type Name:</label>
        <input type="text" id="type_name" name="type_name" required>

        <label for="description">Certificate Description:</label>
        <textarea id="description" name="description" rows="4" required></textarea>

        <label for="course">Link to Course:</label>
        <select id="course" name="course_id" required>
            <option value="" disabled selected>Choose a Course</option>
            <?php foreach ($courses as $course): ?>
                <option value="<?= htmlspecialchars($course['CourseID']); ?>"><?= htmlspecialchars($course['CourseTitle']); ?></option>
            <?php endforeach; ?>
        </select>

        <label for="certificate_image">Upload Certificate Image:</label>
        <input type="file" id="certificate_image" name="certificate_image" accept="image/*" required>

        <button type="submit" class="btn">Create Achievement Type</button>
    </form>

    <h2>Existing Achievement Types</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Course</th>
                <th>Certificate</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($achievementTypes as $achievement): ?>
                <tr>
                    <td><?= htmlspecialchars($achievement['AchievementTypeName']); ?></td>
                    <td><?= htmlspecialchars($achievement['AchievementTypeDescription']); ?></td>
                    <td><?= htmlspecialchars($achievement['CourseTitle']); ?></td>
                    <td>
                        <img src="<?= htmlspecialchars($achievement['AchievementTypeImage']); ?>" alt="Certificate" style="width: 100px; height: auto;">
                    </td>
                    <td>
                        <a href="editachievementtype.php?id=<?= $achievement['AchievementTypeID']; ?>" class="btn">Edit</a>
                        <a href="deleteachievementtype.php?id=<?= $achievement['AchievementTypeID']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this achievement type?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>
