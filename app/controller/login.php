<!DOCTYPE html>
<html lang="en">
<head>
<?php
// Start session to store session variables
session_start();

// Include your database connection
include '../../config/db_connection.php'; 

if (isset($_POST['submit'])) {
    // Retrieve and escape email and password
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['pass']);

    // Check if the user is an administrator
    $admin_query = "SELECT * FROM administrator WHERE AdminEmail = ?";
    $admin_stmt = $conn->prepare($admin_query);
    $admin_stmt->bind_param("s", $email);
    $admin_stmt->execute();
    $admin_result = $admin_stmt->get_result();

    if ($admin_result->num_rows > 0) {
        $admin_data = $admin_result->fetch_assoc();
        
        // Verify the hashed password for administrators
        if (password_verify($password, $admin_data['Password'])) {
         // Password is correct, store session as an associative array
         $_SESSION['admin'] = [
             'AdminID' => $admin_data['AdminID'],
             'AdminName' => $admin_data['AdminName'],
             'AdminRole' => $admin_data['AdminRole']
         ];
     
         header('Location: ../admin/adminhome.php');
         exit();
     } else {
         $error_message= "<p style='color:red;'>Invalid email or password</p>";
        }
    }

    // Check if the user is a learner
    $learner_query = "SELECT * FROM learner WHERE LearnerEmail = ?";
    $learner_stmt = $conn->prepare($learner_query);
    $learner_stmt->bind_param("s", $email);
    $learner_stmt->execute();
    $learner_result = $learner_stmt->get_result();

    if ($learner_result->num_rows > 0) {
        $learner_data = $learner_result->fetch_assoc();

        // Verify the hashed password for learners
        if (password_verify($password, $learner_data['Password'])) {
            // Password is correct, store session and redirect
            $_SESSION['learner'] = $learner_data['LearnerID'];
            header('Location: ../../public/pages/home.php');
            exit();
        } else {
           $error_message = "<p style='color:red;'>Invalid email or password</p>";
        }
    } else {
      $error_message= "<p style='color:red;'>Invalid email or password</p>";
    }
}
?>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>login</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="../../public/css/style.css">
   <style>
      .forgot-link {
          display: block;
          margin-top: 10px;
          color: #007bff;
          text-decoration: none;
      }

      .forgot-link:hover {
          text-decoration: underline;
      }

      .links-container {
          display: flex;
          justify-content: space-between;
          margin-top: 15px; /* Adjust margin for spacing */
      }

      .link {
          font-size: 1.4em; /* Uniform font size for both links */
      }
   </style>
</head>
<body>
<?php
$message = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : '';
if ($message) {
    echo "<script>alert('$message');</script>";
}
?>
<header class="header">
   <section class="flex">
      <a href="../../public/pages/home.php" class="logo">Online Learning.</a>
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
         <img src="../../public/pages/images/emptyprofile.jpg" class="image" alt="">
         <h3 class="name">Learner</h3>
         <p class="role">Learner</p>
         <a href="login.php" class="btn">login</a>
         <div class="flex-btn">
            <a href="register.php" class="option-btn">register</a>
         </div>
      </div>
   </section>
</header>   

<div class="side-bar">
   <div id="close-btn">
      <i class="fas fa-times"></i>
   </div>
   <div class="profile">
      <img src="../../public/pages/images/emptyprofile.jpg" class="image" alt="">
      <h3 class="name">Learner</h3>
      <p class="role">Learner</p>
      <a href="login.php" class="btn">login</a>
   </div>
   <nav class="navbar">
      <a href="../../public/pages/home.php"><i class="fas fa-home"></i><span>Home</span></a>
      <a href="../../public/pages/about.php"><i class="fas fa-question"></i><span>About</span></a>
      <a href="../../public/pages/contact.php"><i class="fas fa-headset"></i><span>Contact Us</span></a>
      <a href="../../public/pages/codeplayground.php"><i class="fas fa-code"></i><span>Code Playground</span></a>
   </nav>
</div>

<section class="form-container">
   <form action="" method="post" enctype="multipart/form-data">
      <h3>login now</h3>
      <?php
      // Display error message here
      if (isset($error_message)) {
          echo "<div class='error-message'>$error_message</div>";
      }
      ?>
      <p>your email <span>*</span></p>
      <input type="email" name="email" placeholder="enter your email" required maxlength="50" class="box">
      <p>your password <span>*</span></p>
      <input type="password" name="pass" placeholder="enter your password" required maxlength="20" class="box">
      <input type="submit" value="login now" name="submit" class="btn">

      <div class="links-container">
         <a href="register.php" class="forgot-link link">Register Account</a>
         <a href="forgotpassword.php" class="forgot-link link">Forgot Password?</a>
      </div>
   </form>
</section>

<!-- custom js file link  -->
<script src="../../public/js/script.js"></script>

</body>
</html>
