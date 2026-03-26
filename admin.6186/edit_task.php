<?php
// admin/edit_task.php
// Admin panel to edit a task

require_once 'common.php';
require_once '../db.php'; // Database connection

if (!isAdminLoggedIn()) {
    adminRedirect('index.php');
}

// Ensure a task ID is provided
if (!isset($_GET['task_id']) || empty($_GET['task_id'])) {
    die("Task ID is required.");
}

$task_id = intval($_GET['task_id']);

// Fetch task details
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
$stmt->execute([$task_id]);
$task = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$task) {
    die("Task not found.");
}

// Handle task update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $status = trim($_POST['status']);

    if (empty($title) || !in_array($status, ['active', 'completed'])) {
        $error = "Invalid input.";
    } else {
        $stmt = $pdo->prepare("UPDATE tasks SET title = ?, status = ? WHERE id = ?");
        $stmt->execute([$title, $status, $task_id]);

        // Redirect after updating
        header("Location: all_tasks.php?success=Task+Updated+Successfully");
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Task</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .edit-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="edit-container">
            <h2 class="text-center mb-4">Edit Task</h2>
            <?php if (isset($error)) { echo "<div class='alert alert-danger'>$error</div>"; } ?>

            <form method="POST" action="edit_task.php?task_id=<?php echo $task_id; ?>">
                <div class="mb-3">
                    <label class="form-label">Task Title:</label>
                    <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($task['title']); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Status:</label>
                    <select name="status" class="form-select" required>
                        <option value="active" <?php if ($task['status'] == 'active') echo 'selected'; ?>>Active</option>
                        <option value="completed" <?php if ($task['status'] == 'completed') echo 'selected'; ?>>Completed</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary w-100">Update Task</button>
            </form>

            <p class="mt-3 text-center"><a href="all_tasks.php" class="btn btn-secondary">Back to All Tasks</a></p>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
