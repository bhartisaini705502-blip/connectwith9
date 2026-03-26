<?php
// admin/all_tasks.php
// Admin panel to manage all tasks

require_once 'common.php';
require_once '../db.php'; // Database connection

if (!isAdminLoggedIn()) {
    adminRedirect('index.php');
}

$search = "";
$status_filter = "";

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';
}

// Fetch tasks based on search and status filter
$query = "SELECT * FROM tasks WHERE 1";

if (!empty($search)) {
    $query .= " AND (id = :search OR title LIKE :search_like)";
}

if (!empty($status_filter) && in_array($status_filter, ['active', 'completed'])) {
    $query .= " AND status = :status_filter";
}

$query .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($query);

if (!empty($search)) {
    $stmt->bindValue(':search', $search, PDO::PARAM_INT);
    $stmt->bindValue(':search_like', "%$search%", PDO::PARAM_STR);
}

if (!empty($status_filter)) {
    $stmt->bindValue(':status_filter', $status_filter, PDO::PARAM_STR);
}

$stmt->execute();
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle task deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_task'])) {
    $task_id = intval($_POST['task_id']);

    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
    $stmt->execute([$task_id]);

    header("Location: all_tasks.php?success=Task+Deleted+Successfully");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>All Tasks</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 30px;
        }
        .table-container {
            margin-top: 20px;
            background: #fff;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center mb-4">All Tasks</h2>

        <form method="GET" action="all_tasks.php" class="text-center mb-3">
            <input type="text" name="search" class="form-control d-inline w-auto" placeholder="Search by Task ID or Title" value="<?php echo htmlspecialchars($search); ?>" required>
            <select name="status" class="form-select d-inline w-auto">
                <option value="">All Status</option>
                <option value="active" <?php if ($status_filter == 'active') echo 'selected'; ?>>Active</option>
                <option value="completed" <?php if ($status_filter == 'completed') echo 'selected'; ?>>Completed</option>
            </select>
            <button type="submit" class="btn btn-primary">Search</button>
            <a href="all_tasks.php" class="btn btn-secondary">Reset</a>
        </form>

        <?php if (isset($_GET['success'])) { ?>
            <div class="alert alert-success text-center"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php } ?>

        <div class="table-container">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tasks)) { ?>
                        <tr><td colspan="5" class="text-center">No tasks found.</td></tr>
                    <?php } else { ?>
                        <?php foreach ($tasks as $task) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($task['id']); ?></td>
                                <td><?php echo htmlspecialchars($task['title']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo ($task['status'] == 'active') ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($task['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($task['created_at']); ?></td>
                                <td>
                                    <a href="edit_task.php?task_id=<?php echo $task['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <form method="POST" action="all_tasks.php" style="display:inline;">
                                        <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                        <button type="submit" name="delete_task" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <p class="text-center mt-3"><a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a></p>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
