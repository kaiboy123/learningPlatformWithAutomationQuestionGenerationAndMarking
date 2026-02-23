<?php
include '../../config/db_connection.php'; // Include your database connection
session_start();

// Check if admin is logged in
// if (!isset($_SESSION['admin'])) {
//     header('Location: adminlogin.php');
//     exit();
// }

// Get learner ID from the URL
$learnerID = isset($_GET['id']) ? $_GET['id'] : null;
if (!$learnerID) {
    header('Location: ../admin/usermanagement.php');
    exit();
}

// Fetch learner details
$learnerSql = "SELECT * FROM learner WHERE LearnerID = ?";
$stmt = $conn->prepare($learnerSql);
$stmt->bind_param("s", $learnerID);
$stmt->execute();
$learner = $stmt->get_result()->fetch_assoc();

if (!$learner) {
    header('Location: usermanagement.php');
    exit();
}

// Fetch all available courses
$courseSql = "SELECT * FROM course";
$courseResult = $conn->query($courseSql);
$courses = [];
if ($courseResult && $courseResult->num_rows > 0) {
    while ($row = $courseResult->fetch_assoc()) {
        $courses[] = $row;
    }
}

// Fetch courses the learner is registered for
$registeredSql = "
    SELECT CourseID FROM paymentsubscription 
    WHERE LearnerID = ? AND SubscriptionStatus = 'active'";
$stmt = $conn->prepare($registeredSql);
$stmt->bind_param("s", $learnerID);
$stmt->execute();
$registeredResult = $stmt->get_result();
$registeredCourses = [];
if ($registeredResult && $registeredResult->num_rows > 0) {
    while ($row = $registeredResult->fetch_assoc()) {
        $registeredCourses[] = $row['CourseID'];
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedCourses = isset($_POST['courses']) ? $_POST['courses'] : [];

    // Determine courses to deactivate and to activate
    $coursesToDeactivate = array_diff($registeredCourses, $selectedCourses); // Unchecked courses
    $coursesToActivate = array_diff($selectedCourses, $registeredCourses); // Newly checked courses

    // Deactivate unchecked courses
    if (!empty($coursesToDeactivate)) {
        $deactivateSql = "
            UPDATE paymentsubscription 
            SET SubscriptionStatus = 'inactive' 
            WHERE LearnerID = ? AND CourseID = ?";
        $stmt = $conn->prepare($deactivateSql);
        foreach ($coursesToDeactivate as $courseID) {
            $stmt->bind_param("ss", $learnerID, $courseID);
            $stmt->execute();
        }
    }

    // Activate newly checked courses
    if (!empty($coursesToActivate)) {
        foreach ($coursesToActivate as $courseID) {
            // Check if there's an existing inactive subscription
            $checkSql = "
                SELECT * FROM paymentsubscription 
                WHERE LearnerID = ? AND CourseID = ? AND SubscriptionStatus = 'inactive'";
            $stmt = $conn->prepare($checkSql);
            $stmt->bind_param("ss", $learnerID, $courseID);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                // Update existing subscription to active
                $updateSql = "
                    UPDATE paymentsubscription 
                    SET SubscriptionStatus = 'active' 
                    WHERE LearnerID = ? AND CourseID = ?";
                $stmt = $conn->prepare($updateSql);
                $stmt->bind_param("ss", $learnerID, $courseID);
                $stmt->execute();
            } else {
                // Insert a new subscription record
                $insertSql = "
                    INSERT INTO paymentsubscription (SubscriptionID, LearnerID, CourseID, PaymentAmount, PaymentMethod, PaymentStatus, PaymentDate, SubscriptionExpiredDate, SubscriptionStatus) 
                    VALUES (?, ?, ?, 0, 'Admin Update', 'successful', NOW(), NULL, 'active')";
                $subscriptionID = generateSubscriptionID($conn); // Generate a meaningful subscription ID
                $stmt = $conn->prepare($insertSql);
                $stmt->bind_param("sss", $subscriptionID, $learnerID, $courseID);
                $stmt->execute();
            }
        }
    }

    // Redirect back to user management
    header("Location: ../admin/usermanagement.php");
    exit();
}

/**
 * Generate a meaningful SubscriptionID like S00001, S00002, etc.
 */
function generateSubscriptionID($conn) {
    $query = "SELECT MAX(SubscriptionID) AS lastID FROM paymentsubscription";
    $result = $conn->query($query);
    $lastID = $result->fetch_assoc()['lastID'];
    $nextID = intval(substr($lastID, 1)) + 1; // Increment the numeric part
    return 'S' . str_pad($nextID, 5, '0', STR_PAD_LEFT); // Format as S00001
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Edit User</title>

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
         margin-left: 10%;
         padding: 40px;
         width: calc(100% - 270px);
         background-color: #f8f9fa;
      }
      .main-content h1 {
         margin-bottom: 40px;
         font-size: 2.5em;
      }
      .form-container {
         background-color: #fff;
         padding: 20px;
         border-radius: 5px;
         box-shadow: 0 0 10px rgba(0,0,0,0.1);
         max-width: 600px;
         margin: auto;
      }
      .form-container label {
         display: block;
         margin-bottom: 10px;
         font-weight: bold;
      }
      .form-container input[type="text"], .form-container input[type="email"], .form-container select {
         width: 100%;
         padding: 10px;
         margin-bottom: 20px;
         border: 1px solid #ddd;
         border-radius: 5px;
      }
      .form-container .checkbox-container {
         display: flex;
         align-items: center;
         margin-bottom: 10px;
      }
      .form-container .checkbox-container input[type="checkbox"] {
         margin-right: 10px;
      }
      .form-container .btn {
         padding: 10px 20px;
         background-color: #007bff;
         color: #fff;
         border: none;
         border-radius: 5px;
         cursor: pointer;
         text-decoration: none;
         display: inline-block;
         width: 100%;
         text-align: center;
      }
      .form-container .btn:hover {
         background-color: #0056b3;
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
      <a href="../admin/adminhome.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
      <?php if ($adminRole === 'Manager'): ?>
         <a href="../admin/adminmanagement.php"><i class="fas fa-user-shield"></i><span>Manage Admin</span></a>
      <?php endif; ?>
      <a href="../admin/usermanagement.php"><i class="fas fa-users"></i><span>Manage Users</span></a>
      <a href="../admin/coursemanagement.php"><i class="fas fa-book"></i><span>Manage Course</span></a>
      <a href="../admin/subscripmanage.php"><i class="fas fa-cogs"></i><span>Manage Plans</span></a>
      <a href="../admin/manageachievement.php"><i class="fas fa-trophy"></i><span>Manage Cert</span></a>
      <a href="../admin/managefeedback.php"><i class="fas fa-comments"></i><span>Manage Feedback</span></a>
      <a href="../admin/report.php"><i class="fas fa-chart-bar"></i><span>Reports</span></a>
      <a href="../controller/logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
   </nav>

</div>

<div class="main-content">
   <header>
      <h2>Edit User</h2>
   </header>

   <div class="form-container">
   <form method="POST">
      <input type="hidden" name="learnerID" value="<?= htmlspecialchars($learner['LearnerID']); ?>">

      <label for="username">Username</label>
      <input type="text" id="username" name="username" value="<?= htmlspecialchars($learner['LearnerName']); ?>" readonly>

      <label for="email">Email</label>
      <input type="email" id="email" name="email" value="<?= htmlspecialchars($learner['LearnerEmail']); ?>" readonly>

      <label for="courses">Courses</label>
      <?php foreach ($courses as $course): ?>
         <div class="checkbox-container">
            <input 
               type="checkbox" 
               id="course_<?= htmlspecialchars($course['CourseID']); ?>" 
               name="courses[]" 
               value="<?= htmlspecialchars($course['CourseID']); ?>" 
               <?= in_array($course['CourseID'], $registeredCourses) ? 'checked' : ''; ?>
            >
            <label for="course_<?= htmlspecialchars($course['CourseID']); ?>"><?= htmlspecialchars($course['CourseTitle']); ?></label>
         </div>
      <?php endforeach; ?>

      <button type="submit" class="btn">Save Changes</button>
   </form>
   
</div>
<a href="javascript:history.back()" class="btn" style="margin-top: 20px; display: inline-block; text-align: center;">Back</a>
</div>

</body>
</html>
