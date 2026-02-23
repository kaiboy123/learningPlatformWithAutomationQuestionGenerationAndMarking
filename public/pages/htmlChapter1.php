<!DOCTYPE html>
<html lang="en">
<head>
   <?php session_start();?>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>HTML Chapter</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="../css/style.css">
   <style>
      .content {
         background-color: var(--white);
         padding: 2rem;
         margin: 2rem auto;
         border-radius: .5rem;
         box-shadow: 0 0 1rem rgba(0, 0, 0, 0.1);
         max-width: 1200px;
      }
      .chapter-details {
         padding: 20px;
         max-width: 1000px;
         margin: auto;
      }

      /* Style for the heading */
      .chapter-details .heading {
         font-size: 2em;
         margin-bottom: 20px;
      }

      /* Style for the video container */
      .video-container {
         text-align: center;
         margin-bottom: 30px;
      }

      .video-container video {
         width: 100%;
         max-width: 800px; /* Adjust as needed */
         height: auto;
      }

      /* Style for the content section */
      .content {
         font-size: 1.6em; /* Increase font size for better readability */
         line-height: 1.6; /* Improve readability */
         margin-bottom: 30px;
      }

      .content h2 {
         font-size: 1.5em; /* Larger heading size */
         margin-bottom: 10px;
      }

      .content p {
         margin-bottom: 15px;
      }

      .content ul {
         margin-top: 10px;
         margin-left: 20px; /* Adjust indentation */
      }

      .content li {
         margin-bottom: 10px;
      }

      .content h1, .content h2, .content h3 {
         color: var(--black);
         margin-bottom: 1rem;
      }

      .content p {
         color: var(--light-color);
         line-height: 1.6;
         margin-bottom: 1rem;
      }

      .content a {
         color: var(--main-color);
         text-decoration: none;
      }

      .content a:hover {
         text-decoration: underline;
      }

      .related-chapters {
         background-color: var(--light-bg);
         padding: 2rem;
         margin: 2rem auto;
         max-width: 1200px;
         border-radius: 0.5rem;
         box-shadow: 0 0 1rem rgba(0, 0, 0, 0.1);
      }

      /* Heading for the section */
      .next-chapter {
         background-color: var(--light-bg);
         padding: 2rem;
         margin: 2rem auto;
         max-width: 1200px;
         border-radius: 0.5rem;
         box-shadow: 0 0 1rem rgba(0, 0, 0, 0.1);
      }

      .next-chapter .heading {
         font-size: 2.5rem;
         color: var(--black);
         margin-bottom: 2rem;
         text-align: center;
      }

      /* Container for the individual boxes */
      .box-container {
         display: flex;
         flex-wrap: wrap;
         gap: 1.5rem;
         justify-content: center;
      }

      /* Style for each box */
      
      /* Icon inside each box */
      .box i {
         font-size: 2rem;
         color: var(--main-color);
         display: block;
         text-align: center;
         margin-top: 1rem;
      }

      /* Image inside each box */
      .box img {
         width: 100%;
         height: auto;
         display: block;
      }

      /* Title inside each box */
      .box h3 {
         font-size: 1.8rem;
         color: var(--black);
         padding: 1rem;
         text-align: center;
         margin: 0;
         background-color: var(--light-bg);
      }
     
/* Styling for the icon container */
.box-icon {
   background-color: var(--main-color);
   border-radius: 50%;
   padding: 1rem;
   margin: auto;
   width: 60px;
   height: 60px;
   display: flex;
   align-items: center;
   justify-content: center;
   color: var(--white);
   font-size: 2rem;
   margin-bottom: 1rem;
}

.box {
   display: block;
   background-color: var(--white);
   border-radius: 0.5rem;
   box-shadow: 0 0 0.5rem rgba(0, 0, 0, 0.1);
   overflow: hidden;
   width: 300px;
   text-decoration: none;
   color: var(--black);
   transition: transform 0.3s ease, box-shadow 0.3s ease;
   position: relative; /* For positioning the completed indicator */
}

/* Hover effect for boxes */
.box:hover {
   transform: scale(1.05);
   box-shadow: 0 0 1rem rgba(0, 0, 0, 0.2);
}

/* Styling for the completed indicator */
.completed-indicator {
   position: absolute;
   top: 10px;
   right: 10px;
   display: flex;
   align-items: center;
   gap: 0.5rem;
   font-size: 1.2rem;
   color: green;
   font-weight: bold;
}

/* Checkmark icon styling */
.completed-indicator i {
   font-size: 1.5rem;
}

/* Adjustments for the h3 inside the box */
.box h3 {
   margin-top: 1rem;
   font-size: 1.5rem;
   color: var(--black);
}
   </style>
</head>
<body>

<?php
include '../../config/db_connection.php';
$chapter_id = isset($_GET['chapter_id']) ? $_GET['chapter_id'] : 'CH001'; // Default to CH001 if not set

// Check if the learner is logged in
$learnerID = isset($_SESSION['learner']) ? $_SESSION['learner'] : null;

    // Initialize learner data with default values
    $learner = [
        'LearnerName' => 'Learner',
        'ProfileImg' => 'images/emptyprofile.jpg',
    ];

    // Fetch learner data if learner ID is provided
    if ($learnerID) {
        $sql = "SELECT * FROM learner WHERE LearnerID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $learnerID);
        $stmt->execute();
        $result = $stmt->get_result();

        // If data is found, update learner array
        if ($result->num_rows > 0) {
            $learner = $result->fetch_assoc();
        }
    }

// Prepare and execute the SQL query
$sql = "SELECT * FROM coursechapter WHERE CourseChapterID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $chapter_id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch the chapter details
$chapter = $result->fetch_assoc();

$assessmentID = $chapter['AssessmentID'] ?? null;

// Check if the learner has completed this assessment
$isAssessmentCompleted = false;

if ($learnerID && $assessmentID) {
    $query = "SELECT COUNT(*) AS completed_count FROM completedassessment WHERE CompletedLearner = ? AND AssessmentID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $learnerID, $assessmentID);
    $stmt->execute();
    $result = $stmt->get_result();
    $completionData = $result->fetch_assoc();
    $isAssessmentCompleted = $completionData['completed_count'] > 0;
}

$isPracticalCompleted = false;

$practicalTestID = $chapter['PracticalTestID'] ?? null;

if ($learnerID && $practicalTestID) {
    $query = "SELECT COUNT(*) AS completed_count FROM completedpracticaltest WHERE CompletedLearner = ? AND PracticalTestID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $learnerID, $practicalTestID);
    $stmt->execute();
    $result = $stmt->get_result();
    $completionData = $result->fetch_assoc();
    $isPracticalCompleted = $completionData['completed_count'] > 0;
}

$isChapterCompleted = false;



    // If both are completed, mark the chapter as completed
$isChapterCompleted = $isAssessmentCompleted && $isPracticalCompleted;


// Get the next chapter if current chapter is completed
$nextChapter = null;

if ($isChapterCompleted) {
    $query = "SELECT * FROM coursechapter WHERE CourseChapterID > ? ORDER BY CourseChapterID ASC LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $chapter_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $nextChapter = $result->fetch_assoc();
}

$sql = "SELECT coursechapter.*, course.CourseID 
        FROM coursechapter 
        INNER JOIN course ON coursechapter.CourseID = course.CourseID 
        WHERE coursechapter.CourseChapterID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $chapter_id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch the chapter details along with the course ID
$chapter = $result->fetch_assoc();

if ($chapter) {
    $courseID = $chapter['CourseID'];
} else {
    $courseID = null; // Handle the case where no chapter is found
}


?>

<header class="header">
   <section class="flex">
      <a href="home.php" class="logo">Online Learning.</a>

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
         <a href="profile.php" class="btn">View Profile</a>
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
   <img src="<?php echo $learner['ProfileImg']; ?>" class="image" alt="">
   <h3 class="name"><?php echo $learner['LearnerName']; ?></h3>
      <p class="role">Learner</p>
      <a href="profile.php" class="btn">View Profile</a>
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

<section class="chapter-details">
   <h1 class="heading"><?php echo $chapter['CourseChapterTitle']; ?></h1>

   <div class="video-container">
   <video controls style="max-width: 100%; width: 800px; height: 500px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
      <source src="<?php echo $chapter['CourseChapterVideoUrl']; ?>" type="video/mp4">
      Your browser does not support the video tag.
   </video>
</div>

   <div class="content">
      <h2>What is this chapter about?</h2>
      <p><?php echo $chapter['CourseChapterDescription']; ?></p>
      
      <h2>Chapter Content</h2>
      <p><?php echo $chapter['CourseChapterContent']; ?></p>
   </div>
</section>

<section class="next-chapter">
   <h1 class="heading">Answer the Below Question Before Proceed to next chapter!</h1>

   <div class="box-container">
      <!-- Assessment question link -->
      <a class="box" id="assessment-link" 
         href="<?php echo $isAssessmentCompleted ? 'javascript:void(0);' : 'assessment.php?chapter_id=' . htmlspecialchars($chapter_id); ?>" 
         style="<?php echo $isAssessmentCompleted ? 'pointer-events: none;' : ''; ?>">
         <i class="fas fa-question-circle"></i>
         <img src="images/assessment.png" alt="">
         <h3>Assessment Questions</h3>
         <?php if ($isAssessmentCompleted): ?>
            <div class="completed-indicator" style="background-color: lightgreen;">
               <i class="fas fa-check-circle"></i>
               <span>Completed</span>
            </div>
         <?php endif; ?>
      </a>

      <!-- Practical question link -->
      <a class="box" id="practical-link" 
         href="<?php echo $isPracticalCompleted ? 'javascript:void(0);' : 'practical.php?chapter_id=' . htmlspecialchars($chapter_id); ?>" 
         style="<?php echo $isPracticalCompleted ? 'pointer-events: none;' : ''; ?>">
         <i class="fas fa-laptop-code"></i>
         <img src="images/practical.png" alt="">
         <h3>Practical Questions</h3>
         <?php if ($isPracticalCompleted): ?>
            <div class="completed-indicator" style="background-color: lightgreen;">
               <i class="fas fa-check-circle"></i>
               <span>Completed</span>
            </div>
         <?php endif; ?>
      </a>
   </div>
</section>

<?php if ($isChapterCompleted): ?>
<section class="congratulations">
   <h1 class="heading" style="text-align: center;">🎉 Congratulations! 🎉</h1>
   <div style="text-align: center; padding: 20px;">
      <p style="font-size: 1.2rem;">You have successfully completed this chapter.</p>
      <a href="playlist.php?CourseID=<?php echo htmlspecialchars($courseID); ?>" 
         class="btn" 
         style="background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-size: 1rem;width:50%; margin-left:auto; margin-right:auto;">
         Return to Playlist
      </a>
   </div>
</section>
<?php else: ?>
   <a href="playlist.php?CourseID=<?php echo htmlspecialchars($courseID); ?>" 
         class="btn" 
         style="background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-size: 1rem;width:50%; margin-left:auto; margin-right:auto;">
         Return to Playlist
      </a>
<?php endif; ?>

<br><br><br><br><br><br>

<script src="../js/script.js"></script>

<script>
   // Mock function to check if the assessment and practical questions are answered correctly
   function checkIfQuestionsAnsweredCorrectly() {
      // You can replace this logic with actual checks
      return localStorage.getItem('assessmentCompleted') === 'true' && localStorage.getItem('practicalCompleted') === 'true';
   }

   function updateNextChapterLink() {
      const nextChapterLink = document.getElementById('next-chapter-link');
      if (checkIfQuestionsAnsweredCorrectly()) {
         nextChapterLink.style.pointerEvents = 'auto';
         nextChapterLink.style.opacity = '1';
         nextChapterLink.href = '<?php echo 'htmlChapter1.php?chapter_id=' . htmlspecialchars($nextChapter['CourseChapterID']); ?>'; // Link to the next chapter
      }
   }

   // Example usage, you should replace this with actual logic
   // Assuming you have a mechanism to set these values after the user completes the questions
   localStorage.setItem('assessmentCompleted', 'true');
   localStorage.setItem('practicalCompleted', 'true');

   // Check and update the next chapter link on page load
   document.addEventListener('DOMContentLoaded', updateNextChapterLink);
</script>

</body>
</html>