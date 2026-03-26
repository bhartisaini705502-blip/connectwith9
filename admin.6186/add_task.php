<?php
// admin/add_task.php
// Page to add a new task

require_once 'common.php';
if (!isAdminLoggedIn()) {
    adminRedirect('index.php');
}

// Database connection
require_once '../db.php';

$errors = [];
$success = "";

// Fetch all interests using PDO
$interests = [];
$stmt = $pdo->query("SELECT id, name FROM interests");
$interests = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $task_title = trim($_POST["task_title"]);
    $task_description = trim($_POST["task_description"]);
    $reward_amount = $_POST["reward_amount"];
    $instructions = trim($_POST["instructions"]);
    // $status = $_POST["status"];
    $selected_interests = isset($_POST["interests"]) ? $_POST["interests"] : [];

    // Validation
    if (empty($task_title)) {
        $errors[] = "Task title is required.";
    }
    if (empty($task_description)) {
        $errors[] = "Task description is required.";
    }
    if (!is_numeric($reward_amount) || $reward_amount < 0) {
        $errors[] = "Reward amount must be a valid number.";
    }
    if (empty($instructions)) {
        $errors[] = "Instructions are required.";
    }
    if (empty($selected_interests)) {
        $errors[] = "At least one interest must be selected.";
    }
    

    // Insert into database if no errors
    if (empty($errors)) {
        $interest_ids = implode(",", $selected_interests); // Store selected interest IDs as comma-separated values

        $sql = "INSERT INTO tasks (title, description, reward_amount, instructions, interest_id, created_at, updated_at) 
                VALUES (:title, :description, :reward_amount, :instructions, :interest_id, NOW(), NOW())";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':title' => $task_title,
            ':description' => $task_description,
            ':reward_amount' => $reward_amount,
            ':instructions' => $instructions,
            ':interest_id' => $interest_ids,
            // ':status' => $status
        ]);

        $success = "Task added successfully!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add New Task</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .task-form-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .checkbox-group {
            display: flex;
            flex-wrap: wrap;
        }
        .checkbox-group label {
            margin-right: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="task-form-container">
            <h2 class="text-center">Add New Task</h2>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error) {
                            echo "<li>$error</li>";
                        } ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Task Title</label>
                    <input type="text" name="task_title" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Task Description</label>
                    <textarea name="task_description" class="form-control" rows="4" required></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Reward Amount</label>
                    <input type="number" name="reward_amount" class="form-control" step="0.01" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Instructions</label>
                    <textarea name="instructions" class="form-control" rows="3" required></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Select Interests</label>
                    <div class="checkbox-group">
                        <?php foreach ($interests as $interest): ?>
                            <label>
                                <input type="checkbox" name="interests[]" value="<?php echo $interest['id']; ?>"> 
                                <?php echo htmlspecialchars($interest['name']); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">Add Task</button>
            </form>

            <div class="text-center mt-3">
                <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
