<!DOCTYPE html>
<html lang="en">

<?php include '../../config/db_connection.php' ?>
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Course Management</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="../../public/pages/css/style.css">

   <style>
      body {
         display: flex;
         font-size: 18px;
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
      
      .addcoursebtn {
        padding: 10px 20px;
        margin-left: 20%;
        background-color: #007bff;
        color: #fff;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        text-align: center; /* Center text in button */
        }
    .addcoursebtn:hover {
    background-color: #0056b3;
    }
      .main-content {
         margin-left: 20px;
         padding: 40px;
         width: calc(100% - 270px);
         background-color: #f8f9fa;
      }
      .main-content h1 {
         margin-bottom: 40px;
         font-size: 2.5em;
      }
      .table-container {
         overflow-x: auto;
         margin-bottom: 40px;
      }
      table {
         width: 100%;
         border-collapse: collapse;
      }
      table, th, td {
         border: 1px solid #ddd;
      }
      th, td {
         padding: 15px;
         text-align: left;
      }
      th {
         background-color: #343a40;
         color: #fff;
      }
      tr:nth-child(even) {
         background-color: #f2f2f2;
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
      .search-container {
         display: flex;
         align-items: center;
         margin-bottom: 20px;
      }
      .search-container input[type="text"] {
         padding: 10px;
         font-size: 16px;
         margin-left: 10px;
         border: 1px solid black;
         border-radius: 5px;
         width: 30%;
      }
      .add-course-btn {
         margin-bottom: 20px;
         display: inline-block;
      }
   </style>

</head>
<body>
<?php
session_start();
// Check if admin is logged in
if (!isset($_SESSION['admin'])) {
   header('Location: ../controller/login.php'); // Redirect to login page if not logged in
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
      <a href="../controller/logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
   </nav>

</div>

<div class="main-content">

   <header>
      <h1>Course Management</h1>
   </header>

   <div class="search-container">
   <i class="fas fa-search"></i>
      <input type="text" id="searchInput" onkeyup="searchFunction()" placeholder="Search for courses..">
      <a href="../controller/courseupload.php" class="addcoursebtn">Add New Course</a>
   </div>

   <div class="table-container">
   <table id="courseTable">
      <thead>
         <tr>
            <th>ID</th>
            <th>Course Name</th>
            <th>Course Image</th> <!-- New column for course image -->
            <th>Description</th>
            <th>Actions</th>
         </tr>
      </thead>
      <?php 
      $sql = "SELECT * FROM course";

      $result = $conn->query(query: $sql);
      ?>
      <tbody>
      <?php
         // Check if there are results
         if ($result->num_rows > 0) {
            // Output data of each row
            while ($row = $result->fetch_assoc()) {
               echo "<tr>";
               echo "<td>" . $row["CourseID"] . "</td>";
               echo "<td>" . $row["CourseTitle"] . "</td>";
               echo '<td><img src="' . $row["CourseImage"] . '" alt="Course Image" style="width:100px; height:auto;"></td>';
               echo "<td>" . $row["CourseDescription"] . "</td>";
               echo '<td>
                        <a href="../controller/courseedit.php?id=' . $row["CourseID"] . '" class="btn">Edit</a>
                        <a href="#" onclick="confirmDelete(\'' . $row['CourseID'] . '\');" class="btn">Delete</a>
                     </td>';
               echo "</tr>";
            }
         } else {
            echo "<tr><td colspan='5'>No courses found</td></tr>";
         }
         ?>
      </tbody>
   </table>
</div>

</div>

<script>
   function confirmDelete(courseID) {
    const userConfirmed = confirm("Are you sure you want to delete this course?");
    if (userConfirmed) {
        window.location.href = `../controller/coursedelete.php?id=${courseID}`;
    }
}
   function searchFunction() {
      var input, filter, table, tr, td, i, j, txtValue;
      input = document.getElementById("searchInput");
      filter = input.value.toUpperCase();
      table = document.getElementById("courseTable");
      tr = table.getElementsByTagName("tr");

      for (i = 1; i < tr.length; i++) {
         tr[i].style.display = "none";
         td = tr[i].getElementsByTagName("td");
         for (j = 0; j < td.length; j++) {
            if (td[j]) {
               txtValue = td[j].textContent || td[j].innerText;
               if (txtValue.toUpperCase().indexOf(filter) > -1) {
                  tr[i].style.display = "";
                  break;
               }
            }
         }
      }
   }
</script>

</body>
</html>