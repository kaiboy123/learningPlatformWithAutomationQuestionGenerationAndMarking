<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>course playlist</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="../css/style.css">
<style>
   .user-feedback {
      margin: 2rem 0;
      padding: 1rem;
      background-color: var(--white);
      border-radius: 0.5rem;
      box-shadow: 0 0 1rem rgba(0, 0, 0, 0.1);
      margin-right: auto;
      margin-left: auto;
   }

   .feedback-container {
      display: flex;
      flex-direction: column;
      gap: 1.5rem;
      margin-top: 1rem;
   }

   .feedback-card {
      padding: 1.5rem;
      background-color: var(--white);
      border-radius: 0.8rem;
      box-shadow: 0 0 1rem rgba(0, 0, 0, 0.1);
   }

   .feedback-header {
      display: flex;
      align-items: center;
      gap: 1.5rem;
      margin-bottom: 1rem;
   }

   .profile-image {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      object-fit: cover;
      box-shadow: 0 0 1rem rgba(0, 0, 0, 0.2);
   }

   .feedback-header h3 {
      margin: 0;
      font-size: 1.8rem;
      color: var(--black);
   }

   .feedback-date {
      font-size: 1.2rem;
      color: var(--light-color);
   }

   .feedback-card p {
      font-size: 1.5rem;
      color: var(--black);
      margin: 1rem 0;
   }

   .feedback-card .fas,
   .feedback-card .far {
      font-size: 1.8rem;
   }
</style>
</head>
<body>
<?php
include '../../config/db_connection.php'; // Include your database connection
session_start();
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

// Get course ID from the URL
 if(isset($_GET['CourseID'])){
   $course_id = $_GET['CourseID'];
}

// Fetch course details
$course_query = $conn->prepare("SELECT * FROM course WHERE CourseID = ?");
$course_query->bind_param("s",$course_id);
$course_query->execute();
$course_result = $course_query->get_result();
$course = $course_result->fetch_assoc();

// Fetch chapters for this course
$chapter_query = $conn->prepare("SELECT * FROM coursechapter WHERE CourseID = ?");
$chapter_query->bind_param("s", $course_id); 
$chapter_query->execute();
$chapter_result = $chapter_query->get_result();

$chapter_count_query = $conn->prepare("SELECT COUNT(*) as chapter_count FROM coursechapter WHERE CourseID = ?");
$chapter_count_query->bind_param("s", $course_id);
$chapter_count_query->execute();
$chapter_count_result = $chapter_count_query->get_result();
$chapter_count_data = $chapter_count_result->fetch_assoc();
$chapter_count = $chapter_count_data['chapter_count'];

   $query = "SELECT COUNT(DISTINCT cc.CourseChapterID) AS completed_chapters
             FROM coursechapter cc
             LEFT JOIN completedassessment ca 
                 ON cc.AssessmentID = ca.AssessmentID AND ca.CompletedLearner = ?
             LEFT JOIN completedpracticaltest cp 
                 ON cc.PracticalTestID = cp.PracticalTestID AND cp.CompletedLearner = ?
             WHERE cc.CourseID = ?
               AND (ca.AssessmentID IS NOT NULL OR cc.AssessmentID IS NULL)
               AND (cp.PracticalTestID IS NOT NULL OR cc.PracticalTestID IS NULL)";
   

$stmt = $conn->prepare($query);
$stmt->bind_param("sss", $learnerID, $learnerID, $course_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

$completedChapters = $data['completed_chapters'] ?? 0;


$ratingQuery = "SELECT AVG(Rating) AS average_rating, COUNT(*) AS response_count
                FROM course_feedback
                WHERE CourseID = ?";
$stmt = $conn->prepare($ratingQuery);
$stmt->bind_param("s", $course_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

$averageRating = round($data['average_rating'] * 2) / 2;
$responseCount = $data['response_count'] ?? 0;

$feedbackQuery = "SELECT cf.Comment, cf.Rating, cf.FeedbackDate, l.LearnerName, l.ProfileImg
                  FROM course_feedback cf
                  JOIN learner l ON cf.LearnerID = l.LearnerID
                  WHERE cf.CourseID = ?";
$stmt = $conn->prepare($feedbackQuery);
$stmt->bind_param("s", $course_id);
$stmt->execute();
$feedbackResult = $stmt->get_result();
$stmt->close();
?>
<header class="header">
   
   <section class="flex">

      <a href="home.php" class="logo">Online Learning. </a>

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

<?php

function isSubscribed($learnerID, $courseID, $conn) {
   $currentDate = date('Y-m-d');
   $query = "SELECT * FROM paymentsubscription 
             WHERE LearnerID = ? AND CourseID = ? AND SubscriptionStatus = 'active' 
             AND SubscriptionExpiredDate >= ?";
   $stmt = $conn->prepare($query);
   $stmt->bind_param("sss", $learnerID, $courseID, $currentDate);
   $stmt->execute();
   $result = $stmt->get_result();

   return $result->num_rows > 0; // Returns true if the user has an active subscription
}

$isUserSubscribed = isSubscribed($learnerID, $course_id, $conn);

// Fetch course rating and response count
$ratingQuery = "SELECT AVG(Rating) AS average_rating, COUNT(*) AS response_count
                FROM course_feedback
                WHERE CourseID = ?";
$stmt = $conn->prepare($ratingQuery);
$stmt->bind_param("s", $course_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

$averageRating = round($data['average_rating'] * 2) / 2; // Round to nearest 0.5
$responseCount = $data['response_count'] ?? 0;
?>

<section class="playlist-details">
   <h1 class="heading">Course Details</h1>

   <div class="row">
      <div class="column">
         <div class="thumb">
            <img src="<?php echo $course['CourseImage']; ?>" alt="">
            <span><?php echo $chapter_count; ?> chapters</span>
         </div>
      </div>
      <div class="column">
         <div class="details">
            <h3><?php echo $course['CourseTitle']; ?></h3>
            <p><?php echo $course['CourseDescription']; ?></p>
            <div class="rating">
               <h3>
                  <?php for ($i = 0; $i < 5; $i++): ?>
                     <?php if ($i < floor($averageRating)): ?>
                        <i class="fas fa-star" style="color: gold; font-size: 3rem;"></i>
                     <?php elseif ($i < ceil($averageRating)): ?>
                        <i class="fas fa-star-half-alt" style="color: gold; font-size: 3rem;"></i>
                     <?php else: ?>
                        <i class="far fa-star" style="color: lightgray; font-size: 3rem;"></i>
                     <?php endif; ?>
                  <?php endfor; ?>
                  <span style="font-size: 1.2rem; color: gray; margin-left: 10px;">
                     (<?php echo $responseCount; ?> responses)
                  </span>
               </h3>
            </div>
         </div>
         <?php if ($isUserSubscribed): ?>
    <?php if ($completedChapters >= 3): ?>
        <a href="rate.php?CourseID=<?php echo htmlspecialchars($course_id); ?>" class="btn" 
           style="background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; width:40%;">
           Rate This Course
        </a>
    <?php else: ?>
        <p style="font-size: 1rem; color: gray; margin-top: 10px;">
            Complete at least <strong>3 chapters</strong> to unlock the rating feature and share your feedback with us!
        </p>
    <?php endif; ?>
    <?php if ($completedChapters == $chapter_count): ?>
                <a href="get_achievement.php?CourseID=<?php echo htmlspecialchars($course_id); ?>" class="btn" 
                   style="background-color: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; width: 40%;">
                   Get Achievement
                </a>
                <?php else: ?>
        <p style="font-size: 1rem; color: gray; margin-top: 10px;">
            You will unlock the achievement once complete whole chapter!
        </p>
            <?php endif; ?>
            
<?php else: ?>
    <a href="payment.php?CourseID=<?php echo htmlspecialchars($course_id); ?>" class="btn" 
       style="background-color: #ff4d4d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; width:40%;">
       Subscribe Now
    </a>
    <p style="font-size: 1rem; color: gray; margin-top: 10px;">
        Subscribe to unlock access to course chapters.
    </p>
<?php endif; ?>
      </div>
   </div>
</section>

<section class="playlist-videos">
   <h1 class="heading">Course Chapters</h1>
   <div class="box-container">
      <?php 
      $previousCompleted = true; // Start with the first chapter unlocked

      if ($chapter_result->num_rows > 0) { 
         while ($chapter = $chapter_result->fetch_assoc()) { 
            // Check if the current chapter's assessment and practical are completed
            $assessmentCompleted = true;
            $practicalCompleted = true;

            if (!empty($chapter['AssessmentID'])) {
               $assessment_query = $conn->prepare("
                  SELECT COUNT(*) AS CompletedCount
                  FROM completedassessment 
                  WHERE AssessmentID = ? AND CompletedLearner = ?
               ");
               $assessment_query->bind_param("ss", $chapter['AssessmentID'], $learnerID);
               $assessment_query->execute();
               $assessment_result = $assessment_query->get_result()->fetch_assoc();
               $assessmentCompleted = $assessment_result['CompletedCount'] > 0;
            }

            if (!empty($chapter['PracticalTestID'])) {
               $practical_query = $conn->prepare("
                  SELECT COUNT(*) AS CompletedCount
                  FROM completedpracticaltest 
                  WHERE PracticalTestID = ? AND CompletedLearner = ?
               ");
               $practical_query->bind_param("ss", $chapter['PracticalTestID'], $learnerID);
               $practical_query->execute();
               $practical_result = $practical_query->get_result()->fetch_assoc();
               $practicalCompleted = $practical_result['CompletedCount'] > 0;
            }

            // Determine if the current chapter should be locked
            $isLocked = !$isUserSubscribed || !$previousCompleted;

            if ($isLocked) {
               $boxClass = "box locked";
               $lockIcon = '<i class="fas fa-lock"></i>';
               $href = "javascript:void(0);"; // Disabled link for locked chapters
               $boxStyle = "opacity: 0.5; background-color: #f9f9f9;"; // Light white background
            } else {
               $boxClass = "box";
               $lockIcon = '<i class="fas fa-play"></i>';
               $href = "htmlChapter1.php?chapter_id=" . $chapter['CourseChapterID']; // Active link for unlocked chapters
               $boxStyle = ""; // Default style
            }

            // Render the chapter
            echo "
            <a class='$boxClass' href='$href' style='$boxStyle'>
               $lockIcon
               <img src='{$chapter['CourseChapterImage']}' alt='' style='opacity: " . ($isLocked ? "0.5" : "1") . ";'>
               <h3>{$chapter['CourseChapterTitle']}</h3>
            </a>
            ";

            // Update the `previousCompleted` status for the next iteration
            $previousCompleted = $assessmentCompleted && $practicalCompleted;
         }
      } else { 
         echo "<p>No chapters found for this course.</p>";
      } 
      ?>
   </div>
</section>


<section class="user-feedback">
   <h1 class="heading">
      User Feedback
      <button onclick="toggleFeedback()" id="toggle-feedback-btn" class="btn"
         style="background-color: #007bff; color: white; padding: 5px 15px; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px;">
         Show Feedback
      </button>
   </h1>
   <div class="feedback-container" id="feedback-section" style="display: none;">
      <?php if ($feedbackResult->num_rows > 0): ?>
         <?php while ($feedback = $feedbackResult->fetch_assoc()): ?>
            <div class="feedback-card">
               <div class="feedback-header">
                  <img src="<?php echo htmlspecialchars($feedback['ProfileImg']); ?>" alt="Profile" class="profile-image">
                  <div>
                     <h3><?php echo htmlspecialchars($feedback['LearnerName']); ?></h3>
                     <p class="feedback-date"><?php echo date("F j, Y", strtotime($feedback['FeedbackDate'])); ?></p>
                  </div>
               </div>
               <p>
                  <?php for ($i = 0; $i < 5; $i++): ?>
                     <?php if ($i < $feedback['Rating']): ?>
                        <i class="fas fa-star" style="color: gold; font-size: 2rem;"></i>
                     <?php else: ?>
                        <i class="far fa-star" style="color: lightgray; font-size: 2rem;"></i>
                     <?php endif; ?>
                  <?php endfor; ?>
               </p>
               <p style="font-size: 1.5rem; color: var(--black);">
                  <?php echo htmlspecialchars($feedback['Comment']); ?>
               </p>
            </div>
         <?php endwhile; ?>
      <?php else: ?>
         <p>No feedback available for this course yet.</p>
      <?php endif; ?>
   </div>
</section>

<script>
   function toggleFeedback() {
      const feedbackSection = document.getElementById("feedback-section");
      const toggleButton = document.getElementById("toggle-feedback-btn");

      if (feedbackSection.style.display === "none") {
         feedbackSection.style.display = "block";
         toggleButton.innerText = "Hide Feedback";
      } else {
         feedbackSection.style.display = "none";
         toggleButton.innerText = "Show Feedback";
      }
   }
</script>

<!-- custom js file link  -->
<script src="js/script.js"></script>

   
</body>
</html>