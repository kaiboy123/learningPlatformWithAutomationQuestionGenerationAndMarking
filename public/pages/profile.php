<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>profile</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="../css/style.css">

</head>
<body>
<?php 
   include '../../config/db_connection.php';

   // Start session
   session_start();

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
      <a href="profile.php" class="btn">view profile</a>
   </div>

   <nav class="navbar">
      <a href="home.php"><i class="fas fa-home"></i><span>Home</span></a>
      <a href="about.php"><i class="fas fa-question"></i><span>About</span></a>
      <a href="contact.php"><i class="fas fa-headset"></i><span>Contact Us</span></a>
      <a href="codeplayground.php"><i class="fas fa-code"></i><span>Code Playground</span></a>
   </nav>
</div>

<section class="user-profile">
   <h1 class="heading">your profile</h1>

   <div class="info">
      <div class="user">
      <img src="<?php echo $learner['ProfileImg']; ?>" class="image" alt="">
         <h3><?php echo $learner['LearnerName']; ?></h3>
         <p>Learner</p>
         <!-- Update button with JavaScript to check login -->
         <a href="#" class="inline-btn" id="update-btn">update profile</a>
      </div>
   
      <div class="box-container">
         <div class="box">
            <div class="flex">
               <i class="fas fa-bookmark"></i>
               <div>
                  <span>0</span>
                  <p>completed courses</p>
               </div>
            </div>
            <a href="#" class="inline-btn">completed courses</a>
         </div>
   
         <div class="box">
            <div class="flex">
               <i class="fas fa-heart"></i>
               <div>
                  <span>3</span>
                  <p>active courses</p>
               </div>
            </div>
            <a href="#" class="inline-btn">active courses</a>
         </div>
   
         <div class="box">
            <div class="flex">
               <i class="fas fa-comment"></i>
               <div>
                  <span>8</span>
                  <p>achievement</p>
               </div>
            </div>
            <a href="#" class="inline-btn">achievement</a>
         </div>
      </div>
   </div>
</section>
<script src="../js/script.js"></script>
<!-- custom js file link  -->
<script>
   // Get learner ID from PHP and pass it to JavaScript
   const learnerID = <?php echo json_encode($learnerID); ?>;

   // Add click event to update button
   document.getElementById('update-btn').addEventListener('click', function (e) {
       if (!learnerID) {
           e.preventDefault(); // Prevent default action
           alert('Please login first to update your profile!');
       } else {
           // Redirect to the update page
           window.location.href = 'update.php';
       }
   });
</script>
</body>
</html>
