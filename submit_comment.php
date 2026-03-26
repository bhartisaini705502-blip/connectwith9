<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user'];
$lead_id = $_POST['lead_id'];
$submission_id = $_POST['submission_id'];
$parent_id = $_POST['parent_id'] ?: null;
$comment = trim($_POST['comment']);

if (!empty($comment)) {
    $query = "INSERT INTO leads_comments (lead_id, submission_id, parent_id, user_id, is_admin, comment) 
              VALUES (?, ?, ?, ?, 0, ?)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$lead_id, $submission_id, $parent_id, $user_id, $comment]);
}

header("Location: lead_submission_details.php?id=$submission_id");
exit();
