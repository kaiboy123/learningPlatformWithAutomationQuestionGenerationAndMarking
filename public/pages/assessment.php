<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Assessment - HTML Chapter 1</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="../css/style.css">
   <style>
     .assessment-container {
         background-color: #f9f9f9;
         padding: 2rem;
         margin: 2rem auto;
         border-radius: 1rem;
         box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
         max-width: 1000px;
         text-align: left;
      }

      /* Styling for the heading */
      .assessment-container h1 {
         font-size: 3rem;
         color: #333;
         margin-bottom: 2rem;
         font-weight: bold;
         border-bottom: 3px solid #f39c12;
         display: inline-block;
         padding-bottom: 0.5rem;
      }

      /* Question container */
      .assessment-form .question {
         margin-bottom: 2rem;
         padding: 1rem;
         border: 1px solid #ddd;
         border-radius: 0.5rem;
         background-color: #fff;
         transition: all 0.3s ease;
         display: block;
      }

      /* Active question */
      .assessment-form .question.active {
         box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      }

      /* Question label */
      .assessment-form label {
         font-size: 2rem;
         font-weight: bold;
         color: #444;
         margin-bottom: 1rem;
         display: block;
      }

      /* Textarea styling */
      .assessment-form textarea {
         width: 100%;
         padding: 1rem;
         font-size: 1.5rem;
         border: 1px solid #ddd;
         border-radius: 0.5rem;
         resize: vertical;
         background-color: #f4f4f4;
         color: #333;
      }

      .assessment-form textarea::placeholder {
         color: #999;
      }

      /* Navigation and submit buttons */
      .assessment-form .btn {
         display: inline-block;
         padding: 1rem 2rem;
         font-size: 1.5rem;
         color: #fff;
         background-color: #f39c12;
         border: none;
         border-radius: 0.5rem;
         cursor: pointer;
         transition: all 0.3s ease;
         margin: 1rem;
         text-align: center;
      }

      .assessment-form .btn:hover {
         background-color: #e67e22;
         transform: scale(1.05);
      }

      .assessment-form .btn:disabled {
         background-color: #ddd;
         color: #aaa;
         cursor: not-allowed;
      }

      /* Adjustments for mobile devices */
      @media (max-width: 768px) {
         .assessment-container {
            padding: 1rem;
         }

         .assessment-form .btn {
            width: 100%;
            margin-bottom: 1rem;
         }
      }
   </style>
</head>
<?php
include '../../config/db_connection.php';
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
?>
<body>

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

<?php

// Retrieve the Chapter ID from the GET request
$chapterID = isset($_GET['chapter_id']) ? $_GET['chapter_id'] : '';

if ($chapterID) {
    // Fetch the associated AssessmentID from the `coursechapter` table
    $chapterQuery = "SELECT AssessmentID FROM coursechapter WHERE CourseChapterID = ?";
    $stmt = $conn->prepare($chapterQuery);
    $stmt->bind_param("s", $chapterID);
    $stmt->execute();
    $chapterResult = $stmt->get_result();
    $chapter = $chapterResult->fetch_assoc();
    
    if ($chapter && $chapter['AssessmentID']) {
        $assessmentID = $chapter['AssessmentID'];

        // Fetch the questions for the AssessmentID
        $questionQuery = "SELECT * FROM assessmentquestion WHERE AssessmentID = ?";
        $stmt = $conn->prepare($questionQuery);
        $stmt->bind_param("s", $assessmentID);
        $stmt->execute();
        $questionResult = $stmt->get_result();

        // Store questions in an array
        $questions = $questionResult->fetch_all(MYSQLI_ASSOC);
    } else {
        echo "<p>No assessment is associated with this chapter.</p>";
        exit;
    }
} else {
    echo "<p>Chapter ID is missing. Cannot load assessment questions.</p>";
    exit;
}
?>

<section class="assessment-container">
   <h1>Assessment for HTML Chapter 1</h1>
   <form class="assessment-form" id="assessment-form" action="../../app/controller/submit_assessment.php?chapter_id=<?php echo urlencode($chapterID); ?>" method="post">
      <input type="hidden" name="assessmentID" value="<?php echo htmlspecialchars($chapter['AssessmentID']); ?>">
      <?php if (!empty($questions)): ?>
         <?php foreach ($questions as $index => $question): ?>
            <div class="question <?php echo $index === 0 ? 'active' : ''; ?>" data-question="<?php echo $index + 1; ?>">
               <label><?php echo $index + 1 . '. ' . htmlspecialchars($question['QuestionTitle']); ?></label>
               <p style="font-size:16px;"><?php echo htmlspecialchars($question['QuestionContent']); ?></p>

               <?php if ($question['QuestionOptions']): ?>
                  <!-- Render Multiple-Choice Question -->
                  <?php 
                     $options = json_decode($question['QuestionOptions'], true); 
                     foreach ($options as $key => $value): ?>
                        <div style="display: flex; align-items: center; margin-bottom: 10px;">
                           <input type="radio" id="option-<?php echo $key . '-' . $question['AssessmentQuestionID']; ?>" name="learnerAnswer[<?php echo htmlspecialchars($question['AssessmentQuestionID']); ?>]" value="<?php echo htmlspecialchars($key); ?>" required style="margin-right: 10px;">
                           <label for="option-<?php echo $key . '-' . $question['AssessmentQuestionID']; ?>" style="display: inline-block;">
                              <?php echo htmlspecialchars($key) . '. ' . htmlspecialchars($value); ?>
                           </label>
                        </div>
                     <?php endforeach; ?>
               <?php else: ?>
                  <!-- Render Open-Ended Question -->
                  <textarea name="learnerAnswer[<?php echo htmlspecialchars($question['AssessmentQuestionID']); ?>]" placeholder="Type your answer here..." required></textarea>
               <?php endif; ?>
            </div>
         <?php endforeach; ?>
         <button type="submit" class="btn">Submit Answers</button>
      <?php else: ?>
         <p>No questions found for this assessment.</p>
      <?php endif; ?>
   </form>
</section>

<!-- custom js file link  -->
<script src="../js/script.js"></script>

</body>
</html>