<?php
// admin/toggle_user_status.php
// Handles AJAX requests to toggle user status

require_once 'common.php';
require_once '../db.php'; // Database connection

if (!isAdminLoggedIn()) {
    die("Unauthorized Access");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_id']) && isset($_POST['status'])) {
    $user_id = intval($_POST['user_id']);
    $new_status = ($_POST['status'] === 'active') ? 'active' : 'inactive';

    $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
    if ($stmt->execute([$new_status, $user_id])) {
        echo "success";
    } else {
        echo "error";
    }
}
?>
