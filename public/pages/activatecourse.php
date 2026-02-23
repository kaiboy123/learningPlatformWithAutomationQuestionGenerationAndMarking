<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Active Courses</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
   <!-- custom css file link  -->
   <link rel="stylesheet" href="../css/style.css" type="text/css">

   <style>
       body {
           font-family: Arial, sans-serif;
           background-color: #f4f4f4;
       }

       .courses-container {
           max-width: 1200px;
           margin: 2rem auto;
           padding: 2rem;
           background-color: #fff;
           border-radius: 8px;
           box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
       }

       .courses-header {
           text-align: center;
           margin-bottom: 2rem;
       }

       .courses-header h1 {
           font-size: 3rem;
           color: #333;
       }

       .course-box {
           display: flex;
           align-items: center;
           justify-content: space-between;
           padding: 2rem;
           margin-bottom: 2rem;
           border-radius: 8px;
           box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
           background-color: #f9f9f9;
       }

       .course-info {
           display: flex;
           align-items: center;
           flex: 1;
       }

       .course-info img {
           width: 120px;
           height: 120px;
           border-radius: 50%;
           margin-right: 2rem;
       }

       .course-details h3 {
           font-size: 2rem;
           margin: 0;
       }

       .course-details p {
           margin: 0.5rem 0;
           color: #666;
           font-size: 1.2rem;
       }

       .progress-bar-container {
           width: 100%;
           height: 30px;
           background-color: #ddd;
           border-radius: 15px;
           overflow: hidden;
           margin-top: 1rem;
       }

       .progress-bar {
           height: 100%;
           background-color: #28a745;
           border-radius: 15px;
       }

       .course-actions a {
           display: inline-block;
           padding: 1rem 2rem;
           margin: 1rem;
           color: #fff;
           background-color: #007bff;
           border-radius: 8px;
           text-decoration: none;
           transition: background-color 0.3s;
           font-size: 1.2rem;
       }

       .course-actions a:hover {
           background-color: #0056b3;
       }

       .subscription-info {
           margin-top: 1rem;
           text-align: center;
           flex: 1;
       }

       .subscription-info h4 {
           margin: 0;
           font-size: 1.5rem;
           color: #333;
       }

       .subscription-info p {
           margin: 0.5rem 0;
           color: #666;
           font-size: 1.2rem;
       }

       .subscription-actions a {
           display: inline-block;
           padding: 0.5rem 1rem;
           margin: 0.5rem;
           color: #fff;
           background-color: #17a2b8;
           border-radius: 8px;
           text-decoration: none;
           transition: background-color 0.3s;
           font-size: 1rem;
       }

       .subscription-actions a:hover {
           background-color: #117a8b;
       }
   </style>
</head>
<body>
<?php 
include '../../config/db_connection.php';
session_start();

$learnerID = isset($_SESSION['learner']) ? $_SESSION['learner'] : null; // Check if the learner ID exists

if ($learnerID === null) {
    // Default values when the user is not logged in
    $learner = [
        'LearnerName' => 'Learner',
        'ProfileImg' => 'images/emptyprofile.jpg'
    ];
} else {
    // Query to fetch learner information based on LearnerID
    $sql = "SELECT * FROM learner WHERE LearnerID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $learnerID);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the learner data was found
    if ($result->num_rows > 0) {
        $learner = $result->fetch_assoc();
    } else {
        // Set default values if no learner data is found
        $learner = [
            'LearnerName' => 'Learner',
            'ProfileImg' => 'images/pic-1.jpg'
        ];
    }
    $query = "
    SELECT ps.CourseID, c.CourseTitle, c.CourseDescription, c.CourseTutorName, c.CourseImage, ps.SubscriptionStatus, MAX(ps.SubscriptionExpiredDate) AS SubscriptionExpiredDate
    FROM paymentsubscription ps
    JOIN course c ON ps.CourseID = c.CourseID
    WHERE ps.LearnerID = ? AND ps.SubscriptionStatus = 'active'
    GROUP BY ps.CourseID, c.CourseTitle, c.CourseDescription, c.CourseTutorName, c.CourseImage, ps.SubscriptionStatus
";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $learnerID);
$stmt->execute();
$result = $stmt->get_result();

$activeCourses = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $activeCourses[] = $row;
    }
}
}

?>
<header class="header">
   <section class="flex">
      <a href="home.php" class="logo" style="font-size: large;">Online Learning.</a>
      <form action="search.php" method="post" class="search-form">
         <input type="text" name="search_box" required placeholder="search courses..." maxlength="100">
         <button type="submit" class="fas fa-search"></button>
      </form>
      <div class="icons">
         <div id="menu-btn" class="fas fa-bars"></div>
         <div id="search-btn" class="fas fa-search"></div>
         <div id="user-btn" class="fas fa-user"></div>
         <div id="toggle-btn" class="fas fa-sun"></div>
      </div>
      <div class="profile">
      <img src="<?php
if ($learner['ProfileImg'] === 'ProfileImage/') {
    echo 'images/emptyprofile.jpg';
} else {
    echo $learner['ProfileImg'];
}
?>" class="image" alt="">
         <h3 class="name"><?php echo $learner['LearnerName']; ?></h3>
         <p class="role">Learner</p>
<a href="profile.php" class="btn">view profile</a>
<div class="flex-btn">
    <?php if ($learnerID === null): ?>
        <!-- Show login and register buttons if not logged in -->
        <a href="../../app/controller/login.php" class="option-btn">login</a>
        <a href="../../app/controller/register.php" class="option-btn">register</a>
    <?php else: ?>
        <!-- Show logout button if logged in -->
        <a href="../../app/controller/logout.php" class="option-btn">logout</a>
    <?php endif; ?>
</div>
      </div>
   </section>
</header>   

<div class="side-bar">
   <div id="close-btn">
      <i class="fas fa-times"></i>
   </div>
   <div class="profile">
   <img src="<?php
if ($learner['ProfileImg'] === 'ProfileImage/') {
    echo 'mages/emptyprofile.jpg';
} else {
    echo $learner['ProfileImg'];
}
?>" class="image" alt="">
      <h3 class="name"><?php echo $learner['LearnerName']; ?></h3>
      <p class="role">Learner</p>
      <a href="profile.php" class="btn">view profile</a>
   </div>

 
   <nav class="navbar">
    <a href="home.php"><i class="fas fa-home"></i><span>Home</span></a>
    <?php if ($learnerID): ?>
        <!-- Replace About and Contact Us with Active Courses and Achievements -->
        <a href="activatecourse.php"><i class="fas fa-book"></i><span>Active Courses</span></a>
        <a href="achievement.php"><i class="fas fa-trophy"></i><span>Achievements</span></a>
    <?php else: ?>
        <!-- Default About and Contact Us links -->
        <a href="about.php"><i class="fas fa-question"></i><span>About</span></a>
        <a href="contact.php"><i class="fas fa-headset"></i><span>Contact Us</span></a>
    <?php endif; ?>
    <a href="codeplayground.php"><i class="fas fa-code"></i><span>Code Playground</span></a>
</nav>
</div>

<section class="courses-container">
   <div class="courses-header">
       <h1>Active Courses</h1>
   </div>

   <?php if (!empty($activeCourses)): ?>
       <?php foreach ($activeCourses as $course): ?>
           <div class="course-box">
               <div class="course-info">
                   <img src="<?php echo htmlspecialchars($course['CourseImage']); ?>" alt="Course Image">
                   <div class="course-details">
                       <h3><?php echo htmlspecialchars($course['CourseTitle']); ?></h3>
                       <p><?php echo htmlspecialchars($course['CourseDescription']); ?></p>
                       <p>Instructor: <?php echo htmlspecialchars($course['CourseTutorName']); ?></p>
                       <p>Status: <?php echo htmlspecialchars($course['SubscriptionStatus']); ?></p>
                   </div>
               </div>
               
               <div class="subscription-info">
                   <h4>Subscription Information</h4>
                   <p><strong>Expires On:</strong> <?php echo htmlspecialchars($course['SubscriptionExpiredDate']); ?></p>
                   <div class="subscription-actions">
                        <a href="payment.php?CourseID=<?php echo urlencode($course['CourseID']); ?>">Renew</a>
                        
                    </div>
                    <div class="subscription-actions" style="text-align: center; margin-top: 10px;">
                        <a href="playlist.php?CourseID=<?php echo urlencode($course['CourseID']); ?>" class="btnw" style="width: 120px; padding: 8px 10px; font-size: 0.85rem;">View Course</a>
                    </div>
                    </div>
               </div>
           </div>
       <?php endforeach; ?>
   <?php else: ?>
       <p style="text-align: center; font-size: 1.5rem; color: #666;">No active courses found.</p>
   <?php endif; ?>
</section>


<!-- custom js file link  -->
<script src="../js/script.js"></script>

</body>
</html>