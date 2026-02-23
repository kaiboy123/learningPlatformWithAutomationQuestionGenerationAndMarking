<?php
include '../../config/db_connection.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['learner'])) {
    header('Location: ../../app/controller/login.php');
    exit();
}

$learnerID = $_SESSION['learner'] ?? null;

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

    if ($result->num_rows > 0) {
        $learner = $result->fetch_assoc();
    }
}

$courseID = $_GET['CourseID'] ?? null;

// Fetch course details dynamically
$course = null;
if ($courseID) {
    $stmt = $conn->prepare("SELECT * FROM course WHERE CourseID = ?");
    $stmt->bind_param("s", $courseID);
    $stmt->execute();
    $result = $stmt->get_result();
    $course = $result->fetch_assoc();
}

// Fetch subscription plans
$subscriptionPlans = [];
$sql = "SELECT * FROM subscriptionplancategory";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $subscriptionPlans[] = $row;
    }
}

// Generate Subscription ID
function generateSubscriptionID($conn) {
    $query = "SELECT MAX(SubscriptionID) AS maxID FROM paymentsubscription";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    $maxID = $row['maxID'];

    if ($maxID) {
        $num = intval(substr($maxID, 1)) + 1;
        return 'S' . str_pad($num, 5, '0', STR_PAD_LEFT);
    } else {
        return 'S00001';
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $cardNumber = trim($_POST['card_number']);
    $expiryDate = trim($_POST['expiry_date']);
    $cvv = trim($_POST['cvv']);
    $subscriptionPlan = $_POST['subscription_plan'] ?? null;

    if (!$subscriptionPlan) {
        $errors[] = "Please select a subscription plan.";
    }

    // Input validations
    if (empty($fullName)) {
        $errors[] = "Full Name is required.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid Email Address.";
    }
    if (!is_numeric($cardNumber) || strlen($cardNumber) !== 16) {
        $errors[] = "Card Number must be 16 digits.";
    }
    if (!preg_match('/^\d{2}\/\d{2}$/', $expiryDate)) {
        $errors[] = "Expiry Date must be in MM/YY format.";
    }
    if (!is_numeric($cvv) || strlen($cvv) !== 3) {
        $errors[] = "CVV must be 3 digits.";
    }

    // Process payment and subscription if no errors
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT * FROM subscriptionplancategory WHERE CategoryID = ?");
        $stmt->bind_param("s", $subscriptionPlan);
        $stmt->execute();
        $plan = $stmt->get_result()->fetch_assoc();

        if ($plan) {
            $paymentAmount = $plan['SubscriptionPrice'];
            $subscriptionDuration = $plan['SubscriptionDuration'];
            $subscriptionExpiredDate = date('Y-m-d', strtotime("+$subscriptionDuration"));

            $subscriptionID = generateSubscriptionID($conn);

            $stmt = $conn->prepare(
                "INSERT INTO paymentsubscription 
                (SubscriptionID, LearnerID, CourseID, PaymentAmount, PaymentMethod, PaymentStatus, PaymentDate, SubscriptionExpiredDate, SubscriptionStatus, CategoryID) 
                VALUES (?, ?, ?, ?, 'Credit Card', 'successful', NOW(), ?, 'active', ?)"
            );
            $stmt->bind_param("sssdss", $subscriptionID, $learnerID, $courseID, $paymentAmount, $subscriptionExpiredDate, $subscriptionPlan);

            if ($stmt->execute()) {
                $successMessage = "Thank you for subscribing to {$course['CourseTitle']}. Your subscription is valid until $subscriptionExpiredDate. Will Redirect Back to Home page in 5 seconds";
                echo "<script>
        setTimeout(() => {
            window.location.href = 'home.php';
        }, 5000); // Redirect after 5 seconds
    </script>";
            } else {
                $errors[] = "Failed to process your subscription. Please try again.";
            }
        } else {
            $errors[] = "Invalid subscription plan selected.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Payment Subscription</title>

   <!-- Font Awesome CDN link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">

   <!-- Custom CSS file link -->
   <link rel="stylesheet" href="../css/style.css">
   <link rel="stylesheet" href="../css/payment.css">
   <style>
.plan {
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 15px;
    text-align: center;
    cursor: pointer;
    transition: 0.3s;
    background-color: #f9f9f9;
}

.plan:hover {
    background-color: #f0f0f0;
}

.selected-plan {
    border-color: #007bff;
    box-shadow: 0 0 10px #007bff;
    background-color: #e6f7ff;
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
      <a href="about.php"><i class="fas fa-question"></i><span>About</span></a>
      <a href="contact.php"><i class="fas fa-headset"></i><span>Contact Us</span></a>
      <a href="codeplayground.php"><i class="fas fa-code"></i><span>Code Playground</span></a>
   </nav>
</div>

<div class="subscription-module">
    <h1>Subscribe to <?= htmlspecialchars($course['CourseTitle'] ?? 'Course Not Found'); ?></h1>

    <div class="plans">
        <?php foreach ($subscriptionPlans as $plan): ?>
            <div class="plan" data-plan-id="<?= $plan['CategoryID']; ?>">
                <h2><?= htmlspecialchars($plan['SubscriptionName']); ?></h2>
                <p><?= htmlspecialchars($plan['SubscriptionDescription']); ?></p>
                <strong>$<?= htmlspecialchars($plan['SubscriptionPrice']); ?></strong>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="error"><?= implode('<br>', $errors); ?></div>
    <?php elseif (!empty($successMessage)): ?>
        <div class="feedback"><?= $successMessage; ?></div>
    <?php endif; ?>

    <form method="POST" class="payment-form" id="payment-form">
    <input type="hidden" name="subscription_plan" id="selected-plan-id" value="<?= htmlspecialchars($_POST['subscription_plan'] ?? '') ?>" required>
    
    <label>Full Name:</label>
    <input type="text" name="full_name" placeholder="Enter your full name" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
    
    <label>Email:</label>
    <input type="email" name="email" placeholder="Enter your email address" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
    
    <label>Card Number:</label>
    <input type="text" name="card_number" maxlength="16" placeholder="16-digit card number" value="<?= htmlspecialchars($_POST['card_number'] ?? '') ?>" required>
    
    <label>Expiry Date (MM/YY):</label>
    <input type="text" name="expiry_date" placeholder="MM/YY" value="<?= htmlspecialchars($_POST['expiry_date'] ?? '') ?>" required>
    
    <label>CVV:</label>
    <input type="text" name="cvv" maxlength="3" placeholder="3-digit CVV" value="<?= htmlspecialchars($_POST['cvv'] ?? '') ?>" required>
    
    <button type="submit" id="subscribe-button">Subscribe Now</button>
</form>

<script>
    const plans = document.querySelectorAll('.plan');
    const hiddenInput = document.getElementById('selected-plan-id');
    const subscribeButton = document.getElementById('subscribe-button');
    const paymentForm = document.getElementById('payment-form');

    // Highlight selected plan and enable subscribe button
    plans.forEach(plan => {
        plan.addEventListener('click', () => {
            plans.forEach(p => p.classList.remove('selected-plan')); // Deselect all plans
            plan.classList.add('selected-plan'); // Highlight clicked plan
            hiddenInput.value = plan.getAttribute('data-plan-id'); // Update hidden input
        });
    });

    // Validate form before submission
    paymentForm.addEventListener('submit', (e) => {
        if (!hiddenInput.value) {
            e.preventDefault(); // Prevent form submission
            alert('Please select a subscription plan before proceeding.'); // Prompt user
        }
    });
</script>

</body>
</html>
