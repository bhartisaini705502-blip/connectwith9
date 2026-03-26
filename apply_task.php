<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db.php';
require_once 'functions.php';

// Ensure the user is logged in
if (!isset($_SESSION['user'])) {
    $_SESSION['error'] = "❌ Please log in to apply for tasks.";
    header("Location: login.php");
    exit();
}

// Ensure task ID is received
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id'])) {
    $user_id = intval($_SESSION['user']);
    $task_id = intval($_POST['task_id']);

    echo "✅ Task ID received: $task_id<br>";

    // Fetch task details
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
    $stmt->execute([$task_id]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$task) {
        die("❌ Task not found.");
    }

    echo "✅ Task found: " . htmlspecialchars($task['title']) . "<br>";

    // Fetch user details
    $stmt = $pdo->prepare("SELECT email, phone_no FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("❌ User not found in database.");
    }

    echo "✅ User found: " . htmlspecialchars($user['email']) . "<br>";

    // Check if the user has already applied
    $stmt = $pdo->prepare("SELECT id FROM task_applications WHERE user_id = ? AND task_id = ?");
    $stmt->execute([$user_id, $task_id]);

    if ($stmt->fetch()) {
        die("❌ You have already applied for this task.");
    }

    echo "✅ User has not applied before. Proceeding...<br>";

    // Insert application
    $stmt = $pdo->prepare("INSERT INTO task_applications (user_id, email, phone_no, task_id, task_title, applied_at) 
                           VALUES (?, ?, ?, ?, ?, NOW())");

    $result = $stmt->execute([$user_id, $user['email'], $user['phone_no'], $task_id, $task['title']]);

    if ($result) {
        echo "✅ Task application inserted successfully.";
        $_SESSION['success'] = "You have successfully applied for the task!";
        header("Location: dashboard.php");
        exit();
    } else {
        die("❌ Error inserting application.");
    }
} else {
    die("❌ Invalid request.");
}
?>
