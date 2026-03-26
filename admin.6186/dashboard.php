<?php
// admin/dashboard.php
// Admin dashboard overview

require_once 'common.php';
if (!isAdminLoggedIn()) {
    adminRedirect('index.php');
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .dashboard-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .list-group-item a {
            display: block;
            padding: 10px;
            font-size: 16px;
        }
        .add-task-btn {
            text-align: center;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-container">
            <h2 class="text-center">Admin Dashboard</h2>
            <ul class="list-group">
                <li class="list-group-item"><a href="all_tasks.php" class="text-decoration-none">All Tasks</a></li>
                <li class="list-group-item"><a href="tasks.php" class="text-decoration-none">Review Task Submissions</a></li>
                <li class="list-group-item"><a href="task_applications.php" class="text-decoration-none">Task Applications Approval</a></li>
                <li class="list-group-item"><a href="withdrawal_requests.php" class="text-decoration-none">Withdrawal Requests</a></li>
                <li class="list-group-item"><a href="admin_management.php" class="text-decoration-none">Admin Management</a></li>
                <li class="list-group-item"><a href="manage_users.php" class="text-decoration-none">Manage Users</a></li>
                <li class="list-group-item"><a href="logout.php" class="text-decoration-none text-danger">Logout</a></li>
            </ul>
            <div class="add-task-btn">
                <a href="add_task.php" class="btn btn-primary">+ Add New Task</a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
