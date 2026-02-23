<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    // Fetch subscription plans

    ?>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin - Subscription Plans Management</title>

   <!-- font awesome cdn link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
   <!-- custom css file link -->
   <link rel="stylesheet" href="css/style.css" type="text/css">
   <link rel="stylesheet" href="css/subscripmanagestyle.css" type="text/css">
   <style>
      
   </style>
</head>
<body>
<?php
include '../../config/db_connection.php';
$sql = "SELECT * FROM subscriptionplancategory";
$result = $conn->query($sql);
$subscriptionPlans = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $subscriptionPlans[] = $row;
    }
}
// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    if ($action === 'insert') {
        $planName = trim($_POST['plan_name']);
    $planDuration = trim($_POST['plan_duration']);
    $planPrice = trim($_POST['plan_price']);
    $planDescription = trim($_POST['plan_description']);
    
    if (empty($planName) || empty($planDuration) || empty($planPrice) || empty($planDescription)) {
        die("All fields are required.");
    }

    if (!preg_match('/^\d+\s*(month|months|year|years)$/i', $planDuration)) {
        die("Invalid duration format. Use '6 months' or '1 year'.");
    }

    if (!is_numeric($planPrice) || floatval($planPrice) <= 0) {
        die("Price must be a valid number greater than 0.");
    }
    $durationRegex = '/(?<value>\d+)\s*(?<unit>month|months|year|years)/i';
    if (preg_match($durationRegex, $planDuration, $matches)) {
        $durationValue = (int)$matches['value'];
        $durationUnit = strtolower($matches['unit']);
        // Standardize duration (e.g., "6 months")
        $planDuration = $durationValue . ' ' . ($durationValue > 1 ? $durationUnit : rtrim($durationUnit, 's'));
    } else {
        die("Invalid duration format.");
    }
    $result = $conn->query("SELECT MAX(CategoryID) AS max_id FROM subscriptionplancategory");
    $row = $result->fetch_assoc();
    $maxID = $row['max_id'];

    // Calculate next CategoryID
    $nextID = 'SC001';
    if ($maxID) {
        $numericPart = (int) substr($maxID, 2);
        $nextID = 'SC' . str_pad($numericPart + 1, 3, '0', STR_PAD_LEFT);
        }

        // Insert new plan with the calculated ID
        $stmt = $conn->prepare("INSERT INTO subscriptionplancategory (CategoryID, SubscriptionName, SubscriptionDuration, SubscriptionDescription, SubscriptionPrice) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssd", $nextID, $planName, $planDuration, $planDescription, $planPrice);
        $stmt->execute();
        header("Location: " . $_SERVER['PHP_SELF']);
    exit();
    } elseif ($action === 'update') {
        // Update existing plan
        $stmt = $conn->prepare("UPDATE subscriptionplancategory SET SubscriptionName = ?, SubscriptionDuration = ?, SubscriptionDescription = ?, SubscriptionPrice = ? WHERE CategoryID = ?");
        $stmt->bind_param("sssds", $_POST['plan_name'], $_POST['plan_duration'], $_POST['plan_description'], $_POST['plan_price'], $_POST['plan_id']);
        $stmt->execute();
        header("Location: " . $_SERVER['PHP_SELF']);
    exit();
    }
}

// Handle deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $stmt = $conn->prepare("DELETE FROM subscriptionplancategory WHERE CategoryID = ?");
    $stmt->bind_param("s", $_GET['id']);
    $stmt->execute();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>
?>
<?php
session_start();
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
      <h1>Subscription Plans Management</h1>
   </header>

   <!-- Create/Upload New Subscription Plan -->
   <section class="section">
       <h2>Create New Subscription Plan</h2>
       <form method="post" onsubmit="return validateForm();">
           <input type="hidden" name="action" value="insert">
           <div class="form-group">
               <label for="plan-name">Plan Name</label>
               <input type="text" id="plan-name" name="plan_name" required>
           </div>
           <div class="form-group">
               <label for="plan-duration">Duration</label>
               <input type="text" id="plan-duration" name="plan_duration" required>
           </div>
           <div class="form-group">
               <label for="plan-price">Price</label>
               <input type="text" id="plan-price" name="plan_price" required>
           </div>
           <div class="form-group">
               <label for="plan-description">Description</label>
               <textarea id="plan-description" name="plan_description" required></textarea>
           </div>
           <div class="form-group">
               <button type="submit">Add Plan</button>
           </div>
       </form>
   </section>

   <!-- Existing Subscription Plans -->
   <section class="section" >
       <h2>Existing Subscription Plans</h2>
       <table >
           <thead>
               <tr>
                   <th>Plan Name</th>
                   <th>Duration</th>
                   <th>Price</th>
                   <th>Description</th>
                   <th>Actions</th>
               </tr>
           </thead>
           <tbody>
               <?php foreach ($subscriptionPlans as $plan): ?>
                   <tr >
                       <td><?= htmlspecialchars($plan['SubscriptionName']); ?></td>
                       <td><?= htmlspecialchars($plan['SubscriptionDuration']); ?></td>
                       <td>$<?= htmlspecialchars($plan['SubscriptionPrice']); ?></td>
                       <td><?= htmlspecialchars($plan['SubscriptionDescription']); ?></td>
                       <td>
                           <button class="edit-btn" 
                                   data-id="<?= $plan['CategoryID']; ?>" 
                                   data-name="<?= $plan['SubscriptionName']; ?>" 
                                   data-duration="<?= $plan['SubscriptionDuration']; ?>" 
                                   data-price="<?= $plan['SubscriptionPrice']; ?>" 
                                   data-description="<?= $plan['SubscriptionDescription']; ?>">
                               Edit
                           </button>
                           <a href="?action=delete&id=<?= $plan['CategoryID']; ?>" onclick="return confirm('Are you sure you want to delete this plan?');">Delete</a>
                       </td>
                   </tr>
               <?php endforeach; ?>
           </tbody>
       </table>
   </section>
<!-- Edit Plan Dialog -->
<div id="edit-dialog" class="dialog">
    <div class="dialog-content">
            <form method="post" id="edit-form" onsubmit="return validateEditForm();">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="plan_id" id="edit-plan-id">
            <div class="form-group">
                <label for="edit-plan-name">Plan Name</label>
                <input type="text" id="edit-plan-name" name="plan_name" required>
            </div>
            <div class="form-group">
                <label for="edit-plan-duration">Duration</label>
                <input type="text" id="edit-plan-duration" name="plan_duration" required>
            </div>
            <div class="form-group">
                <label for="edit-plan-price">Price</label>
                <input type="text" id="edit-plan-price" name="plan_price" required>
            </div>
            <div class="form-group">
                <label for="edit-plan-description">Description</label>
                <textarea id="edit-plan-description" name="plan_description" required></textarea>
            </div>
            <div class="form-group">
                <button type="submit">Save Changes</button>
                <button type="button" onclick="closeEditDialog();">Cancel</button>
            </div>
        </form>
    </div>


</div>
<script>
    // Validation for Edit Plan Form
function validateEditForm() {
    const planName = document.getElementById("edit-plan-name").value.trim();
    const planDuration = document.getElementById("edit-plan-duration").value.trim();
    const planPrice = document.getElementById("edit-plan-price").value.trim();
    const planDescription = document.getElementById("edit-plan-description").value.trim();

    // Validate plan name
    if (!planName) {
        alert("Plan Name is required.");
        return false;
    }

    // Validate duration
    const durationRegex = /^\d+\s*(month|months|year|years)$/i;
    if (!durationRegex.test(planDuration)) {
        alert("Please enter a valid duration (e.g., '6 months', '1 year').");
        return false;
    }

    // Validate price
    if (isNaN(planPrice) || parseFloat(planPrice) <= 0) {
        alert("Please enter a valid price greater than 0.");
        return false;
    }

    // Validate description
    if (!planDescription) {
        alert("Description is required.");
        return false;
    }

    return true;
}

    // Open Edit Dialog
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function () {
            const id = this.dataset.id;
            const name = this.dataset.name;
            const duration = this.dataset.duration;
            const price = this.dataset.price;
            const description = this.dataset.description;

            // Populate dialog fields
            document.getElementById('edit-plan-id').value = id;
            document.getElementById('edit-plan-name').value = name;
            document.getElementById('edit-plan-duration').value = duration;
            document.getElementById('edit-plan-price').value = price;
            document.getElementById('edit-plan-description').value = description;

            // Show dialog
            document.getElementById('edit-dialog').style.display = 'block';
        });
    });

    // Close Edit Dialog
    function closeEditDialog() {
        document.getElementById('edit-dialog').style.display = 'none';
    }

    // Close dialog when clicking outside
    window.onclick = function (event) {
        const dialog = document.getElementById('edit-dialog');
        if (event.target === dialog) {
            dialog.style.display = 'none';
        }
    };
</script>
<script>
    function validateForm() {
        const planName = document.getElementById("plan-name").value.trim();
        const planDuration = document.getElementById("plan-duration").value.trim();
        const planPrice = document.getElementById("plan-price").value.trim();
        const planDescription = document.getElementById("plan-description").value.trim();

        // Validate plan name
        if (!planName) {
            alert("Plan Name is required.");
            return false;
        }

        // Validate duration
        const durationRegex = /^\d+\s*(month|months|year|years)$/i;
        if (!durationRegex.test(planDuration)) {
            alert("Please enter a valid duration (e.g., '6 months', '1 year').");
            return false;
        }

        // Validate price
        if (isNaN(planPrice) || parseFloat(planPrice) <= 0) {
            alert("Please enter a valid price greater than 0.");
            return false;
        }

        // Validate description
        if (!planDescription) {
            alert("Description is required.");
            return false;
        }

        return true;
    }
</script>
<script src="../../public/js/script.js"></script>

</body>
</html>