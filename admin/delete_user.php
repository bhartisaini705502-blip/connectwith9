<?php
require_once 'common.php';
require_once '../db.php';

if (!isAdminLoggedIn()) {
    adminRedirect('index.php');
}

if (!isset($_GET['user_id'])) {
    header("Location: manage_users.php?error=invalid_user");
    exit();
}

$user_id = $_GET['user_id'];

try {
    $pdo->beginTransaction();

    // Fetch tables that reference `users`
    $tables = [
        'detailed_profile',
        'otps',
        'submissions',
        'submission_files',
        'submission_urls',
        'task_applications',
        'transactions',
        'user_interests',
        'wallet_transactions',
        'withdrawal_requests'
    ];

    // Check if `user_id` exists in each table before deleting
    foreach ($tables as $table) {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM $table LIKE 'user_id'");
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) { // If column `user_id` exists
            $deleteStmt = $pdo->prepare("DELETE FROM $table WHERE user_id = ?");
            if (!$deleteStmt->execute([$user_id])) {
                throw new Exception("Failed to delete from $table: " . implode(" | ", $deleteStmt->errorInfo()));
            }
        }
    }

    // Delete user only after related data is removed
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    if (!$stmt->execute([$user_id])) {
        throw new Exception("Failed to delete user: " . implode(" | ", $stmt->errorInfo()));
    }

    $pdo->commit();

    header("Location: manage_users.php?success=user_deleted");
    exit();
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("User deletion failed: " . $e->getMessage());

    // Debugging output - Remove this in production
    die("Error: " . $e->getMessage());  

    header("Location: manage_users.php?error=delete_failed");
    exit();
}
