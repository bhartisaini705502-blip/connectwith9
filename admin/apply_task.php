<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt'); // Log errors to a file

require_once 'db.php';
require_once 'functions.php';

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    $_SESSION['error'] = "Please log in to apply for tasks.";
    header("Location: login.php");
    exit();
}

function getTaskById($task_id, $pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
        $stmt->execute([$task_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database Error (getTaskById): " . $e->getMessage());
        return null;
    }
}

function getUserById($user_id, $pdo) {
    try {
        $stmt = $pdo->prepare("SELECT email, phone_no FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database Error (getUserById): " . $e->getMessage());
        return null;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['task_id'])) {
    try {
        $user_id = intval($_SESSION['user']);
        $task_id = intval($_POST['task_id']);

        // Validate task existence
        $task = getTaskById($task_id, $pdo);
        if (!$task) {
            $_SESSION['error'] = "Task not found.";
            header("Location: dashboard.php");
            exit();
        }
        $task_title = $task['title'];

        // Validate user existence
        $user = getUserById($user_id, $pdo);
        if (!$user) {
            $_SESSION['error'] = "User not found.";
            header("Location: dashboard.php");
            exit();
        }
        $email = $user['email'];
        $phone_no = $user['phone_no'];

        // Check if the user has already applied
        $stmt = $pdo->prepare("SELECT id FROM task_applications WHERE user_id = ? AND task_id = ?");
        $stmt->execute([$user_id, $task_id]);
        if ($stmt->fetch()) {
            $_SESSION['error'] = "You have already applied for this task.";
            header("Location: dashboard.php");
            exit();
        }

        // Insert application
        $stmt = $pdo->prepare("INSERT INTO task_applications (user_id, email, phone_no, task_id, task_title, applied_at) VALUES (?, ?, ?, ?, ?, NOW())");
        if ($stmt->execute([$user_id, $email, $phone_no, $task_id, $task_title])) {
            $_SESSION['success'] = "You have successfully applied for the task!";
        } else {
            $_SESSION['error'] = "An error occurred while applying. Please try again.";
            error_log("Insert Error: " . implode(" | ", $stmt->errorInfo()));
        }

    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        error_log("Database Error (Apply Task): " . $e->getMessage());
    }
} else {
    $_SESSION['error'] = "Invalid request.";
    error_log("Invalid request to apply_task.php");
}

header("Location: dashboard.php");
exit();
?>
