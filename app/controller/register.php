<?php
// Start session to store session variables if needed
session_start();

// Include database configuration
include '../../config/db_connection.php'; 

// Check if form is submitted
if (isset($_POST['submit'])) {
    // Get form inputs and escape them for security
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['pass']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['c_pass']);
    $profile_img = $_FILES['profile_img']['name'];

    // Check if passwords match
    if ($password != $confirm_password) {
        echo "<p style='color:red;'>Passwords do not match.</p>";
    } else {
        // Check if the email already exists in the database
        $check_email_query = "SELECT * FROM learner WHERE LearnerEmail = '$email'";
        $check_email_result = mysqli_query($conn, $check_email_query);

        if (mysqli_num_rows($check_email_result) > 0) {
            // If email exists, show an error message

            echo "<script>alert('This Email Address has been register already. Please try another email address');</script>";
        } else {

         $get_last_id_query = "SELECT LearnerID FROM learner ORDER BY LearnerID DESC LIMIT 1";
            $last_id_result = mysqli_query($conn, $get_last_id_query);
            $last_id = mysqli_fetch_assoc($last_id_result);

            if ($last_id) {
                $last_id_number = (int)substr($last_id['LearnerID'], 1); // Extract numeric part
                $new_id_number = str_pad($last_id_number + 1, 4, '0', STR_PAD_LEFT); // Increment and pad with zeros
            } else {
                $new_id_number = '0001'; // Start from 0001 if no entries
            }
            $learner_id = 'L' . $new_id_number; // Create new LearnerID

            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $target_dir = "ProfileImage/";
            $target_file = $target_dir . basename($_FILES["profile_img"]["name"]);
           
            move_uploaded_file($_FILES["profile_img"]["tmp_name"], $target_file);

            // Insert learner data into the database
            $insert_query = "INSERT INTO learner (LearnerID,LearnerName, LearnerEmail, Password, ProfileImg, RegistrationDate) 
                             VALUES ('$learner_id','$name', '$email', '$hashed_password', '$target_file', NOW())";

            // Check if insertion is successful
            if (mysqli_query($conn, $insert_query)) {
                // Registration successful, display a message
                echo "<script>alert('Registration successful. You can now login.'); </script>";
               
            } else {
                
                echo "<script>alert('Register Failed');</script>";
            }
        }
    }
}

   // Get learner ID from session
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

?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Register</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="../../public/css/style.css">

   <script>
      function validateForm() {
         // Get form elements
         var name = document.forms["registerForm"]["name"].value;
         var email = document.forms["registerForm"]["email"].value;
         var pass = document.forms["registerForm"]["pass"].value;
         var c_pass = document.forms["registerForm"]["c_pass"].value;
         var emailPattern = /^[^ ]+@[^ ]+\.[a-z]{2,3}$/;
         var passwordPattern = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,20}$/;

         // Validate email
         if (!email.match(emailPattern)) {
            alert("Please enter a valid email address.");
            return false;
         }

         // Validate password
         if (!pass.match(passwordPattern)) {
            alert("Password must be 8-20 characters long, include at least one lowercase letter, one uppercase letter, and one digit.");
            return false;
         }

         // Confirm password
         if (pass != c_pass) {
            alert("Passwords do not match. Please try again.");
            return false;
         }

         return true;
      }
   </script>

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
        <a href="login.php" class="option-btn">login</a>
        <a href="register.php" class="option-btn">register</a>
        </div>
    <?php else: ?>
        <a href="profile.php" class="btn">view profile</a>
        <!-- Show logout button if logged in -->
        <div class="flex-btn">
        <a href="logout.php" class="option-btn">logout</a>
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
      <a href="../../public/pages/home.php"><i class="fas fa-home"></i><span>Home</span></a>
      <a href="../../public/pages/about.php"><i class="fas fa-question"></i><span>About</span></a>
      <a href="../../public/pages/contact.php"><i class="fas fa-headset"></i><span>Contact Us</span></a>
      <a href="../../public/pages/codeplayground.php"><i class="fas fa-code"></i><span>Code Playground</span></a>
   </nav>
</div>

<section class="form-container">
   <form name="registerForm" action="" method="post" enctype="multipart/form-data" onsubmit="return validateForm()">
      <h3>Register Now</h3>
      <p>Your Name <span>*</span></p>
      <input type="text" name="name" placeholder="Enter your name" required maxlength="50" class="box">
      <p>Your Email <span>*</span></p>
      <input type="email" name="email" placeholder="Enter your email" required maxlength="50" class="box">
      <p>Your Password <span>*</span></p>
      <input type="password" name="pass" placeholder="Enter your password" required maxlength="20" class="box">
      <p>Confirm Password <span>*</span></p>
      <input type="password" name="c_pass" placeholder="Confirm your password" required maxlength="20" class="box">
      <p>Select Profile <span>*</span></p>
      <input type="file" name="profile_img" accept="image/*">
      <input type="submit" value="Register Now" name="submit" class="btn">
      <!-- Add a backward button to go back to login -->
      <a href="login.php" class="btn">Back to Login</a>
   </form>
</section>

<!-- custom js file link  -->
<script src="../../public/js/script.js"></script>

</body>
</html>