<?php
include '../../config/db_connection.php'; // Include your database connection
session_start();

// Check if admin is logged in
// if (!isset($_SESSION['admin'])) {
//     header('Location: adminlogin.php');
//     exit();
// }

// Fetch administrators
$sql = "
    SELECT AdminID, AdminName, AdminEmail, AdminContactInfo, AccountStatus, AdminRole
    FROM administrator
";
$result = $conn->query($sql);

$admins = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $admins[] = $row;
    }
}

// Fetch roles for the filter dropdown
$roleResult = $conn->query("SELECT DISTINCT AdminRole FROM administrator");
$roles = [];
if ($roleResult && $roleResult->num_rows > 0) {
    while ($row = $roleResult->fetch_assoc()) {
        $roles[] = $row['AdminRole'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin Management</title>

   <!-- Font Awesome for icons -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
   <!-- Custom CSS -->
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
      .main-content {
         margin-left: 20px;
         padding: 40px;
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
      .search-container select {
         padding: 10px;
         font-size: 16px;
         border: 1px solid black;
         border-radius: 5px;
         margin-left: 15px;
      }
   </style>
</head>
<body>
<?php
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
      <img src="../../public/pages/images/pic-1.jpg" class="image" alt="Admin Picture">
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
<div class="container">
   <h1>Admin Management</h1>

   <!-- Add New Admin Button -->
   <div style="text-align: right; margin-bottom: 10px;">
      <a href="add_admin.php" class="btn btn-primary">Add New Admin</a>
   </div>

   <!-- Search and Filter -->
   <div class="search-container">
      <input type="text" id="searchInput" onkeyup="searchFunction()" placeholder="Search admins..">
      <select id="filterSelect" onchange="filterFunction()">
         <option value="">Filter by role</option>
         <?php foreach ($roles as $role): ?>
            <option value="<?= htmlspecialchars($role); ?>"><?= htmlspecialchars($role); ?></option>
         <?php endforeach; ?>
      </select>
   </div>

   <!-- Admin Table -->
   <table id="adminTable">
      <thead>
         <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Contact</th>
            <th>Status</th>
            <th>Role</th>
            <th>Actions</th>
         </tr>
      </thead>
      <tbody>
         <?php foreach ($admins as $admin): ?>
         <tr>
            <td><?= htmlspecialchars($admin['AdminID']); ?></td>
            <td><?= htmlspecialchars($admin['AdminName']); ?></td>
            <td><?= htmlspecialchars($admin['AdminEmail']); ?></td>
            <td><?= htmlspecialchars($admin['AdminContactInfo']); ?></td>
            <td><?= htmlspecialchars($admin['AccountStatus']); ?></td>
            <td><?= htmlspecialchars($admin['AdminRole']); ?></td>
            <td>
               <a href="admin_edit.php?id=<?= $admin['AdminID']; ?>" class="btn btn-primary">Edit</a>
               <a href="admin_delete.php?id=<?= $admin['AdminID']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this admin?')">Delete</a>
            </td>
         </tr>
         <?php endforeach; ?>
      </tbody>
   </table>
</div>

<script>
   function searchFunction() {
      var input, filter, table, tr, td, i, j, txtValue;
      input = document.getElementById("searchInput");
      filter = input.value.toUpperCase();
      table = document.getElementById("adminTable");
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

   function filterFunction() {
      var input, filter, table, tr, td, i, txtValue;
      input = document.getElementById("filterSelect");
      filter = input.value.toUpperCase();
      table = document.getElementById("adminTable");
      tr = table.getElementsByTagName("tr");

      for (i = 1; i < tr.length; i++) {
         tr[i].style.display = "none";
         td = tr[i].getElementsByTagName("td")[5]; // Role column
         if (td) {
            txtValue = td.textContent || td.innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1 || filter == "") {
               tr[i].style.display = "";
            }
         }
      }
   }
</script>

</body>
</html>
