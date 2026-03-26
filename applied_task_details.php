<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user'];
$task_id = isset($_GET['task_id']) ? (int) $_GET['task_id'] : 0;

// Fetch task details
$sql = "SELECT ta.*, t.title, t.description, t.instructions, t.expiry_date, t.max_submissions, t.current_submissions, t.video_instruction, t.audio_instruction
        FROM task_applications ta
        JOIN tasks t ON ta.task_id = t.id
        WHERE ta.task_id = :task_id AND ta.user_id = :user_id";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(":task_id", $task_id, PDO::PARAM_INT);
$stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
$stmt->execute();

$task = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$task) {
    echo "<p>Task not found or not applied by you.</p>";
    exit();
}
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
            background-color: #f8f9fa;
        }

        .card {
            border-radius: 20px;
        }

        h2 {
            font-weight: 600;
        }

        .btn-custom {
            background-color: #0d6efd;
            color: white;
            border-radius: 30px;
            padding: 10px 30px;
        }

        video,
        audio {
            border: 1px solid #dee2e6;
            border-radius: 10px;
        }
    </style>
</head>

<body>

<!-- Include Sidebar -->
<?php include("sidebar.php"); ?>

<!-- Toggle Button -->
<button class="toggle-btn" onclick="toggleSidebar()">☰</button>


    <div class="main-content">
    <div class="card shadow p-4">
            <h2 class="mb-4 text-primary"><?php echo htmlspecialchars($task['title']); ?></h2>

            <p><strong>Task Link:</strong></p>
            <a href="<?php echo nl2br(htmlspecialchars($task['description'])); ?>" ><p class="text" style="color:blue;"><?php echo nl2br(htmlspecialchars($task['description'])); ?></p></a>
            

            <p><strong>Instructions:</strong></p>
            <p class="text-muted"><?php echo nl2br(htmlspecialchars($task['instructions'])); ?></p>

            <?php if (!empty($task['video_instruction'])): ?>
                <div class="mb-4">
                    <p><strong>Video Instruction:</strong></p>
                    <video class="w-100" controls>
                        <source src="<?php echo htmlspecialchars($task['video_instruction']); ?>" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
            <?php endif; ?>

            <?php if (!empty($task['audio_instruction'])): ?>
                <div class="mb-4">
                    <p><strong>Audio Instruction:</strong></p>
                    <audio class="w-100" controls>
                        <source src="<?php echo htmlspecialchars($task['audio_instruction']); ?>" type="audio/mpeg">
                        Your browser does not support the audio tag.
                    </audio>
                </div>
            <?php endif; ?>

            <div class="mb-4">
                <p><strong>Expiry Date:</strong> <?php echo date('d M Y', strtotime($task['expiry_date'])); ?></p>
                <p><strong>Submissions:</strong>
                    <?php echo $task['current_submissions'] . " / " . $task['max_submissions']; ?></p>
            </div>

            <div class="text-center mt-4">
                <button class="btn btn-primary" onclick="window.history.back();"> Back to My Applied Tasks</button>
            </div>
        </div>
    </div>

        <script>
        function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("active");
        }
    
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>