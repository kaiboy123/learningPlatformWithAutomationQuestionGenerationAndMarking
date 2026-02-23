<?php
include '../../config/db_connection.php';
session_start();

$learnerID = $_SESSION['learner'] ?? null;

if (!$learnerID) {
    header('Location: ../../app/controller/login.php');
    exit();
}

// Fetch learner details
$stmt = $conn->prepare("SELECT * FROM learner WHERE LearnerID = ?");
$stmt->bind_param("s", $learnerID);
$stmt->execute();
$learner = $stmt->get_result()->fetch_assoc();

// Fetch achievements
$stmt = $conn->prepare("
    SELECT at.AchievementTypeName AS AchievementTitle, 
           at.AchievementTypeDescription AS AchievementDescription, 
           a.AchievementDate, 
           at.AchievementTypeImage AS AchievementImage, 
           c.CourseTitle
    FROM achievements a
    JOIN achievement_type at ON a.AchievementTypeID = at.AchievementTypeID
    JOIN course c ON at.CourseID = c.CourseID
    WHERE a.LearnerID = ?
    ORDER BY a.AchievementDate DESC
");
$stmt->bind_param("s", $learnerID);
$stmt->execute();
$result = $stmt->get_result();
$achievements = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Achievements</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
   <!-- custom css file link  -->
   <link rel="stylesheet" href="../css/style.css" type="text/css">

   <style>
       body {
           font-family: Arial, sans-serif;
           background-color: #f4f4f4;
       }

       .achievements-container {
           max-width: 1200px;
           margin: 2rem auto;
           padding: 2rem;
           background-color: #fff;
           border-radius: 8px;
           box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
       }

       .achievements-header {
           text-align: center;
           margin-bottom: 2rem;
       }

       .achievements-header h1 {
           font-size: 3rem;
           color: #333;
       }

       .achievement-box {
           display: flex;
           justify-content: space-between;
           align-items: center;
           padding: 2rem;
           margin-bottom: 2rem;
           border-radius: 8px;
           box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
           background-color: #f9f9f9;
       }

       .achievement-info {
           display: flex;
           align-items: center;
       }

       .achievement-info img {
           width: 120px;
           height: 120px;
           border-radius: 50%;
           margin-right: 2rem;
       }

       .achievement-details h3 {
           font-size: 2rem;
           margin: 0;
       }

       .achievement-details p {
           margin: 0.5rem 0;
           color: #666;
           font-size: 1.2rem;
       }

       .achievement-actions a {
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

       .achievement-actions a:hover {
           background-color: #0056b3;
       }
   </style>
</head>
<body>

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
      <img src="<?php echo $learner['ProfileImg']; ?>" class="image" alt="">
      <h3 class="name"><?php echo $learner['LearnerName']; ?></h3>
         <p class="role">Learner</p>
         <a href="profile.php" class="btn">view profile</a>
         <div class="flex-btn">
            <a href="../../app/controller/login.php" class="option-btn">login</a>
            <a href="../../app/controller/register.php" class="option-btn">register</a>
         </div>
      </div>
   </section>
</header>   

<div class="side-bar">
   <div id="close-btn">
      <i class="fas fa-times"></i>
   </div>
   <div class="profile">
   <img src="<?php echo $learner['ProfileImg']; ?>" class="image" alt="">
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

<section class="achievements-container">
   <div class="achievements-header">
       <h1>My Achievements</h1>
   </div>
   <?php if (!empty($achievements)): ?>
       <?php foreach ($achievements as $achievement): ?>
           <div class="achievement-box">
               <div class="achievement-info">
                   <img src="<?php echo htmlspecialchars($achievement['AchievementImage']); ?>" alt="Achievement">
                   <div class="achievement-details">
                       <h3><?php echo htmlspecialchars($achievement['AchievementTitle']); ?></h3>
                       <p>Date Earned: <?php echo htmlspecialchars(date("F j, Y", strtotime($achievement['AchievementDate']))); ?></p>
                       <p><?php echo htmlspecialchars($achievement['AchievementDescription']); ?></p>
                       <p>Course: <?php echo htmlspecialchars($achievement['CourseTitle']); ?></p>
                   </div>
               </div>
           </div>
       <?php endforeach; ?>
   <?php else: ?>
       <p style="text-align: center; font-size: 1.5rem; color: #666;">No achievements found.</p>
   <?php endif; ?>
</section>

<!-- custom js file link  -->
<script src="../js/script.js"></script>

</body>
</html>