<?php
include '../../config/db_connection.php';
session_start();
// Fetch the chapter ID from the URL, default to a specific chapter if not provided
$chapter_id = isset($_GET['chapter_id']) ? $_GET['chapter_id'] : 'CH001';
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
// Prepare and execute the query to fetch practical test details
$sql = "SELECT pt.PracticalTestID, pt.PracticalQuestionTitle, pt.PracticalQuestionContent, pt.PracticalQuestionAnswer 
        FROM practicaltest pt
        JOIN coursechapter cc ON pt.PracticalTestID = cc.PracticalTestID
        WHERE cc.CourseChapterID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $chapter_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if a practical question exists for the given chapter
$practical = $result->fetch_assoc();
if (!$practical) {
    die("No practical question found for this chapter.");
}
$practicalTestID = $practical['PracticalTestID'];
// User's submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_code = $_POST['user_code'] ?? '';

    // Create JSON file for input data
    $input_data = [
        'userCode' => $user_code,
        'questionTitle' => $practical['PracticalQuestionTitle'],
        'questionContent' => $practical['PracticalQuestionContent'],
    ];
    $input_file = 'input.json';
    file_put_contents($input_file, json_encode($input_data));

    // Execute the Python script
    $output_file = 'output.json';
    $command = escapeshellcmd("python3 analyze.py $input_file $output_file");
    $output = shell_exec($command);
    $returnCode = shell_exec("echo $?"); // Capture return code

    // Debug logging
    file_put_contents('debug.log', "Command: $command\nOutput: $output\nReturn Code: $returnCode\n", FILE_APPEND);

    // Check if the output file exists and parse it
    if (file_exists($output_file)) {
        $output_data = json_decode(file_get_contents($output_file), true);
    } else {
        $output_data = null;
        file_put_contents('debug.log', "Error: Output file not found.\n", FILE_APPEND);
    }

    if ($output_data && isset($output_data['correct'])) {
        if ($output_data['correct']) {
            // Insert completion record into the database
            $completedPracticalID = uniqid('CP');
            $completionDate = date('Y-m-d');

            $query = "INSERT INTO completedpracticaltest (CompletedPracticalID, CompletedLearner, PracticalTestID, CompletionDate) 
                      VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            
            if ($stmt) {
                $stmt->bind_param("ssss", $completedPracticalID, $learnerID, $practicalTestID, $completionDate);
                $executeSuccess = $stmt->execute();
                
                if ($executeSuccess) {
                    // Respond with success and include the redirect button
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Correct! You have completed the practical question.',
                        'redirect_url' => "htmlChapter1.php?chapter_id=$chapter_id",
                    ]);
                } else {
                    // Log execution failure
                    file_put_contents('debug.log', "Insert execute failed: " . $stmt->error . "\n", FILE_APPEND);
                }
            } else {
                // Log preparation failure
                file_put_contents('debug.log', "Prepare failed: " . $conn->error . "\n", FILE_APPEND);
            }
        } else {
            // Handle incorrect response
            echo json_encode([
                'status' => 'error',
                'feedback' => $output_data['feedback'],
            ]);
        }
    } else {
        // Handle errors in the script
        echo json_encode([
            'status' => 'error',
            'feedback' => 'An error occurred while analyzing your code. Please try again.',
        ]);
    }
    exit;
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Practical Questions</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 16px;
            line-height: 1.6;
            background-color: #f4f4f9;
            color: #333;
            margin: 0;
        }
        .content {
            background-color: #fff;
            padding: 2rem;
            margin: 2rem auto;
            border-radius: .5rem;
            box-shadow: 0 0 1rem rgba(0, 0, 0, 0.1);
            max-width: 1000px;
        }
        .heading {
            font-size: 2rem;
            color: #444;
            margin-bottom: 1rem;
        }
        .submit-btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            color: #fff;
            background-color: #28a745;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 1rem;
        }
        .submit-btn:hover {
            background-color: #218838;
        }
        .code-input {
            width: 100%;
            height: 200px;
            padding: 1rem;
            margin-top: 1rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: monospace;
            font-size: 1rem;
            resize: vertical;
        }
        .result {
            margin-top: 1rem;
            padding: 1rem;
            border-radius: 5px;
            display: none;
        }
        .result.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .result.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
    <h1 class="heading"><?php echo htmlspecialchars($practical['PracticalQuestionTitle']); ?></h1>

    <div class="content" style="font-size: 16px;">
        <h2>Task</h2>
        <p><?php echo nl2br(htmlspecialchars($practical['PracticalQuestionContent'])); ?></p>

        <form method="post" id="codeForm">
            <textarea class="code-input" style="font-size: 16px;" name="user_code" placeholder="Write your HTML code here..."></textarea>
            <button type="submit" class="submit-btn" style="font-size: 16px;">
                <span class="button-text">Submit</span>
                <span class="spinner" style="display: none;">🔄</span>
            </button>

        </form>

        <div class="result" id="result"></div>
    </div>
</section>
<script>
    document.getElementById('codeForm').addEventListener('submit', async function (event) {
    event.preventDefault(); // Prevent default form submission

    const submitButton = document.querySelector('.submit-btn');
    const resultDiv = document.getElementById('result');
    const userCode = document.querySelector('textarea[name="user_code"]').value;

    const formData = new FormData();
    formData.append('user_code', userCode);

    submitButton.textContent = 'Submitting...';
    submitButton.disabled = true;
    resultDiv.style.display = 'none';

    try {
        const response = await fetch('', {
            method: 'POST',
            body: formData,
        });

        const data = await response.json();

        if (data.status === 'success') {
            resultDiv.className = 'result success';
            resultDiv.innerHTML = `
                ${data.message}
                <br><br>
                <a href="${data.redirect_url}" class="submit-btn" style="background-color: #007bff; text-decoration: none;">
                    Back to Course Chapter Page
                </a>
            `;
        } else {
            resultDiv.className = 'result error';
            resultDiv.innerHTML = data.feedback;
        }
        resultDiv.style.display = 'block';
    } catch (error) {
        resultDiv.className = 'result error';
        resultDiv.textContent = 'An error occurred while submitting your code. Please try again.';
        resultDiv.style.display = 'block';
    } finally {
        submitButton.textContent = 'Submit';
        submitButton.disabled = false;
    }
});

</script>

</body>
</html>
