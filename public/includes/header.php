<?php
require_once __DIR__ . '/../../config/db_connection.php';
session_start();

/* ======================
   USER SESSION HANDLING
====================== */

$learnerID = $_SESSION['learner'] ?? null;

$learner = [
    'LearnerName' => 'Learner',
    'ProfileImg' => '../images/emptyprofile.jpg',
];

if ($learnerID) {
    $stmt = $conn->prepare("SELECT * FROM learner WHERE LearnerID = ?");
    $stmt->bind_param("s", $learnerID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $learner = $result->fetch_assoc();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title><?= $pageTitle ?? 'Online Learning'; ?></title>

   <link rel="stylesheet"
   href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">

   <link rel="stylesheet" href="../css/style.css">
   <?= $extraCSS ?? '' ?>
</head>

<body>

<header class="header">
<section class="flex">

<a href="home.php" class="logo">Online Learning.</a>

<form action="../../app/controllers/search.php" method="post" class="search-form">
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
   <img src="<?= $learner['ProfileImg']; ?>" class="image">
   <h3 class="name"><?= $learner['LearnerName']; ?></h3>
   <p class="role">Learner</p>

<?php if (!$learnerID): ?>
   <div class="flex-btn">
      <a href="../../app/controllers/login.php" class="option-btn">login</a>
      <a href="../../app/controllers/register.php" class="option-btn">register</a>
   </div>
<?php else: ?>
   <a href="profile.php" class="btn">view profile</a>
   <div class="flex-btn">
      <a href="../../app/controllers/logout.php" class="option-btn">logout</a>
   </div>
<?php endif; ?>

</div>
</section>
</header>


<div class="side-bar">

<div id="close-btn">
   <i class="fas fa-times"></i>
</div>

<div class="profile">
   <img src="<?= $learner['ProfileImg']; ?>" class="image">
   <h3 class="name"><?= $learner['LearnerName']; ?></h3>
   <p class="role">Learner</p>

<?php if (!$learnerID): ?>
   <a href="../../app/controllers/login.php" class="btn">login</a>
<?php else: ?>
   <a href="profile.php" class="btn">view profile</a>
<?php endif; ?>

</div>

<nav class="navbar">
   <a href="home.php"><i class="fas fa-home"></i><span>Home</span></a>
   <a href="about.php"><i class="fas fa-question"></i><span>About</span></a>
   <a href="contact.php"><i class="fas fa-headset"></i><span>Contact</span></a>
   <a href="codeplayground.php"><i class="fas fa-code"></i><span>Code Playground</span></a>
</nav>

</div>