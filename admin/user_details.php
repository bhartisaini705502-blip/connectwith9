<?php
require_once 'common.php';
require_once '../db.php';

if (!isAdminLoggedIn()) {
    adminRedirect('index.php');
}

// Get user ID from request
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
if ($user_id <= 0) {
    die("Invalid user ID.");
}

// Fetch user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :user_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found.");
}

// Fetch applied tasks by user from task_applications
$taskStmt = $pdo->prepare("SELECT * FROM task_applications WHERE user_id = :user_id ORDER BY applied_at DESC");
$taskStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$taskStmt->execute();
$tasks = $taskStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Details</title>
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #e3f2fd;
        }
        .task-card {
            background-color: #ffffff54;
            padding: 10px;
            border-radius: 8px;
        }
    </style>
</head>
<body>
<?php include("sidebar.php"); ?>
<div class="main-content">
    <h2 class="text-center">User Details</h2>

    <div class="card" style="background-color:#ffffff54;">
        <div class="card-body">
            <h5 class="card-title">ID: <?php echo htmlspecialchars($user['id']); ?></h5>
            <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Status:</strong> 
                <span class="badge bg-<?php echo ($user['status'] == 'active') ? 'success' : 'secondary'; ?>">
                    <?php echo ucfirst($user['status']); ?>
                </span>
            </p>
            <p><strong>Registered On:</strong> <?php echo date("d M Y, h:i A", strtotime($user['created_at'])); ?></p>
            <a href="manage_users.php" class="btn btn-secondary">Back to Users</a>
        </div>
    </div>

    <h3 class="mt-4">Applied Tasks</h3>
    <?php if (empty($tasks)) { ?>
        <div class="alert alert-info">No tasks applied by this user.</div>
    <?php } else { ?>
        <div class="row">
            <?php foreach ($tasks as $task) { ?>
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card task-card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($task['task_title']); ?></h5>
                            <p><strong>Applied On:</strong> <?php echo date("d M Y, h:i A", strtotime($task['applied_at'])); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($task['email']); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($task['phone_no']); ?></p>
                            <p><strong>Task Status:</strong> 
                                <span class="badge bg-<?php echo ($task['task_status'] == 'approved') ? 'success' : (($task['task_status'] == 'pending') ? 'warning' : 'danger'); ?>">
                                    <?php echo ucfirst($task['task_status']); ?>
                                </span>
                            </p>
                            <p><strong>Overall Status:</strong> 
                                <span class="badge bg-<?php echo ($task['status'] == 'active') ? 'primary' : 'secondary'; ?>">
                                    <?php echo ucfirst($task['status']); ?>
                                </span>
                            </p>
                            <a href="task_details.php?task_id=<?php echo $task['task_id']; ?>" class="btn btn-primary btn-sm">View Task</a>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
</div>
</body>
</html>
