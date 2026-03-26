<?php
session_start();
require_once 'db.php'; // Database connection

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user'];

// Validate task_id
if (!isset($_POST['task_id']) || !is_numeric($_POST['task_id'])) {
    $_SESSION['error'] = "Invalid task ID.";
    header("Location: applied_task.php");
    exit();
}

$task_id = (int) $_POST['task_id'];

// Check if the task was previously rejected
$sql = "SELECT id FROM task_applications WHERE user_id = :user_id AND task_id = :task_id AND task_status = 'rejected'";
$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => $user_id, 'task_id' => $task_id]);
$taskApplication = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$taskApplication) {
    $_SESSION['error'] = "Invalid request or task is not eligible for reapplication.";
    header("Location: applied_task.php");
    exit();
}

// Update task status to 'pending'
$updateSql = "UPDATE task_applications SET task_status = 'pending' WHERE user_id = :user_id AND task_id = :task_id";
$updateStmt = $pdo->prepare($updateSql);

if ($updateStmt->execute(['user_id' => $user_id, 'task_id' => $task_id])) {
    $_SESSION['success'] = "You have successfully reapplied for the task.";
} else {
    $_SESSION['error'] = "Failed to reapply due to a database error.";
}

header("Location: dashboard.php");
exit();
?>
