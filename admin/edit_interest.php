<?php
require_once 'common.php';
if (!isAdminLoggedIn()) {
    adminRedirect('index.php');
}

require_once '../db.php';

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Interest ID.");
}

$interest_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM interests WHERE id = :id");
$stmt->execute([':id' => $interest_id]);
$interest = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$interest) {
    die("Interest not found.");
}

$errors = [];
$success = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_name = trim($_POST["name"]);
    if (empty($new_name)) {
        $errors[] = "Interest name is required.";
    } else {
        $stmt = $pdo->prepare("UPDATE interests SET name = :name WHERE id = :id");
        $stmt->execute([':name' => $new_name, ':id' => $interest_id]);
        $success = "Interest updated successfully!";
        // Reload updated data
        $interest['name'] = $new_name;
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Interest</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="index.css">
    <style>

    </style>
</head>

<body>
    <?php
    include("sidebar.php");
    ?>

    <!-- Toggle Button for Mobile -->
    <button class="toggle-btn" onclick="toggleSidebar()">☰</button>


  
    <div class="main-content">
    <div class="container mt-4">
        <h2>Edit Interest</h2>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger"><?php foreach ($errors as $error) echo "<p>$error</p>"; ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="post">
            <div class="mb-3">
                <label class="form-label">Interest Name</label>
                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($interest['name']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="add_interest.php" class="btn btn-secondary">Back</a>
        </form>
    </div>
    </div>

    <!-- FontAwesome for Icons -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>

    <!-- Sidebar Toggle Script -->
    <script>
        function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("active");
        }
    </script>

    <!-- FontAwesome for Icons -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>    
</body>

</html>