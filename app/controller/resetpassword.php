<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Reset Password</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

   <style>
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
         <img src="images/pic-1.jpg" class="image" alt="">
         <h3 class="name">shaikh anas</h3>
         <p class="role">student</p>
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
      <img src="images/pic-1.jpg" class="image" alt="">
      <h3 class="name">shaikh anas</h3>
      <p class="role">student</p>
      <a href="profile.php" class="btn">view profile</a>
   </div>

   <nav class="navbar">
      <a href="home.php"><i class="fas fa-home"></i><span>Home</span></a>
      <a href="about.php"><i class="fas fa-question"></i><span>About</span></a>
      <a href="contact.php"><i class="fas fa-headset"></i><span>Contact Us</span></a>
      <a href="codeplayground.php"><i class="fas fa-code"></i><span>Code Playground</span></a>
   </nav>

</div>

<section class="form-container">
   <form action="reset_password.php" method="post">
      <h3>Reset Password</h3>
      <p>Enter a new password and confirm it</p>
      <input type="password" name="new_password" placeholder="Enter new password" required class="box">
      <input type="password" name="confirm_password" placeholder="Confirm new password" required class="box">
      <input type="submit" value="Reset Password" class="btn">
   </form>
</section>

</body>
</html>