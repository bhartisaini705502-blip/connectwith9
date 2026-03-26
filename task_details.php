<?php
session_start();
date_default_timezone_set('Asia/Kolkata'); // Set your desired timezone


require_once 'functions.php';

// Database Connection
$servername = "localhost";
$username = "u647904474_microjobs";
$password = "TechTrick@1234#";
$database = "u647904474_microjobs";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// User session check
if (!isLoggedIn() || !isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
function timeAgo($timestamp)
{
    // Create DateTime object using Asia/Kolkata timezone
    $dateTime = new DateTime("@$timestamp");
    $dateTime->setTimezone(new DateTimeZone('Asia/Kolkata'));

    $time = time() - $timestamp;
    $diff = abs($time);

    if ($diff < 60) {
        return $diff . ' seconds ago';
    }
    if ($diff < 3600) {
        return floor($diff / 60) . ' minutes ago';
    }
    if ($diff < 86400) {
        return floor($diff / 3600) . ' hours ago';
    }
    if ($diff < 604800) {
        return floor($diff / 86400) . ' days ago';
    }
    return $dateTime->format('d M Y, h:i A');
}

$user_id = $_SESSION['user'];

// Fetch task
$task_id = intval($_GET['task_id'] ?? 0);
if (!$task_id)
    die("Task ID not provided.");

$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
$stmt->execute([$task_id]);
$task = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$task)
    die("Task not found.");

$expired = isset($task['expiry_date']) && strtotime($task['expiry_date']) < time();
$reward = $task['reward_amount'];
$expiry_date = $task['expiry_date'];

// Application status
$stmt = $pdo->prepare("SELECT * FROM task_applications WHERE task_id = ? AND user_id = ?");
$stmt->execute([$task_id, $user_id]);
$application = $stmt->fetch(PDO::FETCH_ASSOC);

$applied = $application ? true : false;
$task_status = $application['task_status'] ?? null;
$task_application_id = $application['id'] ?? null;

$checkSubmittedStmt = $pdo->prepare("SELECT COUNT(*) FROM submissions WHERE task_id = ? AND user_id = ?");
$checkSubmittedStmt->execute([$task_id, $user_id]);
$userAlreadySubmitted = $checkSubmittedStmt->fetchColumn() > 0;

// Apply for task
if (!$applied && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply'])) {
    $user_stmt = $pdo->prepare("SELECT email, phone_no FROM users WHERE id = ?");
    $user_stmt->execute([$user_id]);
    $user_data = $user_stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("INSERT INTO task_applications (task_id, user_id, applied_at, email, phone_no, task_title) VALUES (?, ?, NOW(), ?, ?, ?)");
    $stmt->execute([$task_id, $user_id, $user_data['email'], $user_data['phone_no'], $task['title']]);
    header("Location: task_details.php?task_id=$task_id");
    exit();
}

// Submit task
$submitted = false;
$error = "";
$userAlreadySubmitted = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_task'])) {
    $submission_urls = $_POST['submission_urls'] ?? [];
    $uploadedPaths = [];

    // Check for at least one valid URL
    $hasValidUrl = false;
    foreach ($submission_urls as $url) {
        if (filter_var(trim($url), FILTER_VALIDATE_URL)) {
            $hasValidUrl = true;
            break;
        }
    }

    // Check if at least one screenshot is uploaded
    $hasScreenshot = !empty($_FILES['screenshots']['name'][0]);
    // $details = isset($_POST['details']) ? $_POST['details'] : '';


    if (!$hasValidUrl && !$hasScreenshot) {
        $error = "Please provide at least one valid URL or upload a screenshot.";
    } else {
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM submissions WHERE task_id = ? AND user_id = ?");
        $checkStmt->execute([$task_id, $user_id]);

        if ($checkStmt->fetchColumn() > 0) {
            $error = "Already submitted.";
            $userAlreadySubmitted = true;
        } else {

            $details = isset($_POST['details']) ? $_POST['details'] : '';
            $stmt = $pdo->prepare("INSERT INTO submissions (user_id, task_id, submitted_at, details, status) VALUES (?, ?, NOW(), ?, 'pending')");
            $stmt->execute([$user_id, $task_id, $details]);


            // $stmt = $pdo->prepare("INSERT INTO submissions (user_id, task_id, submitted_at,details, status) VALUES (?, ?, NOW(),$details, 'pending')");
            // $stmt->execute([$user_id, $task_id]);
            $submission_id = $pdo->lastInsertId();

            // Handle screenshots
            if (!empty($_FILES['screenshots']['name'][0])) {
                $uploadDir = 'uploads/screenshots/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                foreach ($_FILES['screenshots']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['screenshots']['error'][$key] == 0) {
                        $filename = time() . "_" . basename($_FILES['screenshots']['name'][$key]);
                        $targetFile = $uploadDir . $filename;

                        if (move_uploaded_file($tmp_name, $targetFile)) {
                            $uploadedPaths[] = $targetFile;

                            // Insert file path into `submission_files` table
                            $stmt = $pdo->prepare("INSERT INTO submission_files (submission_id, file_path) VALUES (?, ?)");
                            $stmt->execute([$submission_id, $targetFile]);

                            // Update submission table with the first screenshot path
                            if (count($uploadedPaths) == 1) {
                                $stmt = $pdo->prepare("UPDATE submissions SET screenshot_path = ? WHERE id = ?");
                                $stmt->execute([$targetFile, $submission_id]);
                            }
                        }
                    }
                }
            }

            // Handle URLs
            foreach ($submission_urls as $url) {
                $cleanUrl = trim($url);
                if (filter_var($cleanUrl, FILTER_VALIDATE_URL)) {
                    $stmt = $pdo->prepare("INSERT INTO submission_urls (submission_id, url) VALUES (?, ?)");
                    $stmt->execute([$submission_id, $cleanUrl]);
                }
            }

            $success = "Submission successful!";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_text'])) {
    $commentText = trim($_POST['comment_text']);
    $parentId = $_POST['parent_id'] ?? null; // Parent ID for replies

    if (!empty($commentText)) {
        $fields = ['task_id', 'comment', 'parent_id'];
        $values = [$task_id, $commentText, $parentId];

        if (isset($_SESSION['user'])) {
            $user_id = $_SESSION['user'];
            $user_name = $_SESSION['user_name'] ?? 'User';

            $fields[] = 'user_id';
            $fields[] = 'user_name';

            $values[] = $user_id;
            $values[] = $user_name;
        }

        try {
            $placeholders = implode(', ', array_fill(0, count($fields), '?'));
            $columns = implode(', ', $fields);
            $stmt = $pdo->prepare("INSERT INTO comments ($columns) VALUES ($placeholders)");
            $stmt->execute($values);
        } catch (PDOException $e) {
            die("Error inserting comment: " . $e->getMessage());
        }
    }
}

// Fetch all comments
$commentStmt = $pdo->prepare("SELECT * FROM comments WHERE task_id = ? ORDER BY created_at ASC");
$commentStmt->execute([$task_id]);
$allComments = $commentStmt->fetchAll(PDO::FETCH_ASSOC);

// Initialize arrays to store parent and reply relationships
$commentTree = [];
$replies = [];

// Loop through all comments and separate replies from top-level comments
foreach ($allComments as $comment) {
    $comment['replies'] = [];
    if ($comment['parent_id']) {
        $replies[$comment['id']] = $comment;
    } else {
        $commentTree[$comment['id']] = $comment;
    }
}

// Build the nested replies
foreach ($replies as $reply) {
    if (isset($commentTree[$reply['parent_id']])) {
        $commentTree[$reply['parent_id']]['replies'][] = $reply;
    }
}

// Now, render the comments (including replies) as needed


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Details</title>
    <link rel="stylesheet" href="css/style.css"> <!-- Include global styles -->
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

            <p><strong class="text-primary">Link:</strong> <a href="<?= htmlspecialchars($task['description']) ?>"
                    target="_blank"><?= htmlspecialchars($task['description']) ?></a></p>
            <p><strong class="text-primary">Reward:</strong> ₹<?= htmlspecialchars($task['reward_amount']) ?></p>
            <p><small class="text-muted">Deadline: <?= $expiry_date ?> | Reward: ₹<?= $reward ?></small>
            </p>
            <p><strong
                    class="text-primary">Instructions:</strong><br><?= nl2br(htmlspecialchars($task['instructions'])) ?>
            </p>

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
            <?php elseif ($userAlreadySubmitted): ?>
                <div class="alert alert-warning mt-4">You have already submitted this task. Thank you!</div>
                <!--  -->

                <!--  -->
            <?php elseif ($task_status === 'approved' && !$userAlreadySubmitted): ?>
                <form method="post" class="mt-4" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="submission_urls">Submission URLs:</label>
                        <div id="urlFields">
                            <input type="url" name="submission_urls[]" class="form-control mb-2"
                                placeholder="Enter submission URL">
                        </div>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addUrlField()">+ Add More
                            URLs</button>
                    </div>
                    <div class="mb-3">
                        <label for="screenshots">Upload Screenshot(s):</label>
                        <input type="file" name="screenshots[]" class="form-control" accept="image/*" multiple>
                    </div>
                    <div class="mb-3">
                        <label for="details">Task Details:</label>
                        <textarea name="details" class="form-control" placeholder="Enter additional details..."
                            rows="4"></textarea>
                    </div>

                    <?php
                    // Correctly determine if the user already submitted the task
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM submissions WHERE task_id = ? AND user_id = ?");
                    $stmt->execute([$task_id, $user_id]);
                    $userAlreadySubmitted = $stmt->fetchColumn() > 0;
                    ?>
                    <?php if ($task_status === 'approved' && !$userAlreadySubmitted): ?>
                        <button type="submit" name="submit_task" class="btn btn-success w-100">Submit Task</button>
                    <?php else: ?>
                        <button type="submit" class="btn btn-success w-100" disabled>Submitted</button>
                    <?php endif; ?>
                </form>
            <?php elseif ($task_status === 'approved' && $userAlreadySubmitted): ?>
                <div class="alert alert-warning mt-4">You have already submitted this task. Thank you!</div>
            <?php endif; ?>

            <?php if (!empty($submitted)): ?>
                <div class="alert alert-success mt-3">Task submitted successfully!</div>
            <?php elseif (!empty($error)): ?>
                <div class="alert alert-danger mt-3"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="mt-5">
                <h4>Comments</h4>
                <!-- Comment Form -->
                <form method="POST" class="mb-4">
                    <input type="hidden" name="action" value="add_comment">
                    <div class="mb-2">
                        <textarea name="comment_text" class="form-control" placeholder="Write a comment..."
                            required></textarea>
                    </div>
                    <button type="submit" class="btn btn-outline-primary">Post Comment</button>
                </form>
                <?php foreach ($commentTree as $comment): ?>
                    <div class="card mb-3 shadow-sm">
                        <div class="card-body">
                            <!-- Parent Comment -->
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-user-circle fa-lg text-primary me-2"></i>
                                    <strong><?= htmlspecialchars($comment['user_name'] ?? 'Admin') ?></strong>
                                </div>
                                <!-- <small class="text-muted"> -->
                                <!-- <i class="far fa-clock me-1"></i> -->
                                <?
                                // = timeAgo(strtotime($comment['created_at']))
                                ?>
                                <!-- </small> -->
                            </div>
                            <p class="mb-2"><?= nl2br(htmlspecialchars($comment['comment'])) ?></p>

                            <!-- Display replies if they exist -->
                            <?php if (!empty($comment['replies'])): ?>
                                <div class="ms-4">
                                    <?php foreach ($comment['replies'] as $reply): ?>
                                        <div class="mb-3">
                                            <strong><?= htmlspecialchars($reply['user_name'] ?? 'Admin') ?>:</strong>
                                            <p><?= nl2br(htmlspecialchars($reply['comment'])) ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>




                <a href="dashboard.php" class="btn btn-primary mt-4 w-100">Back to Dashboard</a>

            </div>
        </div>
    </div>

    <script>
        function addUrlField() {
            const urlFields = document.getElementById('urlFields');
            const newInput = document.createElement('input');
            newInput.type = 'url';
            newInput.name = 'submission_urls[]';
            newInput.className = 'form-control mb-2';
            newInput.placeholder = 'Enter submission URL';
            urlFields.appendChild(newInput);
        }

        function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("active");
        }

        function replyTo(commentId) {
            const textarea = document.querySelector('textarea[name="comment_text"]');
            const parentField = document.querySelector('input[name="parent_id"]');
            textarea.focus();
            parentField.value = commentId;
        }
    </script>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>