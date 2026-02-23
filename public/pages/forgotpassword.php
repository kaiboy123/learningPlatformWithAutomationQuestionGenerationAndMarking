<?php
session_start();
include '../../config/db_connection.php';

// Include PHPMailer
require __DIR__ . '/phpmailer/src/PHPMailer.php';
require __DIR__ . '/phpmailer/src/SMTP.php';
require __DIR__ . '/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    // Check if email exists in the `learner` table
    $sql = "SELECT LearnerID FROM learner WHERE LearnerEmail = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $learner = $result->fetch_assoc();
        $learnerId = $learner['LearnerID'];

        // Generate token and expiry
        $token = bin2hex(random_bytes(16));
        $tokenHash = password_hash($token, PASSWORD_DEFAULT); // Hash the token for security
        $expiry = date("Y-m-d H:i:s", time() + 60 * 30); // 30 minutes from now

        // Update the `learner` table with the token and expiry
        $updateSql = "UPDATE learner SET ResetTokenHash = ?, ResetTokenExpiresAt = ? WHERE LearnerID = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param('sss', $tokenHash, $expiry, $learnerId);
        $updateStmt->execute();

        if ($updateStmt->affected_rows > 0) {
            // Send password reset email
            $mail = new PHPMailer(true);

            try {
                // SMTP configuration
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'hankai0115@gmail.com'; // Your Gmail address
                $mail->Password = 'xx';  // Your Gmail App Password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Email setup
                $mail->setFrom('hankai0115@gmail.com', 'Online Learning');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Request';
                $mail->Body = "
                    <h2>Password Reset Request</h2>
                    <p>Click <a href='http://localhost/PhpAssignmentKai/reset-password.php?token=$token'>here</a> to reset your password.</p>
                    <p>This link will expire in 30 minutes.</p>
                ";

                $mail->send();
                echo "<script>alert('Reset link sent! Check your email.');</script>";
            } catch (Exception $e) {
                echo "<script>alert('Email could not be sent. Mailer Error: {$mail->ErrorInfo}');</script>";
            }
        } else {
            echo "<script>alert('Failed to update reset token. Please try again.');</script>";
        }
    } else {
        echo "<script>alert('No learner found with this email address.');</script>";
    }
}


   // Get learner ID from session
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
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Forgot Password</title>

   <!-- font awesome cdn link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">

   <!-- custom css file link -->
   <link rel="stylesheet" href="css/style.css">

   <style>
      .hidden {
         display: none;
      }
      .form-container {
         max-width: 500px;
         margin: auto;
         margin-top: 10px;
         padding: 20px;
         border: 1px solid #ddd;
         border-radius: 5px;
         background: #f9f9f9;
      }
      .form-container h3 {
         margin-bottom: 20px;
      }
      .form-container input.box {
         width: 100%;
         padding: 10px;
         margin-bottom: 15px;
         border: 1px solid #ddd;
         border-radius: 5px;
      }
      .form-container input.btn {
         width: 100%;
         padding: 10px;
         border: none;
         background: #007bff;
         color: #fff;
         border-radius: 5px;
         cursor: pointer;
      }
      .form-container input.btn:hover {
         background: #0056b3;
      }
      #reset-form {
         height: 400px;
        max-width: 400px;
        margin: 50px auto;
        padding: 20px;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        text-align: center;
        font-family: Arial, sans-serif;
    }

    #reset-form h3 {
        font-size: 2.8em;
        color: #333;
        margin-bottom: 15px;
    }

    #reset-form p {
        font-size: 1.9em;
        color: #666;
        margin-bottom: 20px;
    }

    #reset-form .input-box {
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 1.5em;
        color: #333;
    }

    #reset-form .input-box:focus {
        border-color: #007bff;
        outline: none;
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
    }

    #reset-form .submit-btn {
        width: 100%;
        padding: 10px;
        border: none;
        border-radius: 5px;
        font-size: 1.5em;
        color: #fff;
        background: #007bff;
        cursor: pointer;
        transition: background 0.3s ease-in-out;
    }

    #reset-form .submit-btn:hover {
        background: #0056b3;
    }

    /* Mobile responsiveness */
    @media (max-width: 768px) {
        #reset-form {
            padding: 15px;
        }
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
      <?php if ($learnerID === null): ?>
       <!-- Show login button if not logged in -->
       <a href="login.php" class="btn">login</a>
       
   <?php else: ?>
       <!-- Show view profile button if logged in -->
       <a href="profile.php" class="btn">view profile</a>
   <?php endif; ?>
   </div>
   <nav class="navbar">
      <a href="home.php"><i class="fas fa-home"></i><span>Home</span></a>
      <a href="about.php"><i class="fas fa-question"></i><span>About</span></a>
      <a href="contact.php"><i class="fas fa-headset"></i><span>Contact Us</span></a>
      <a href="codeplayground.php"><i class="fas fa-code"></i><span>Code Playground</span></a>
   </nav>
</div>
<section class="form-container">

<form id="reset-form" onsubmit="sendResetLink(event)">
    <h3>Forgot Password</h3>
    <p>Please enter your email address. We'll send you a password reset link.</p>
    <input type="email" name="email" placeholder="Enter your email" class="input-box" required>
    <button type="submit" class="submit-btn">Send Reset Link</button>
</form>
</section>
<script>
     async function sendResetLink(event) {
    event.preventDefault(); // Prevent form submission
    
    const email = document.querySelector('input[name="email"]').value; // Get email input value
    const response = await fetch('http://127.0.0.1:5000/forgot-password', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: email })
    });

    const result = await response.json();
    
    // Handle response
    if (response.ok) {
        alert(result.message); // Display success message
    } else {
        alert(result.error); // Display error message
    }
}

   </script>
<script src="js/script.js"></script>
</body>
</html>
