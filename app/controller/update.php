<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>update</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
<?php
session_start();
require '../../config/db_connection.php'; // Ensure your database connection file is included

// Get learner ID from session
$learnerID = isset($_SESSION['learner']) ? $_SESSION['learner'] : null;

// Initialize learner data with default values
$learner = [
    'LearnerName' => '',
    'LearnerEmail' => '',
    'ProfileImg' => 'images/emptyprofile.jpg',
];

if ($learnerID) {
    // Fetch learner data
    $sql = "SELECT * FROM learner WHERE LearnerID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $learnerID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $learner = $result->fetch_assoc();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $oldPass = $_POST['old_pass'];
    $newPass = $_POST['new_pass'];
    $confirmPass = $_POST['c_pass'];
    $profileImg = $learner['ProfileImg'];

    $errors = [];

    if (!empty($oldPass) && !empty($newPass) && $newPass !== $confirmPass) {
        $errors[] = "New password and confirm password do not match.";
    }

    if (empty($errors)) {
        // Check old password if changing password
        if (!empty($oldPass)) {
            if (!password_verify($oldPass, $learner['Password'])) {
                $errors[] = "Old password is incorrect.";
            }
        }

        if (empty($errors)) {
            // Handle profile picture upload
            if (isset($_FILES['profile_img']) && $_FILES['profile_img']['error'] === UPLOAD_ERR_OK) {
                $targetDir = "images/";
                $fileName = uniqid() . "_" . basename($_FILES['profile_img']['name']);
                $targetFile = $targetDir . $fileName;

                if (move_uploaded_file($_FILES['profile_img']['tmp_name'], $targetFile)) {
                    $profileImg = $targetFile;
                } else {
                    $errors[] = "Failed to upload the image.";
                }
            }

            if (empty($errors)) {
                // Update learner data
                $hashedPassword = empty($newPass) ? $learner['Password'] : password_hash($newPass, PASSWORD_DEFAULT);

                $sql = "UPDATE learner SET LearnerName = ?, LearnerEmail = ?, Password = ?, ProfileImg = ? WHERE LearnerID = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssss", $name, $email, $hashedPassword, $profileImg, $learnerID);
                $stmt->execute();

                if ($stmt->affected_rows > 0) {
                    echo "<script>alert('Profile updated successfully!');</script>";
                    // Reload updated data
                    $learner['LearnerName'] = $name;
                    $learner['LearnerEmail'] = $email;
                    $learner['ProfileImg'] = $profileImg;
                } else {
                    echo "<script>alert('No changes were made to your profile.');</script>";
                }
            }
        }
    }

    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<script>alert('$error');</script>";
        }
    }
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
         <a href="profile.php" class="btn">view profile</a>
         <div class="flex-btn">
            <a href="login.php" class="option-btn">login</a>
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
   <img src="<?php echo $learner['ProfileImg']; ?>" class="image" alt="">
   <h3 class="name"><?php echo $learner['LearnerName']; ?></h3>
      <p class="role">Learner</p>
      <a href="profile.php" class="btn">view profile</a>
   </div>

   <nav class="navbar">
      <a href="home.php"><i class="fas fa-home"></i><span>Home</span></a>
      <a href="about.php"><i class="fas fa-question"></i><span>About</span></a>
      <!-- <a href="courses.html"><i class="fas fa-graduation-cap"></i><span>courses</span></a>
      <a href="teachers.html"><i class="fas fa-chalkboard-user"></i><span>teachers</span></a> -->
      <a href="contact.php"><i class="fas fa-headset"></i><span>Contact Us</span></a>
      <a href="codeplayground.php"><i class="fas fa-code"></i><span>Code Playground</span></a>
   </nav>

</div>

<section class="form-container">
   <form action="" method="post" enctype="multipart/form-data">
      <h3>Update Profile</h3>
      <p>Update Name</p>
      <input type="text" name="name" value="<?php echo htmlspecialchars($learner['LearnerName']); ?>" maxlength="50" class="box">
      <p>Update Email</p>
      <input type="email" name="email" value="<?php echo htmlspecialchars($learner['LearnerEmail']); ?>" maxlength="50" class="box">
      <p>Previous Password</p>
      <input type="password" name="old_pass" placeholder="Enter your old password" maxlength="20" class="box">
      <p>New Password</p>
      <input type="password" name="new_pass" placeholder="Enter your new password" maxlength="20" class="box">
      <p>Confirm Password</p>
      <input type="password" name="c_pass" placeholder="Confirm your new password" maxlength="20" class="box">
      <p>Update Profile Picture</p>
      <input type="file" name="profile_img" accept="image/*" class="box">
      <input type="submit" value="Update Profile" class="btn">
   </form>
</section>


<!-- custom js file link  -->
<script src="js/script.js"></script>

   
</body>
</html>