<?php
// task_details.php
// Displays detailed information about a task

session_start();
require_once 'db.php';
require_once 'functions.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("Task ID not provided.");
}

$task_id = intval($_GET['id']);
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
$stmt->execute([$task_id]);
$task = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$task) {
    die("Task not found.");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Task Details</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .task-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="task-container">
            <h2 class="text-center"><?php echo $task['title']; ?></h2>
            <p><?php echo $task['description']; ?></p>
            <p><strong>Reward:</strong> Rs. <?php echo $task['reward_amount']; ?></p>
            <p><strong>Instructions:</strong> <?php echo $task['instructions']; ?></p>
            
            <p class="mt-3 text-center"><a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a></p>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>