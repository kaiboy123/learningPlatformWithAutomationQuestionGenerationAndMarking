<!DOCTYPE html>
<html lang="en">
<head>
   <?php session_start();?>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>home</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
   <!-- custom css file link  -->
   <link rel="stylesheet" href="../css/style.css" type="text/css">
   <link rel="stylesheet" href="../css/home.css" type="text/css">
   <style>
     
   </style>
</head>
<body>
<?php 
   include '../../config/db_connection.php';

   
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
   }
   $activeCoursesQuery = "
    SELECT ps.CourseID, c.CourseTitle, c.CourseImage, COUNT(cc.CourseChapterID) AS total_chapters
    FROM paymentsubscription ps
    JOIN course c ON ps.CourseID = c.CourseID
    JOIN coursechapter cc ON c.CourseID = cc.CourseID
    WHERE ps.LearnerID = ? AND ps.SubscriptionStatus = 'active' AND ps.SubscriptionExpiredDate >= CURDATE()
    GROUP BY ps.CourseID
";
$stmt = $conn->prepare($activeCoursesQuery);
$stmt->bind_param("s", $learnerID);
$stmt->execute();
$activeCoursesResult = $stmt->get_result();

$activeCourses = [];
while ($row = $activeCoursesResult->fetch_assoc()) {
    // Calculate completed chapters for each course
    $completedChaptersQuery = "
        SELECT COUNT(cc.CourseChapterID) AS completed_chapters
        FROM coursechapter cc
        LEFT JOIN completedassessment ca ON cc.AssessmentID = ca.AssessmentID AND ca.CompletedLearner = ?
        LEFT JOIN completedpracticaltest cp ON cc.PracticalTestID = cp.PracticalTestID AND cp.CompletedLearner = ?
        WHERE cc.CourseID = ? AND ca.AssessmentID IS NOT NULL AND cp.PracticalTestID IS NOT NULL
    ";
    $chapterStmt = $conn->prepare($completedChaptersQuery);
    $chapterStmt->bind_param("sss", $learnerID, $learnerID, $row['CourseID']);
    $chapterStmt->execute();
    $chapterResult = $chapterStmt->get_result();
    $completedChapters = $chapterResult->fetch_assoc()['completed_chapters'];

    // Calculate progress percentage
    $progressPercentage = ($row['total_chapters'] > 0) 
        ? ($completedChapters / $row['total_chapters']) * 100 
        : 0;

    // Add to active courses
    $activeCourses[] = [
        'CourseID' => $row['CourseID'],
        'CourseTitle' => $row['CourseTitle'],
        'CourseImage' => $row['CourseImage'],
        'progress' => $progressPercentage,
    ];
}

// Count active courses
$totalActiveCourses = count($activeCourses);

if ($learnerID) {
    // Query to fetch completed courses for the specific learner
    $completedCoursesQuery = "
        SELECT c.CourseID
        FROM course c
        JOIN coursechapter cc ON c.CourseID = cc.CourseID
        LEFT JOIN completedassessment ca 
            ON cc.AssessmentID = ca.AssessmentID AND ca.CompletedLearner = ?
        LEFT JOIN completedpracticaltest cp 
            ON cc.PracticalTestID = cp.PracticalTestID AND cp.CompletedLearner = ?
        WHERE c.CourseID IN (
            SELECT cc.CourseID
            FROM coursechapter cc
            LEFT JOIN completedassessment ca 
                ON cc.AssessmentID = ca.AssessmentID AND ca.CompletedLearner = ?
            LEFT JOIN completedpracticaltest cp 
                ON cc.PracticalTestID = cp.PracticalTestID AND cp.CompletedLearner = ?
            GROUP BY cc.CourseID
            HAVING COUNT(cc.CourseChapterID) = 
                   SUM((ca.AssessmentID IS NOT NULL OR cc.AssessmentID IS NULL) 
                       AND (cp.PracticalTestID IS NOT NULL OR cc.PracticalTestID IS NULL))
        )
        GROUP BY c.CourseID;
    ";

    $stmt = $conn->prepare($completedCoursesQuery);
    $stmt->bind_param("ssss", $learnerID, $learnerID, $learnerID, $learnerID);
    $stmt->execute();
    $completedCoursesResult = $stmt->get_result();

    // Calculate the number of completed courses
    $completedCoursesCount = 0; // Default to zero
    while ($course = $completedCoursesResult->fetch_assoc()) {
        // Verify that all chapters are fully completed
        $chapterCheckQuery = "
            SELECT COUNT(cc.CourseChapterID) AS total_chapters,
                   SUM((ca.AssessmentID IS NOT NULL OR cc.AssessmentID IS NULL) 
                       AND (cp.PracticalTestID IS NOT NULL OR cc.PracticalTestID IS NULL)) AS completed_chapters
            FROM coursechapter cc
            LEFT JOIN completedassessment ca 
                ON cc.AssessmentID = ca.AssessmentID AND ca.CompletedLearner = ?
            LEFT JOIN completedpracticaltest cp 
                ON cc.PracticalTestID = cp.PracticalTestID AND cp.CompletedLearner = ?
            WHERE cc.CourseID = ?
        ";

        $chapterStmt = $conn->prepare($chapterCheckQuery);
        $chapterStmt->bind_param("sss", $learnerID, $learnerID, $course['CourseID']);
        $chapterStmt->execute();
        $chapterResult = $chapterStmt->get_result()->fetch_assoc();

        if ((int)$chapterResult['total_chapters'] === (int)$chapterResult['completed_chapters']) {
            $completedCoursesCount++;
        }
    }

    // Count Achievements
    $achievementsQuery = "SELECT COUNT(*) AS achievements_count FROM achievements WHERE LearnerID = ?";
    $stmt = $conn->prepare($achievementsQuery);
    $stmt->bind_param("s", $learnerID);
    $stmt->execute();
    $achievementsResult = $stmt->get_result();
    $achievementsCount = $achievementsResult->fetch_assoc()['achievements_count'] ?? 0;
} else {
    $completedCoursesCount = 0;
    $achievementsCount = 0;
}
?>
<header class="header">
   <section class="flex">
      <a href="about.php" class="logo" style="font-size:large;">Online Learning.</a>

      <form action="search.html" method="post" class="search-form">
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
        <?php if ($learnerID === null): ?>
            <div class="flex-btn">
            <!-- Show login and register buttons if not logged in -->
            <a href="../../app/controller/login.php" class="option-btn">login</a>
            <a href="../../app/controller/register.php" class="option-btn">register</a>
            </div>
        <?php else: ?>
            <a href="profile.php" class="btn">view profile</a>
            <!-- Show logout button if logged in -->
            <div class="flex-btn">
            <a href="../../app/controller/logout.php" class="option-btn">logout</a>
            </div>
        <?php endif; ?>

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
    echo 'images/emptyprofile.jpg';
} else {
    echo $learner['ProfileImg'];
}
?>" class="image" alt="">
      <h3 class="name"><?php echo $learner['LearnerName']; ?></h3>
      <p class="role">Learner</p>
      <?php if ($learnerID === null): ?>
       <!-- Show login button if not logged in -->
       <a href="../../app/controller/login.php" class="btn">login</a>
       
   <?php else: ?>
       <!-- Show view profile button if logged in -->
       <a href="profile.php" class="btn">view profile</a>
   <?php endif; ?>
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


<section class="home-grid">
    <div class="grid-container">
        <!-- Personal Statistics -->
        <div class="personal-statistics">
    <h3 class="title">Personal Statistics</h3>
    <?php if ($learnerID === null): ?>
        <p style="font-size: 1.6rem;">Log in to view your personal statistics, track your progress, and access achievements.</p>
        <div class="flex-btn">
            <a href="../../app/controller/login.php" class="inline-btn">Login</a>
            <a href="../../app/controller/register.php" class="inline-btn">Register</a>
        </div>
    <?php else: ?>
        <!-- <p class="likes">Completed Courses: <span><?php echo $completedCoursesCount; ?></span></p> -->
        <h3 class="title" style="margin-top:5%;">Course Progress</h3>
        <?php foreach ($activeCourses as $course): ?>
            <p style="font-size:13px;"><?php echo htmlspecialchars($course['CourseTitle']); ?></p>
            <div class="progress-bar">
                <div class="progress" style="width: <?php echo round($course['progress'], 2); ?>%;"></div>
            </div>
        <?php endforeach; ?>
        <p class="likes">Active Courses: <span><?php echo $totalActiveCourses; ?></span></p>
        <a href="activatecourse.php" class="inline-btn">View Active Courses</a>
        <p class="likes">Achievements: <span><?php echo $achievementsCount; ?></span></p>
        <a href="achievement.php" class="inline-btn">View Achievements</a>
    <?php endif; ?>
</div>

        <!-- Welcome Section -->
        <div class="welcome-section">
    <h1 class="heading">Welcome</h1>
    <div class="animation-container">
        <div class="cartoon-kid">
            <img src="images/wave.png" alt="Cartoon Kid Waving" class="animated-kid">
        </div>
        <div class="greeting">
            <?php if ($learnerID === null): ?>
                <h2>Welcome to Online Learning!</h2>
                <p>Join us today and start your learning journey. Log in or register to explore our courses and track your progress!</p>
                <div class="flex-btn">
                    <a href="../../app/controller/login.php" class="inline-btn">Login</a>
                    <a href="../../app/controller/register.php" class="inline-btn">Register</a>
                </div>
            <?php else: ?>
                <h2>Hi, <?= $learner['LearnerName']; ?>!</h2>
                <p>Welcome back to your learning journey. Let's explore new courses today!</p>
            <?php endif; ?>
        </div>
    </div>
</div>
    </div>
</section>
<?php 
   include '../../config/db_connection.php';

   $sql = "SELECT c.CourseID, c.CourseImage, c.CourseTutorName, c.CourseTitle, COUNT(cc.CourseChapterID) AS chapter_count
        FROM course c, coursechapter cc
        WHERE c.CourseID = cc.CourseID
        GROUP BY c.CourseID";

$result = $conn->query($sql);

   ?>
<section class="courses">
   <h1 class="heading">Our Courses</h1>
   <div class="box-container">
      <?php
      if ($result->num_rows > 0) {
         while ($row = $result->fetch_assoc()) {
            ?>
            <div class="box">
               <div class="tutor">
                  <img src="<?= $row['CourseImage']; ?>" alt="">
                  <!-- <div class="info">
                     <h3><?= $row['CourseTutorName']; ?></h3>
                     <span><?= $row['CourseTutorName']; ?></span>
                  </div> -->
               </div>
               <div class="thumb">
                  <img src="<?= $row['CourseImage']; ?>" alt="">
                  <span><?= $row['chapter_count']; ?> chapters</span>
               </div>
               <h3 class="title"><?= $row['CourseTitle']; ?></h3>
               <!-- Show the "View Course" button only for subscribed users -->
               <?php if ($learnerID): ?>
                  <a href="playlist.php?CourseID=<?= $row['CourseID']; ?>" class="inline-btn">View Course</a>
               <?php else: ?>
                <a href="playlist.php?CourseID=<?= $row['CourseID']; ?>" class="inline-btn">View Course</a>
               <?php endif; ?>
            </div>
            <?php
         }
      } else {
         echo "<p>No courses found.</p>";
      }
      ?>
   </div>
</section>
<div id="login-dialog" class="dialog">
   <div class="dialog-content">
       <div class="dialog-header">
           <h2>Login Required</h2>
       </div>
       <div class="dialog-body">
           <p>You need to log in to subscribe to a course.</p>
       </div>
       <div class="dialog-footer">
           <a href="login.php" class="inline-btn">Login</a>
           <button onclick="closeLoginDialog()" class="inline-btn">Close</button>
       </div>
   </div>
</div>
<!-- Pop-up dialog for the next webinar -->
<div id="webinar-dialog" class="dialog">
   <div class="dialog-content">
       <div class="dialog-header">
           <h2>Next Webinar</h2>
       </div>
       <div class="dialog-body">
           <p>Join us for our next webinar on Advanced JavaScript Techniques on July 30, 2024. Learn the latest trends and improve your coding skills!</p>
       </div>
       <div class="dialog-footer">
           <button id="close-webinar-dialog">Close</button>
       </div>
   </div>
</div>

<!-- Pop-up dialog for the next course -->
<div id="course-dialog" class="dialog">
   <div class="dialog-content">
       <div class="dialog-header">
           <h2>Upcoming Course Launch</h2>
       </div>
       <div class="dialog-body">
           <p>We are excited to announce the launch of our new course on Full Stack Web Development on August 15, 2024. Enroll now to secure your spot!</p>
       </div>
       <div class="dialog-footer">
           <button id="close-course-dialog">Close</button>
       </div>
   </div>
</div>

<!-- custom js file link  -->
<script>
    // Ensure you target all subscribe buttons
    document.querySelectorAll('.subscribe-btn').forEach(button => {
        button.addEventListener('click', function () {
            const courseTitle = this.getAttribute('data-course-title');
            const courseId = this.getAttribute('data-course-id');
            const courseImage = this.getAttribute('data-course-image');
            const courseDescription = this.getAttribute('data-course-description');
            const courseTutor = this.getAttribute('data-course-tutor');
            const chapterCount = this.getAttribute('data-chapter-count');

            // Update dialog content with course details
            document.getElementById('course-title-unique').innerText = courseTitle;
            document.getElementById('course-image-unique').src = courseImage;
            document.getElementById('course-description-unique').innerText = courseDescription;
            document.getElementById('course-tutor-unique').innerText = courseTutor;
            document.getElementById('chapter-count-unique').innerText = chapterCount;
            document.getElementById('subscribe-link-unique').href = `payment.php?CourseID=${courseId}`;

            // Show the dialog
            document.getElementById('subscribe-dialog-unique').style.display = 'block';
        });
    });

    // Close the subscription dialog
    document.getElementById('close-dialog-unique').addEventListener('click', function () {
        document.getElementById('subscribe-dialog-unique').style.display = 'none';
    });

    // Close dialog if clicked outside of the dialog content
    window.addEventListener('click', function (event) {
        const dialog = document.getElementById('subscribe-dialog-unique');
        if (event.target === dialog) {
            dialog.style.display = 'none';
        }
    });
</script>
<script src="../js/script.js"></script>

<script>
    function showLoginDialog() {
       document.getElementById('login-dialog').style.display = 'block';
   }

   function closeLoginDialog() {
       document.getElementById('login-dialog').style.display = 'none';
   }

   window.onclick = function(event) {
       const dialog = document.getElementById('login-dialog');
       if (event.target === dialog) {
           dialog.style.display = 'none';
       }
   }
   // Get the dialogs
   var webinarDialog = document.getElementById("webinar-dialog");
   var courseDialog = document.getElementById("course-dialog");

   // Get the buttons that open the dialogs
   var webinarBtn = document.getElementById("webinar-btn");
   var courseBtn = document.getElementById("course-btn");

   // Get the <span> elements that close the dialogs
   var closeWebinarDialog = document.getElementById("close-webinar-dialog");
   var closeCourseDialog = document.getElementById("close-course-dialog");

   // When the user clicks the buttons, open the respective dialogs
   webinarBtn.onclick = function() {
       webinarDialog.style.display = "block";
   }

   courseBtn.onclick = function() {
       courseDialog.style.display = "block";
   }

   // When the user clicks on <span> (x), close the respective dialogs
   closeWebinarDialog.onclick = function() {
       webinarDialog.style.display = "none";
   }

   closeCourseDialog.onclick = function() {
       courseDialog.style.display = "none";
   }

   // When the user clicks anywhere outside of the dialog, close it
   window.onclick = function(event) {
       if (event.target == webinarDialog) {
           webinarDialog.style.display = "none";
       }
       if (event.target == courseDialog) {
           courseDialog.style.display = "none";
       }
   }
</script>

</body>
</html>