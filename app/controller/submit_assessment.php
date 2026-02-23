<?php
include '../../config/db_connection.php';

$assessmentID = $_POST['assessmentID'] ?? ''; // Ensure assessmentID is passed
session_start();
$chapterID = $_GET['chapter_id'] ?? $_SESSION['chapter_id'] ?? null;
$learnerID = isset($_SESSION['learner']) ? $_SESSION['learner'] : null;

    // Initialize learner data with default values
    $learner = [
        'LearnerName' => 'Learner',
        'ProfileImg' => '../../public/pages/images/emptyprofile.jpg',
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
if ($assessmentID) {
    // Fetch questions for the given assessment ID
    $query = "SELECT AssessmentQuestionID, QuestionTitle, QuestionAnswer FROM assessmentquestion WHERE AssessmentID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $assessmentID);
    $stmt->execute();
    $result = $stmt->get_result();

    $questions = [];
    while ($row = $result->fetch_assoc()) {
        $questions[$row['AssessmentQuestionID']] = [
            'questionTitle' => $row['QuestionTitle'],
            'correctAnswer' => $row['QuestionAnswer'],
            
        ];
    }

    $learnerAnswers = $_POST['learnerAnswer'] ?? [];
    
    // Combine questions with learner answers
    $submittedAnswers = [];
    foreach ($questions as $questionID => $questionData) {
        $submittedAnswers[] = [
            'questionID' => $questionID,
            'questionTitle' => $questionData['questionTitle'],
            'correctAnswer' => $questionData['correctAnswer'],
            'learnerAnswer' => $learnerAnswers[$questionID]
        ];
    }
   
    // Save $questions directly to JSON
    $inputFilePath = '../../storage/tmp/assessment_input.json'; // Adjust path as needed
    file_put_contents($inputFilePath, json_encode($submittedAnswers));

    // Call the Python script for processing
    $outputFilePath = '../../storage/tmp/assessment_output.json'; // Adjust path as needed
    $command = escapeshellcmd("python3 ../ai/automark_assessment.py $inputFilePath $outputFilePath");
    exec($command, $output, $returnCode);

    $results = [];
    if ($returnCode === 0 && file_exists($outputFilePath)) {
        // Read output from the Python script
        $results = json_decode(file_get_contents($outputFilePath), true);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessment Results</title>
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    <style>
        .results-container {
            background-color: #f9f9f9;
            padding: 2rem;
            margin: 2rem auto;
            border-radius: 1rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            max-width: 900px;
            text-align: center;
        }
        .results-container h1 {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 1.5rem;
            font-weight: bold;
            border-bottom: 3px solid #f39c12;
            display: inline-block;
            padding-bottom: 0.5rem;
        }
        .results-list {
            text-align: left;
            margin-bottom: 1.5rem;
        }
        .results-list .question-result {
            margin-bottom: 2rem;
            font-size: 1.5rem;
            padding: 1rem;
            border: 1px solid #ddd;
            border-radius: 0.5rem;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .results-list label {
            font-size: 1.8rem;
            color: #444;
            margin-bottom: 0.5rem;
            display: block;
            font-weight: bold;
        }
        .results-list .correct {
            color: green;
            font-weight: bold;
        }
        .results-list .incorrect {
            color: red;
            font-weight: bold;
        }
        .results-list .feedback {
            margin-top: 1rem;
            font-size: 1.2rem;
            color: #666;
        }
        .score-container {
            margin-top: 2rem;
            font-size: 1.8rem;
            font-weight: bold;
            color: #333;
        }
        .retry-btn {
            display: inline-block;
            margin-top: 1.5rem;
            padding: 1rem 2rem;
            font-size: 1.5rem;
            color: #fff;
            background-color: #f39c12;
            border: none;
            border-radius: 0.5rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .retry-btn:hover {
            background-color: #e67e22;
            transform: scale(1.05);
        }
    </style>
</head>
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
            <a href="login.php" class="option-btn">Login</a>
            <a href="register.php" class="option-btn">Register</a>
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

<section class="results-container">
    <h1>Assessment Results</h1>
    <div class="results-list">
        <?php if (!empty($results)): ?>
            <?php foreach ($results as $result): ?>
                <div class="question-result">
                    <label><?php echo htmlspecialchars($result['questionTitle']); ?></label>
                    <p>Your Answer: 
                        <span class="<?php echo ($result['marking']['score'] > 0) ? 'correct' : 'incorrect'; ?>">
                            <?php echo htmlspecialchars($result['learnerAnswer']); ?>
                        </span>
                    </p>
                    <p>Correct Answer: <span class="correct"><?php echo htmlspecialchars($result['correctAnswer']); ?></span></p>
                    <p class="feedback"><?php echo htmlspecialchars($result['marking']['feedback']); ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Failed to process the assessment. Please try again later.</p>
        <?php endif; ?>
    </div>

    <div class="score-container">
        <?php if (!empty($results)): ?>
            Your Score: 
            <?php
            $totalCorrect = array_reduce($results, function ($carry, $item) {
                return $carry + ($item['marking']['score'] > 0 ? 1 : 0);
            }, 0);
            echo $totalCorrect . '/' . count($results);

            $fullMarkAchieved = ($totalCorrect === count($results));
            ?>
        <?php endif; ?>
    </div>

    <?php if (!empty($results)): ?>
        <?php if ($fullMarkAchieved): ?>
            <form action="mark_completed.php" method="post">
            <input type="hidden" name="chapter_id" value="<?php echo htmlspecialchars($chapterID); ?>">
                <input type="hidden" name="assessmentID" value="<?php echo htmlspecialchars($assessmentID); ?>">
                <input type="hidden" name="learnerID" value="<?php echo htmlspecialchars($learnerID); ?>"> <!-- Replace with the logged-in learner ID -->
                <button type="submit" class="retry-btn">Mark as Completed</button>
            </form>
        <?php else: ?>
            <a href="../../public/pages/assessment.php?chapter_id=<?php echo urlencode($chapterID); ?>" class="retry-btn">Try Again</a>
        <?php endif; ?>
    <?php endif; ?>
</section>

<!-- Custom JS -->
<script src="js/script.js"></script>
</body>
</html>
