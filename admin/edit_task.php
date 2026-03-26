<?php
// admin/edit_task.php
require_once 'common.php';
require_once '../db.php';

if (!isAdminLoggedIn()) {
    adminRedirect('index.php');
}

if (!isset($_GET['task_id']) || empty($_GET['task_id'])) {
    die("Task ID is required.");
}

$task_id = intval($_GET['task_id']);

// Fetch task
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
$stmt->execute([$task_id]);
$task = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$task) {
    die("Task not found.");
}

// Fetch all interests for dropdown
$interestStmt = $pdo->query("SELECT id, name FROM interests ORDER BY name ASC");
$allInterests = $interestStmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $reward_amount = trim($_POST['reward_amount']);
    $instructions = trim($_POST['instructions']);
    $status = trim($_POST['status']);
    $expiry_date = trim($_POST['expiry_date']);
    $max_submissions = trim($_POST['max_submissions']);
    $interest_id = (int) $_POST['interest_id'];
    $is_approved = isset($_POST['is_approved']) ? 1 : 0;

    if (
        empty($title) || 
        !in_array($status, ['active', 'inactive']) || 
        !is_numeric($reward_amount) || 
        (!empty($expiry_date) && !strtotime($expiry_date)) || 
        !is_numeric($max_submissions) ||
        !$interest_id
    ) {
        $error = "Please fill all fields correctly.";
    } else {
        $stmt = $pdo->prepare("UPDATE tasks 
            SET title = ?, description = ?, reward_amount = ?, instructions = ?, status = ?, expiry_date = ?, max_submissions = ?, interest_id = ?, is_approved = ?
            WHERE id = ?");
        $stmt->execute([
            $title,
            $description,
            $reward_amount,
            $instructions,
            $status,
            $expiry_date,
            $max_submissions,
            $interest_id,
            $is_approved,
            $task_id
        ]);

        header("Location: all_tasks.php?success=Task+Updated+Successfully");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Task</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="index.css">
    <style>
 body {
            background-color: #e3f2fd;
        }
        .edit-container {
            max-width: 700px;
            margin: 50px auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>
    <?php
    include("sidebar.php");
    ?>

    <!-- Toggle Button for Mobile -->
    <button class="toggle-btn" onclick="toggleSidebar()">☰</button>


  
    <div class="main-content">
    <div class="edit-container">
        <h2 class="text-center mb-4">Edit Task</h2>
        <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Task Title:</label>
                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($task['title']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Links:</label>
                <input type="text" name="description" class="form-control" value="<?= htmlspecialchars($task['description']) ?>" >
                
            </div>
            <div class="mb-3">
                <label class="form-label">Reward Amount (₹):</label>
                <input type="number" name="reward_amount" step="0.01" class="form-control" value="<?= htmlspecialchars($task['reward_amount']) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Instructions:</label>
                <textarea name="instructions" class="form-control" rows="3"><?= htmlspecialchars($task['instructions']) ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Status:</label>
                <select name="status" class="form-select">
                    <option value="active" <?= $task['status'] == 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $task['status'] == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Expiry Date (YYYY-MM-DD HH:MM:SS):</label>
                <input type="text" name="expiry_date" class="form-control" value="<?= htmlspecialchars($task['expiry_date']) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Max Submissions:</label>
                <input type="number" name="max_submissions" class="form-control" value="<?= htmlspecialchars($task['max_submissions']) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Task Interest Category:</label>
                <select name="interest_id" class="form-select" required>
                    <option value="">-- Select Interest --</option>
                    <?php foreach ($allInterests as $interest): ?>
                        <option value="<?= $interest['id'] ?>" <?= $task['interest_id'] == $interest['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($interest['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="is_approved" name="is_approved" <?= $task['is_approved'] ? 'checked' : '' ?>>
                <label class="form-check-label" for="is_approved">Approved Task</label>
            </div>
            <button type="submit" class="btn btn-primary w-100">Update Task</button>
        </form>

        <p class="mt-3 text-center"><a href="all_tasks.php" class="btn btn-secondary">Back to All Tasks</a></p>
    </div>
    </div>

    <style>
      
       
    </style>


    <!-- FontAwesome for Icons -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>

    <!-- Sidebar Toggle Script -->
    <script>
        function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("active");
        }
    </script>



    <!-- FontAwesome for Icons -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>

    
</body>

</html>