<!DOCTYPE html>
<html lang="en">
   <?php
   include '../../config/db_connection.php';

   // Fetch course details using the CourseID passed in the URL
   if (isset($_GET['id'])) {
      $courseID = $_GET['id'];
   
      // Fetch course data
      $courseQuery = "SELECT * FROM course WHERE CourseID = ?";
      $stmt = $conn->prepare($courseQuery);
      $stmt->bind_param("s", $courseID);
      $stmt->execute();
      $courseResult = $stmt->get_result();
      $course = $courseResult->fetch_assoc();

      // Fetch course chapters
      $chapterQuery = "SELECT * FROM coursechapter WHERE CourseID = ?";
      $stmt = $conn->prepare($chapterQuery);
      $stmt->bind_param("s", $courseID);
      $stmt->execute();
      $chapterResult = $stmt->get_result();
      $chapters = $chapterResult->fetch_all(MYSQLI_ASSOC);
   } else {
      echo "Course ID not provided!";
      exit;
   }
   ?>
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Edit Course</title>

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
      .form-group img {
         width: 150px;
         height: auto;
         display: block;
         margin-top: 10px;
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
      .chapter-tutorial-practical {
       display: flex;
       justify-content: space-between;
       margin-top: 10px;
   }

   .chapter-tutorial-practical .btn-left {
       flex: 1;
       margin-right: 10px;
   }

   .chapter-tutorial-practical .btn-right {
       flex: 1;
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


<div style="display: flex; gap: 20px; align-items: flex-start;">
    <!-- Chapter List Sidebar -->
    <div style="width: 200px; background-color: #f1f1f1; padding: 15px; border-radius: 8px;">
        <h3 style="margin-top: 0;">Chapters</h3>
        <ul id="chapterList" style="list-style-type: none; padding: 0; margin: 0;">
            <?php foreach ($chapters as $index => $chapter): ?>
                <li style="margin-bottom: 10px;">
                    <a href="#chapter<?php echo $index + 1; ?>" style="text-decoration: none; color: #007bff;">
                        Chapter <?php echo $index + 1; ?>: <?php echo htmlspecialchars($chapter['CourseChapterTitle']); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
        <!-- Add New Chapter Link -->
      
    </div>

    <!-- Main Content (Course Form) -->
    <div style="flex: 1;">
        <div class="container">
            <h1>Edit Course</h1>
            <form action="courseManageHandle.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="deleted_chapters" id="deleted_chapters" value="">
                <input type="hidden" name="courseID" value="<?php echo $courseID; ?>">

                <!-- Course Details -->
                <div class="form-group">
                    <label for="courseName">Course Name</label>
                    <input type="text" id="courseName" name="courseName" value="<?php echo $course['CourseTitle']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="courseImage">Course Image</label>
                    <img src="<?php echo $course['CourseImage']; ?>" alt="Course Image" id="current-course-image" style="max-width: 200px; margin-bottom: 10px;">
                    <input type="file" id="courseImage" name="courseImage" class="file-input">
                </div>
                <div class="form-group">
                    <label for="courseDescription">Course Description</label>
                    <textarea id="courseDescription" name="courseDescription" required><?php echo $course['CourseDescription']; ?></textarea>
                </div>

                <!-- Chapters -->
                <div class="chapters">
                    <h2>Chapters</h2>
                    <?php foreach ($chapters as $index => $chapter): ?>
                        <div class="chapter" id="chapter<?php echo $index + 1; ?>">
                            <h3>Chapter <?php echo $index + 1; ?></h3>
                            <input type="hidden" name="chapters[<?php echo $index; ?>][id]" value="<?php echo $chapter['CourseChapterID']; ?>">
                            <label for="chapter<?php echo $index + 1; ?>Title">Title</label>
                            <input type="text" id="chapter<?php echo $index + 1; ?>Title" name="chapters[<?php echo $index; ?>][title]" value="<?php echo htmlspecialchars($chapter['CourseChapterTitle']); ?>" required>
                            <label for="chapter<?php echo $index + 1; ?>Description">Description</label>
                            <textarea id="chapter<?php echo $index + 1; ?>Description" name="chapters[<?php echo $index; ?>][description]" required><?php echo htmlspecialchars($chapter['CourseChapterDescription']); ?></textarea>
                            <label for="chapter<?php echo $index + 1; ?>Video">Upload Video</label>
                            <input type="file" id="chapter<?php echo $index + 1; ?>Video" name="chapters[<?php echo $index; ?>][video]" accept="video/*">
                            <?php if (!empty($chapter['CourseChapterVideoUrl'])): ?>
                                <p>Current Video: <a href="<?php echo $chapter['CourseChapterVideoUrl']; ?>" target="_blank">View Video</a></p>
                            <?php endif; ?>
                            <label for="chapter<?php echo $index + 1; ?>Content">Content</label>
                            <textarea id="chapter<?php echo $index + 1; ?>Content" name="chapters[<?php echo $index; ?>][content]" required><?php echo htmlspecialchars($chapter['CourseChapterContent']); ?></textarea>
                            <label for="chapter<?php echo $index + 1; ?>Image">Upload Image</label>
                            <input type="file" id="chapter<?php echo $index + 1; ?>Image" name="chapters[<?php echo $index; ?>][image]" accept="image/*">
                            <?php if (!empty($chapter['CourseChapterImage'])): ?>
                                <p>Current Image:</p>
                                <img src="<?php echo $chapter['CourseChapterImage']; ?>" alt="Chapter Image" style="max-width:200px;">
                            <?php endif; ?>
                            <div class="chapter-tutorial-practical">
    <button type="button" class="btn btn-secondary btn-left" onclick="editAssessment(<?php echo $index; ?>)">Edit Assessment</button>
    <button type="button" class="btn btn-secondary btn-right" onclick="editPractical(<?php echo $index; ?>)">Edit Practical</button>
</div>
                            <div class="chapter-actions">
                                <button type="button" class="btn btn-danger" onclick="removeChapter('<?php echo $chapter['CourseChapterID']; ?>', this)">Remove</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="btn btn-success btn-add-chapter" onclick="addChapter()">Add New Chapter</button>
                <div class="form-group">
                    <button type="submit" class="btn">Save Changes</button>
                </div>
                <div style="position: fixed; bottom: 20px; right: 20px;">
                  <button onclick="scrollToTop(event)" class="btn btn-primary" style="padding: 10px 20px; border-radius: 50%; font-size: 18px;">
                     ↑
                  </button>
               </div>
            </form>
        </div>
    </div>
</div>

<script>
   var deletedChapters = [];

// Remove chapter function
function removeChapter(chapterId, button) {
        var chapter = button.closest('.chapter'); // Find the closest chapter div
        if (chapter) {
            // Display a confirmation dialog
            if (confirm('Are you sure you want to delete this chapter?')) {
                // Add the chapter ID to the deletedChapters array if not already present
                if (!deletedChapters.includes(chapterId)) {
                    deletedChapters.push(chapterId);
                }

                // Remove the chapter from the UI
                chapter.remove();

                // Update the hidden input field with the list of deleted chapters
                document.getElementById('deleted_chapters').value = deletedChapters.join(',');

              
            }
        }
    }

// Function to populate hidden input with deleted chapters
function populateDeletedChapters() {
    document.getElementById('deleted_chapters').value = deletedChapters.join(',');
}

function editAssessment(index) {
    var chapterId = document.querySelector(`[name="chapters[${index}][id]"]`).value;
    var courseId = "<?php echo $courseID; ?>";

    window.location.href = `assessmentEdit.php?courseID=${courseId}&chapterID=${chapterId}`;
}

function editPractical(index) {
    var chapterId = document.querySelector(`[name="chapters[${index}][id]"]`).value;
    var courseId = "<?php echo $courseID; ?>";

    window.location.href = `editPractical.php?courseID=${courseId}&chapterID=${chapterId}`;
}

// Attach populate function to form submission
document.querySelector('form').onsubmit = function() {
    populateDeletedChapters();
};
function addChapterToSidebar(chapterNumber) {
        var chapterList = document.getElementById('chapterList');
        var newListItem = document.createElement('li');
        newListItem.innerHTML = `<a href="#chapter${chapterNumber}" style="text-decoration: none; color: #007bff;">Chapter ${chapterNumber}</a>`;
        chapterList.appendChild(newListItem);
    }

    // Back to top function
    function scrollToTop(event) {
    if (event) event.preventDefault(); // Prevent default behavior if an event is passed
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function addChapter() {
        var chaptersContainer = document.querySelector('.chapters');
        var newChapterNumber = chaptersContainer.querySelectorAll('.chapter').length + 1;

        var newChapter = document.createElement('div');
        newChapter.classList.add('chapter');
        newChapter.id = 'chapter' + newChapterNumber;

        newChapter.innerHTML = `
            <h3>Chapter ${newChapterNumber}</h3>
            <input type="hidden" name="chapters[${newChapterNumber - 1}][id]" value="">
            <label for="chapter${newChapterNumber}Title">Title</label>
            <input type="text" id="chapter${newChapterNumber}Title" name="chapters[${newChapterNumber - 1}][title]" required>
            <label for="chapter${newChapterNumber}Description">Description</label>
            <textarea id="chapter${newChapterNumber}Description" name="chapters[${newChapterNumber - 1}][description]" required></textarea>
            <label for="chapter${newChapterNumber}Video">Upload Video</label>
            <input type="file" id="chapter${newChapterNumber}Video" name="chapters[${newChapterNumber - 1}][video]" accept="video/*">
            <label for="chapter${newChapterNumber}Content">Content</label>
            <textarea id="chapter${newChapterNumber}Content" name="chapters[${newChapterNumber - 1}][content]" required></textarea>
            <label for="chapter${newChapterNumber}Image">Upload Image</label>
            <input type="file" id="chapter${newChapterNumber}Image" name="chapters[${newChapterNumber - 1}][image]" accept="image/*">
            <div class="chapter-actions">
                <button type="button" class="btn btn-danger" onclick="removeChapter('new-chapter-${newChapterNumber}', this)">Remove</button>
            </div>
        `;

        chaptersContainer.appendChild(newChapter);

        // Add the new chapter to the sidebar
        addChapterToSidebar(newChapterNumber);
    }
</script>

</body>
</html>