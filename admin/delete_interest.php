
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

// Check if this interest is used in any task or user_interests
$stmt1 = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE FIND_IN_SET(:id, interest_id)");
$stmt1->execute([':id' => $interest_id]);
$task_count = $stmt1->fetchColumn();

$stmt2 = $pdo->prepare("SELECT COUNT(*) FROM user_interests WHERE interest_id = :id");
$stmt2->execute([':id' => $interest_id]);
$user_interest_count = $stmt2->fetchColumn();

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Interest</title>
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
        <?php

if ($task_count > 0 || $user_interest_count > 0) {
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Cannot Delete Interest</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    </head>
    <body>
        <div class='container mt-5'>
            <div class='alert alert-warning'>
                <h4 class='alert-heading'>Deletion Not Allowed</h4>
                <p>This interest is linked to <strong>$task_count</strong> task(s) and <strong>$user_interest_count</strong> user(s), and cannot be deleted.</p>
                <a href='add_interest.php' class='btn btn-secondary mt-2'>Go Back</a>
            </div>
        </div>
    </body>
    </html>";
    exit;
}

// Proceed with deletion
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stmt = $pdo->prepare("DELETE FROM interests WHERE id = :id");
    $stmt->execute([':id' => $interest_id]);
    header("Location: add_interest.php?deleted=1");
    exit();
}

?>
        <h2>Delete Interest</h2>
        <p>Are you sure you want to delete this interest?</p>
        <form method="post">
            <button type="submit" class="btn btn-danger">Delete</button>
            <a href="add_interest.php" class="btn btn-secondary">Cancel</a>
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