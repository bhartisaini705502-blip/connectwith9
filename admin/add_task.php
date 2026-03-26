<!-- add_task.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Add Task</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="index.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .container {
            width: 60%;
            background: rgba(255, 255, 255, 0.5);
            color: black;
            padding: 30px;
            border-radius: 18px;
        }

        @media (max-width: 768px) {
            .container {
                width: 100%;
            }
        }

        .toggle-btn {
            position: fixed;
            top: 10px;
            left: 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            z-index: 1000;
        }

        .checkbox-group label {
            display: block;
        }
    </style>
</head>

<body>
    <?php include("sidebar.php"); ?>
    <button class="toggle-btn" onclick="toggleSidebar()">☰</button>

    <?php
    // Error Reporting
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    date_default_timezone_set('Asia/Kolkata');

    require_once 'common.php';
    require_once '../db.php';

    if (!isAdminLoggedIn()) {
        adminRedirect('index.php');
    }

    $errors = [];
    $success = "";
    $interests = $pdo->query("SELECT id, name FROM interests")->fetchAll(PDO::FETCH_ASSOC);

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // var_dump($_POST);
        // var_dump($_FILES);

        $task_title = trim($_POST["task_title"]);
        $task_description = trim($_POST["task_description"]);
        $reward_amount = $_POST["reward_amount"];
        $instructions = trim($_POST["instructions"]);
        $expiry_date = $_POST["expiry_date"];
        $max_submissions = $_POST["max_submissions"];
        $selected_interests = $_POST["interests"] ?? [];

        $video_path = null;
        $audio_path = null;

        // Validation
        if (empty($task_title)) $errors[] = "Task title is required.";
        if (!is_numeric($reward_amount) || $reward_amount < 0) $errors[] = "Reward amount must be valid.";
        if (empty($selected_interests)) $errors[] = "At least one interest must be selected.";
        if (empty($expiry_date)) $errors[] = "Expiry date is required.";
        if (!is_numeric($max_submissions) || $max_submissions < 1) $errors[] = "Max submissions must be valid.";

        // File Uploads
        $upload_dir = '../uploads/';
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

        if (!empty($_FILES['video_instruction']['name'])) {
            $video_filename = time() . "_" . basename($_FILES['video_instruction']['name']);
            $video_full_path = $upload_dir . $video_filename;
            if (move_uploaded_file($_FILES['video_instruction']['tmp_name'], $video_full_path)) {
                $video_path = 'uploads/' . $video_filename;
            } else {
                $errors[] = "Failed to upload video.";
            }
        }

        if (!empty($_FILES['audio_instruction']['name'])) {
            $audio_filename = time() . "_" . basename($_FILES['audio_instruction']['name']);
            $audio_full_path = $upload_dir . $audio_filename;
            if (move_uploaded_file($_FILES['audio_instruction']['tmp_name'], $audio_full_path)) {
                $audio_path = 'uploads/' . $audio_filename;
            } else {
                $errors[] = "Failed to upload audio.";
            }
        }

       // Insert into DB
if (empty($errors)) {
    $interest_ids = implode(",", $selected_interests);
    $is_approved = isset($_POST["is_approved"]) ? 1 : 0;

    $stmt = $pdo->prepare("INSERT INTO tasks (
        title, description, reward_amount, instructions, interest_id, expiry_date,
        max_submissions, current_submissions, video_instruction, audio_instruction, is_approved, created_at, updated_at
    ) VALUES (
        :title, :description, :reward_amount, :instructions, :interest_id, :expiry_date,
        :max_submissions, 0, :video_instruction, :audio_instruction, :is_approved, NOW(), NOW()
    )");

    if ($stmt->execute([
        ':title' => $task_title,
        ':description' => $task_description,
        ':reward_amount' => $reward_amount,
        ':instructions' => $instructions,
        ':interest_id' => $interest_ids, // keep this, it doesn't reference leads now
        ':expiry_date' => $expiry_date,
        ':max_submissions' => $max_submissions,
        ':video_instruction' => $video_path,
        ':audio_instruction' => $audio_path,
        ':is_approved' => $is_approved
    ])) {
        $success = "Task added successfully!";
    } else {
        $errors[] = "Database error: " . implode(" | ", $stmt->errorInfo());
    }
}

    }
    ?>

    <div class="main-content">
        <h2 class="text-center"><b>Add New Task</b></h2>
        <div class="container">
            <?php if ($errors): ?>
                <div class="alert alert-danger">
                    <ul><?php foreach ($errors as $e) echo "<li>$e</li>"; ?></ul>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success; ?></div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Task Title</label>
                    <input type="text" name="task_title" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Task URL</label>
                    <input type="url" name="task_description" class="form-control">
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
                    <label class="form-label">Upload Video Instructions</label>
                    <input type="file" name="video_instruction" class="form-control" accept="video/*">
                </div>

                <div class="mb-3">
                    <label class="form-label">Upload Audio Instructions</label>
                    <input type="file" name="audio_instruction" class="form-control" accept="audio/*">
                </div>

                <div class="mb-3">
                    <label class="form-label">Expiry Date</label>
                    <input type="datetime-local" name="expiry_date" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Max Submissions</label>
                    <input type="number" name="max_submissions" class="form-control" required>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="is_approved" name="is_approved" value="1">
                        <label class="form-check-label" for="is_approved">Mark as Approved</label>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Select Interests</label>
                    <div class="checkbox-group">
                        <?php foreach ($interests as $interest): ?>
                            <label>
                                <input type="checkbox" name="interests[]" value="<?= $interest['id']; ?>">
                                <?= htmlspecialchars($interest['name']); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">Add Task</button>
            </form>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("active");
        }
    </script>
</body>
</html>
