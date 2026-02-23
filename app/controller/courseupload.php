<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Add New Course</title>

   <!-- Font Awesome CDN link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">

   <!-- Custom CSS file link -->
   <link rel="stylesheet" href="css/style.css">

   <style>
      body {
         display: flex;
         font-size: 18px;
         background-color: #f8f9fa;
      }
      .side-bar {
         width: 250px;
         background-color: #343a40;
         color: #fff;
         min-height: 100vh;
         position: fixed;
      }
      .side-bar .profile {
         padding: 20px;
         text-align: center;
      }
      .side-bar .profile img {
         width: 100px;
         height: 100px;
         border-radius: 50%;
         margin-bottom: 10px;
      }
      .side-bar nav a {
         display: block;
         color: #fff;
         padding: 15px;
         text-decoration: none;
         border-bottom: 1px solid #495057;
      }
      .side-bar nav a:hover {
         background-color: #495057;
      }
      .main-content {
         margin-left: 10%;
         padding: 40px;
         width: calc(100% - 270px);
         background-color: #f8f9fa;
      }
      .container {
         max-width: 900px;
         margin: 0 auto;
         background: #fff;
         padding: 20px;
         border-radius: 10px;
         box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      }
      h1 {
         margin-bottom: 20px;
         font-size: 2em;
      }
      .form-group {
         margin-bottom: 20px;
      }
      .form-group label {
         display: block;
         margin-bottom: 5px;
         font-weight: bold;
      }
      .form-group input,
      .form-group textarea {
         width: 100%;
         padding: 10px;
         border: 1px solid #ddd;
         border-radius: 5px;
      }
      .form-group textarea {
         resize: vertical;
         height: 100px;
      }
      .form-group .file-input {
         margin-top: 10px;
      }
      .chapters {
         margin-top: 30px;
      }
      .chapter {
         border: 1px solid #ddd;
         padding: 15px;
         margin-bottom: 10px;
         border-radius: 5px;
         background: #f2f2f2;
      }
      .chapter h3 {
         margin-top: 0;
      }
      .chapter input,
      .chapter textarea {
         width: calc(100% - 22px);
         padding: 10px;
         margin-top: 5px;
         border: 1px solid #ddd;
         border-radius: 5px;
      }
      .chapter textarea {
         height: 70px;
      }
      .chapter-actions {
         text-align: right;
      }
      .btn {
         padding: 10px 20px;
         background-color: #007bff;
         color: #fff;
         border: none;
         border-radius: 5px;
         cursor: pointer;
         text-decoration: none;
         display: inline-block;
      }
      .btn:hover {
         background-color: #0056b3;
      }
      .btn-danger {
         background-color: #dc3545;
      }
      .btn-danger:hover {
         background-color: #c82333;
      }
      .btn-success {
         background-color: #28a745;
      }
      .btn-success:hover {
         background-color: #218838;
      }
      .btn-add-chapter {
         margin-top: 20px;
      }
   </style>
</head>
<body>

<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit();
 }
// Get the logged-in admin's role and details
$adminRole = $_SESSION['admin']['AdminRole'];
$adminName = $_SESSION['admin']['AdminName'];
?>

<div class="side-bar">

<div class="profile">
      <img src="images/pic-1.jpg" class="image" alt="Admin Picture">
      <h1 class="name" style="color:#ddd"><?= htmlspecialchars($_SESSION['admin']['AdminName']); ?></h1>
      <p class="role"><?= htmlspecialchars($_SESSION['admin']['AdminRole']); ?></p>
   </div>

   <nav class="navbar" style="max-height: 70vh; overflow-y: auto;">
      <a href="adminhome.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
      <?php if ($adminRole === 'Manager'): ?>
         <a href="adminmanagement.php"><i class="fas fa-user-shield"></i><span>Manage Admin</span></a>
      <?php endif; ?>
      <a href="usermanagement.php"><i class="fas fa-users"></i><span>Manage Users</span></a>
      <a href="coursemanagement.php"><i class="fas fa-book"></i><span>Manage Course</span></a>
      <a href="subscripmanage.php"><i class="fas fa-cogs"></i><span>Manage Plans</span></a>
      <a href="manageachievement.php"><i class="fas fa-trophy"></i><span>Manage Cert</span></a>
      <a href="managefeedback.php"><i class="fas fa-comments"></i><span>Manage Feedback</span></a>
      <a href="report.php"><i class="fas fa-chart-bar"></i><span>Reports</span></a>
      <a href="logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
   </nav>

</div>

<div class="main-content">
   <div class="container">
      <h1>Add New Course</h1>
      <form action="save_new_course.php" method="post" enctype="multipart/form-data">
         <div class="form-group">
            <label for="courseName">Course Name</label>
            <input type="text" id="courseName" name="courseName" required>
         </div>
         <div class="form-group">
            <label for="courseImage">Course Image</label>
            <input type="file" id="courseImage" name="courseImage" required>
         </div>
         <div class="form-group">
            <label for="courseDescription">Course Description</label>
            <textarea id="courseDescription" name="courseDescription" required></textarea>
         </div>
         <div class="chapters">
    <h2>Chapters</h2>
    <div class="chapter" id="chapter1">
        <h3>Chapter 1</h3>
        <label for="chapter1Title">Title</label>
        <input type="text" id="chapter1Title" name="chapterTitle[]" required>
        <label for="chapter1Description">Description</label>
        <textarea id="chapter1Description" name="chapterDescription[]" required></textarea>
        <label for="chapter1Content">Content</label>
        <textarea id="chapter1Content" name="chapterContent[]" required></textarea>
        <label for="chapter1Video">Chapter Video</label>
        <input type="file" id="chapter1Video" name="chapterVideo[]" accept="video/*" required>
        <label for="chapter1Image">Chapter Image</label>
        <input type="file" id="chapter1Image" name="chapterImage[]" accept="image/*" required>
    </div>
</div>
<button type="button" class="btn btn-add-chapter" onclick="addChapter()">Add Chapter</button>
         <button type="submit" class="btn">Save Course</button>
      </form>
   </div>
</div>

<script>
   function removeChapter(chapterId) {
      var chapter = document.getElementById(chapterId);
      chapter.remove();
   }

   function addChapter() {
    var chaptersContainer = document.querySelector('.chapters');
    var newChapterNumber = chaptersContainer.querySelectorAll('.chapter').length + 1;

    var newChapter = document.createElement('div');
    newChapter.classList.add('chapter');
    newChapter.id = 'chapter' + newChapterNumber;

    newChapter.innerHTML = `
        <h3>Chapter ${newChapterNumber}</h3>
        <label for="chapter${newChapterNumber}Title">Title</label>
        <input type="text" id="chapter${newChapterNumber}Title" name="chapterTitle[]" required>
        <label for="chapter${newChapterNumber}Description">Description</label>
        <textarea id="chapter${newChapterNumber}Description" name="chapterDescription[]" required></textarea>
        <label for="chapter${newChapterNumber}Content">Content</label>
        <textarea id="chapter${newChapterNumber}Content" name="chapterContent[]" required></textarea>
        <label for="chapter${newChapterNumber}Video">Chapter Video</label>
        <input type="file" id="chapter${newChapterNumber}Video" name="chapterVideo[]" accept="video/*" required>
        <label for="chapter${newChapterNumber}Image">Chapter Image</label>
        <input type="file" id="chapter${newChapterNumber}Image" name="chapterImage[]" accept="image/*" required>
        <div class="chapter-actions">
            <button type="button" class="btn btn-danger" onclick="removeChapter('chapter${newChapterNumber}')">Remove</button>
        </div>
    `;

    chaptersContainer.appendChild(newChapter);
}
</script>

</body>
</html>