<?php
require_once '../db.php'; // Connect to your DB
session_start();
$query = "
    SELECT * FROM leads 
    WHERE task_id NOT IN (SELECT id FROM tasks)
";
$leadsToDelete = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);

if (empty($leadsToDelete)) {
    echo "No orphaned leads to delete.";
} else {
    echo count($leadsToDelete) . " orphaned lead(s) found. Proceeding with deletion...";
    // Execute DELETE if necessary
}
?>
