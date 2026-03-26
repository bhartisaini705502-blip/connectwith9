<?php
session_start(); // Start the session

// Database Connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "microjobs";

require_once 'functions.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['user'])) {
    die("User not logged in properly.");
}
$user_id = $_SESSION['user'];

// Fetch task
$task_id = intval($_GET['task_id'] ?? 0);
if (!$task_id) {
    die("Task ID not provided.");
}

// Check if the task exists
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
$stmt->execute([$task_id]);
$task = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$task) {
    die("Task not found.");
}

$expired = isset($task['expiry_date']) && strtotime($task['expiry_date']) < time();

// Check if already applied
$stmt = $pdo->prepare("SELECT * FROM task_applications WHERE task_id = ? AND user_id = ?");
$stmt->execute([$task_id, $user_id]);
$application = $stmt->fetch(PDO::FETCH_ASSOC);

$applied = $application ? true : false;
$task_status = $application['task_status'] ?? null;
$task_application_id = $application['id'] ?? null;

// Apply logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply'])) {
    if (!$applied) {
        // Fetch user data for application
        $user_stmt = $pdo->prepare("SELECT email, phone_no FROM users WHERE id = ?");
        $user_stmt->execute([$user_id]);
        $user_data = $user_stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("INSERT INTO task_applications (task_id, user_id, applied_at, email, phone_no, task_title) VALUES (?, ?, NOW(), ?, ?, ?)");
        $stmt->execute([$task_id, $user_id, $user_data['email'], $user_data['phone_no'], $task['title']]);

        $applied = true;
        // Fetch the updated application details
        $stmt = $pdo->prepare("SELECT * FROM task_applications WHERE task_id = ? AND user_id = ?");
        $stmt->execute([$task_id, $user_id]);
        $application = $stmt->fetch(PDO::FETCH_ASSOC);
        $task_status = $application['task_status'] ?? null;
        $task_application_id = $application['id'] ?? null;
    }
}

// Task submission logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_task'])) {
    // Ensure lead_id (task_id) exists in the leads table
    $stmt = $pdo->prepare("SELECT id FROM leads WHERE task_id = ?");
    $stmt->execute([$task_id]);
    $lead = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$lead) {
        die("The specified lead does not exist for this task.");
    }

    // Handle task submission
    $submission_urls = $_POST['submission_urls'] ?? [];
    $uploadedPaths = [];
    $client_name = trim($_POST['client_name'] ?? '');
    $client_details = trim($_POST['client_details'] ?? '');
    $client_contact = trim($_POST['client_contact'] ?? '');

    // Handle file uploads (screenshots)
    if (!empty($_FILES['screenshots']['name'][0])) {
        $uploadDir = 'uploads/screenshots/';
        foreach ($_FILES['screenshots']['tmp_name'] as $key => $tmpName) {
            $filename = basename($_FILES['screenshots']['name'][$key]);
            $targetPath = $uploadDir . time() . '_' . $filename;
            if (move_uploaded_file($tmpName, $targetPath)) {
                $uploadedPaths[] = $targetPath;
            }
        }
    }

    // Convert URLs and screenshots arrays to strings
    $urlsStr = implode(',', array_map('trim', $submission_urls));
    $screenshotsStr = implode(',', $uploadedPaths);

    // Insert the submission into the database
    $reward_amount = $task['reward_amount'] ?? 0; // Get reward from task

$stmt = $pdo->prepare("INSERT INTO leads_submissions (lead_id, user_id, submitted_at, status, url, screenshot, client_name, client_details, client_contact, reward_amount) VALUES (?, ?, NOW(), 'pending', ?, ?, ?, ?, ?, ?)");
$res = $stmt->execute([$lead['id'], $user_id, $urlsStr, $screenshotsStr, $client_name, $client_details, $client_contact, $reward_amount]);

    if($res){
        echo"<script>alert('Lead submission successful')</script>";
    }
    else{
        echo"<script>alert('Lead submission failed')</script>";
    }

}

// Comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_text'])) {
    $commentText = trim($_POST['comment_text']);
    $parentId = $_POST['parent_id'] ?? null;
    if (!empty($commentText)) {
        $stmt = $pdo->prepare("INSERT INTO comments (task_id, name, email, comment, parent_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $task_id,
            $_SESSION['user_name'] ?? 'Anonymous',
            $_SESSION['user_email'] ?? '',
            $commentText,
            $parentId ?: null
        ]);
    }
}

// Get comments
$commentStmt = $pdo->prepare("SELECT * FROM comments WHERE task_id = ? ORDER BY created_at DESC");
$commentStmt->execute([$task_id]);
$allComments = $commentStmt->fetchAll(PDO::FETCH_ASSOC);

// Organize comments into a tree structure
$commentTree = [];
foreach ($allComments as $comment) {
    if ($comment['parent_id']) {
        $commentTree[$comment['parent_id']]['replies'][] = $comment;
    } else {
        $commentTree[$comment['id']] = $comment;
        $commentTree[$comment['id']]['replies'] = [];
    }
}
$expiry_date = isset($task['expiry_date']) ? $task['expiry_date'] : 'N/A';  // Set a default value if not found
?>

<!-- Continue with the rest of the HTML code (same as before) -->


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Details</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>

    <style>
        body {
            background-color: #f0f4f8;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .task-container {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 80px 20px;
        }

        .task-card {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            width: 100%;
        }

        .task-card h2 {
            font-size: 2rem;
            font-weight: bold;
            color: #198754;
        }

        .task-card p,
        .task-card a {
            font-size: 1rem;
        }

        .task-card a {
            word-break: break-all;
        }

        video,
        audio {
            width: 100%;
            border-radius: 10px;
        }

        @media (max-width: 576px) {
            .task-card {
                padding: 25px;
            }

            .task-card h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Include Sidebar -->
    <?php include("sidebar.php"); ?>

    <!-- Toggle Button -->
    <button class="toggle-btn" onclick="toggleSidebar()">☰</button>

    <!-- Main Content -->
    <div class="main-content">

   
        <div class="task-card">
            <h2 class="text-center"><?= htmlspecialchars($task['title']) ?></h2>
            <?php
    echo "Task ID: $task_id";


    ?>

            <p><strong class="text-primary">Link:</strong> <a href="<?= htmlspecialchars($task['description']) ?>" target="_blank"><?= htmlspecialchars($task['description']) ?></a></p>
            <p><strong class="text-primary">Reward:</strong> ₹<?= htmlspecialchars($task['reward_amount']) ?></p>
            <p><small class="text-muted">Deadline: <?= $expiry_date ?></small></p>
            <p><strong class="text-primary">Instructions:</strong><br><?= nl2br(htmlspecialchars($task['instructions'])) ?></p>

            <?php if (!empty($task['video_instruction'])): ?>
                <p><strong class="text-primary">Video Instruction:</strong></p>
                <video controls>
                    <source src="<?= htmlspecialchars($task['video_instruction']) ?>" type="video/mp4">
                </video>
            <?php endif; ?>

            <?php if (!empty($task['audio_instruction'])): ?>
                <p><strong class="text-primary">Audio Instruction:</strong></p>
                <audio controls>
                    <source src="<?= htmlspecialchars($task['audio_instruction']) ?>" type="audio/mpeg">
                </audio>
            <?php endif; ?>

            <?php if (!$applied && !$expired): ?>
                <form method="post" class="mt-4">
                    <button type="submit" name="apply" class="btn btn-primary w-100">Apply for Task</button>
                </form>
            <?php elseif ($task_status === 'pending'): ?>
                <div class="alert alert-info mt-4">Your application is pending admin approval.</div>
            <?php elseif ($task_status === 'rejected'): ?>
                <div class="alert alert-danger mt-4">Your task application has been rejected.</div>
            <?php elseif ($task_status === 'approved'): ?>
                <form method="post" class="mt-4" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="submission_urls">Submission URLs:</label>
                        <div id="urlFields">
                            <input type="url" name="submission_urls[]" class="form-control mb-2" placeholder="Enter submission URL">
                        </div>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addUrlField()">+ Add More URLs</button>
                    </div>
                    <div class="mb-3">
                        <label for="screenshots">Upload Screenshot(s):</label>
                        <input type="file" name="screenshots[]" class="form-control" accept="image/*" multiple>
                    </div>
                    <div class="mb-3">
                        <label for="client_name">Client Name:</label>
                        <input type="text" name="client_name" class="form-control" placeholder="Enter client name" required>
                    </div>
                    <div class="mb-3">
                        <label for="client_details">Client Details:</label>
                        <textarea name="client_details" class="form-control" placeholder="Enter client details" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="client_contact">Client Contact:</label>
                        <input type="text" name="client_contact" class="form-control" placeholder="Enter client contact" required>
                    </div>
                    <button type="submit" name="submit_task" class="btn btn-success w-100">Submit Task</button>
                </form>
            <?php endif; ?>

            <!-- Comment Section -->
            <div class="mt-5">
                <h3>Comments</h3>
                <form method="post">
                    <textarea name="comment_text" class="form-control mb-2" placeholder="Write a comment..." required></textarea>
                    <button type="submit" class="btn btn-primary">Post Comment</button>
                </form>
                
                <?php if ($commentTree): ?>
                    <div class="mt-4">
                        <?php foreach ($commentTree as $comment): ?>
                            <div class="comment">
                                <div class="comment-text">
                                    <strong><?= htmlspecialchars($comment['name']) ?>:</strong>
                                    <p><?= nl2br(htmlspecialchars($comment['comment'])) ?></p>
                                </div>
                                <?php if (!empty($comment['replies'])): ?>
                                    <div class="replies">
                                        <?php foreach ($comment['replies'] as $reply): ?>
                                            <div class="reply">
                                                <strong><?= htmlspecialchars($reply['name']) ?>:</strong>
                                                <p><?= nl2br(htmlspecialchars($reply['comment'])) ?></p>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="js/script.js"></script>
</body>
</html>
