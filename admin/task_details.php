<?php
session_start();

// Database Connection
$servername = "localhost";
$username = "u647904474_microjobs";
$password = "TechTrick@1234#";
$database = "u647904474_microjobs";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

if (!isset($_GET['task_id'])) {
    die("Task ID not provided.");
}
$task_id = intval($_GET['task_id']);

// Fetch task details
$sql = "SELECT * FROM tasks WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $task_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    die("Task not found.");
}
$task = $result->fetch_assoc();
$stmt->close();

$video_path = (!empty($task['video_instruction']) && file_exists(".." . DIRECTORY_SEPARATOR . $task['video_instruction'])) ? ".." . DIRECTORY_SEPARATOR . $task['video_instruction'] : false;
$audio_path = (!empty($task['audio_instruction']) && file_exists(".." . DIRECTORY_SEPARATOR . $task['audio_instruction'])) ? ".." . DIRECTORY_SEPARATOR . $task['audio_instruction'] : false;

// Handle New Comment Submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['comment'])) {
    $comment = trim($_POST['comment']);
    $parent_id = $_POST['parent_id'] ?? NULL;

    if (isset($_SESSION['user_name']) && isset($_SESSION['user_email'])) {
        $name = $_SESSION['user_name'];
        $email = $_SESSION['user_email'];
    } elseif (isset($_SESSION['admin_role'])) {
        $role = $_SESSION['admin_role'];
        $name = ($role === 'super_admin') ? 'Admin' : ucfirst(str_replace('_', ' ', $role));

        $email = $_SESSION['admin_role'] . '@connectwith9.com';
    } else {
        $name = 'Admin';
        $email = '';
    }

    if (!empty($comment)) {
        $stmt = $conn->prepare("INSERT INTO comments (task_id, name, email, comment, parent_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isssi", $task_id, $name, $email, $comment, $parent_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Handle Likes
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['like_comment_id'])) {
    $comment_id = intval($_POST['like_comment_id']);
    $ip_address = $_SERVER['REMOTE_ADDR'];

    $check = $conn->prepare("SELECT * FROM comment_likes WHERE comment_id = ? AND ip_address = ?");
    $check->bind_param("is", $comment_id, $ip_address);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows === 0) {
        $insert = $conn->prepare("INSERT INTO comment_likes (comment_id, ip_address) VALUES (?, ?)");
        $insert->bind_param("is", $comment_id, $ip_address);
        $insert->execute();
        $insert->close();
    }
    $check->close();
}

// Handle Comment Deletion
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_comment_id'])) {
    $delete_id = intval($_POST['delete_comment_id']);
    $stmt = $conn->prepare("SELECT email FROM comments WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $comment = $res->fetch_assoc();
        if (
            ($_SESSION['admin_role'] ?? '') === 'super_admin' ||
            ($comment['email'] ?? '') === ($_SESSION['user_email'] ?? '')
        ) {

            function deleteReplies($conn, $parent_id)
            {
                $stmt = $conn->prepare("SELECT id FROM comments WHERE parent_id = ?");
                $stmt->bind_param("i", $parent_id);
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    deleteReplies($conn, $row['id']);
                }
                $stmt = $conn->prepare("DELETE FROM comments WHERE parent_id = ?");
                $stmt->bind_param("i", $parent_id);
                $stmt->execute();
            }
            deleteReplies($conn, $delete_id);

            $stmt = $conn->prepare("DELETE FROM comment_likes WHERE comment_id = ?");
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();

            $stmt = $conn->prepare("DELETE FROM comments WHERE id = ?");
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();
        }
    }
    $stmt->close();
}

// Fetch Comments
function fetchComments($conn, $task_id, $parent_id = NULL, $limit = null)
{
    $sql = "SELECT * FROM comments WHERE task_id = ? AND parent_id " . (is_null($parent_id) ? "IS NULL" : "= ?") . " ORDER BY created_at DESC";
    if ($limit)
        $sql .= " LIMIT ?";
    if (is_null($parent_id)) {
        $stmt = $conn->prepare($limit ? "$sql" : str_replace(" LIMIT ?", "", $sql));
        if ($limit)
            $stmt->bind_param("ii", $task_id, $limit);
        else
            $stmt->bind_param("i", $task_id);
    } else {
        $stmt = $conn->prepare($limit ? "$sql" : str_replace(" LIMIT ?", "", $sql));
        if ($limit)
            $stmt->bind_param("iii", $task_id, $parent_id, $limit);
        else
            $stmt->bind_param("ii", $task_id, $parent_id);
    }
    $stmt->execute();
    return $stmt->get_result();
}

function timeAgo($datetime)
{
    $timestamp = strtotime($datetime);
    $time = time() - $timestamp;
    if ($time < 60)
        return $time . ' seconds ago';
    if ($time < 3600)
        return floor($time / 60) . ' minutes ago';
    if ($time < 86400)
        return floor($time / 3600) . ' hours ago';
    if ($time < 604800)
        return floor($time / 86400) . ' days ago';
    return date('d M Y, h:i A', $timestamp);
}

function countLikes($conn, $comment_id)
{
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM comment_likes WHERE comment_id = ?");
    $stmt->bind_param("i", $comment_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    return $res['total'] ?? 0;
}

function displayComments($conn, $task_id, $parent_id = NULL, $depth = 0)
{
    $is_reply = !is_null($parent_id);
    $limit = $is_reply ? 2 : null;
    $comments = fetchComments($conn, $task_id, $parent_id, $limit);

    foreach ($comments as $row): ?>
        <div class="card mb-3 shadow-sm ms-<?= $depth * 4 ?>">
            <div class="card-body d-flex">
                <div class="me-3">
                    <div class="bg-gradient rounded-circle d-flex justify-content-center align-items-center shadow"
                        style="width: 50px; height: 50px; font-size: 22px; background: linear-gradient(135deg, #6f42c1, #6610f2);">
                        <i class="fas fa-user-astronaut"></i>
                    </div>
                </div>
                <div class="w-100">
                    <p class="mb-1"><?= nl2br(htmlspecialchars($row['comment'])) ?></p>
                    <small class="text-muted">
                        <?= htmlspecialchars($row['name']) ?> • <?= timeAgo($row['created_at']) ?>
                    </small>
                    <?php if (
                        ($_SESSION['admin_role'] ?? '') === 'super_admin' ||
                        ($row['email'] ?? '') === ($_SESSION['user_email'] ?? '')
                    ): ?>

                        <form method="POST" class="d-inline-block ms-2">
                            <input type="hidden" name="delete_comment_id" value="<?= $row['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                onclick="return confirm('Are you sure you want to delete this comment?')">
                                🗑 Delete
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php displayComments($conn, $task_id, $row['id'], $depth + 1);
    endforeach;
}

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
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

    <!-- Include Font Awesome once in your main layout -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body {
            background-color: #f8f9fa;
        }

        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }

        .task-info p {
            font-size: 1.1rem;
        }

        .not-found {
            color: red;
            font-weight: bold;
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
        <div class="container">
            <h2 class="text-center mb-4">Task Details</h2>
            <div class="task-info mb-4">
                <p><strong>Title:</strong> <?= htmlspecialchars($task['title']) ?></p>
                <p><strong>Link:</strong> <a href="<?= htmlspecialchars($task['description']) ?>"
                        target="_blank"><?= htmlspecialchars($task['description']) ?></a></p>
                <p><strong>Reward:</strong> ₹<?= number_format($task['reward_amount'], 2) ?></p>
                <p><strong>Instructions:</strong> <?= nl2br(htmlspecialchars($task['instructions'])) ?></p>
            </div>

            <h4><strong>Audio Instruction:</strong></h4>
            <div class="mb-3">
                <?php if ($audio_path): ?>
                    <audio controls>
                        <source src="<?= $audio_path ?>" type="audio/mpeg">
                        Your browser does not support the audio element.
                    </audio>
                <?php else: ?>
                    <p class="not-found">❌ Audio file NOT found</p>
                <?php endif; ?>
            </div>

            <h4><strong>Video Instruction:</strong></h4>
            <div class="mb-3">
                <?php if ($video_path): ?>
                    <video controls>
                        <source src="<?= $video_path ?>" type="video/mp4">
                        Your browser does not support the video element.
                    </video>
                <?php else: ?>
                    <p class="not-found">❌ Video file NOT found</p>
                <?php endif; ?>
            </div>



            <hr>
            <h4 class="mb-3">💬 Comments</h4>

            <form method="POST" class="mb-4">
                <input type="hidden" name="name" value="<?= htmlspecialchars($_SESSION['user_name'] ?? 'Anonymous') ?>">
                <input type="hidden" name="email" value="<?= htmlspecialchars($_SESSION['user_email'] ?? '') ?>">
                <?php
                $totalComments = $conn->query("SELECT COUNT(*) AS total FROM comments WHERE task_id = $task_id")->fetch_assoc()['total'];
                ?>

                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                <div class="mb-2">
                    <textarea class="form-control" name="comment" placeholder="Your Comment" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Submit Comment</button>

            </form>

            <div id="comments">
                <?php displayComments($conn, $task_id); ?>
            </div>
            <div class="text-center mb-4">
                <a href="dashboard.php" class="btn btn-success">Back to Dashboard</a>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            function toggleReply(id) {
                const box = document.getElementById('reply-box-' + id);
                if (box) box.style.display = (box.style.display === 'none') ? 'block' : 'none';
            }

            function toggleReply(id) {
                const box = document.getElementById('reply-box-' + id);
                box.style.display = (box.style.display === 'none') ? 'block' : 'none';
            }

        </script>
</body>

</html>