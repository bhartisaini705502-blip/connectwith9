<?php
require_once "../db.php";

$titleErr = $descriptionErr = $rewardErr = $expiryErr = "";
$successMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST["task_title"]);
    $url = trim($_POST["task_description"]);
    $reward = trim($_POST["reward_amount"]);
    $instructions = trim($_POST["instructions"]);
    $expiry = trim($_POST["expiry_date"]);
    $max_submissions = trim($_POST["max_submissions"]);
    $interest_id = isset($_POST["interests"]) ? intval($_POST["interests"]) : null;

    $video_path = "";
    $audio_path = "";

    // Validations
    if (empty($title)) {
        $titleErr = "Title is required";
    }
    
    if (!is_numeric($reward)) {
        $rewardErr = "Reward must be a number";
    }
    if (empty($expiry)) {
        $expiryErr = "Expiry date is required";
    }

    // File Uploads
    if (!empty($_FILES["video_instruction"]["name"])) {
        $video_path = "uploads/" . uniqid() . "_" . basename($_FILES["video_instruction"]["name"]);
        move_uploaded_file($_FILES["video_instruction"]["tmp_name"], $video_path);
    }

    if (!empty($_FILES["audio_instruction"]["name"])) {
        $audio_path = "uploads/" . uniqid() . "_" . basename($_FILES["audio_instruction"]["name"]);
        move_uploaded_file($_FILES["audio_instruction"]["tmp_name"], $audio_path);
    }

    if (empty($titleErr) && empty($descriptionErr) && empty($rewardErr) && empty($expiryErr)) {
        $stmt = $pdo->prepare("INSERT INTO leads (title, url, instructions, reward, interest_id, maxsubmissions, videoinstructions, audioinstructions, expirydate) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->execute([
            $title,
            $url,
            $instructions,
            $reward,
            $interest_id,
            $max_submissions,
            $video_path,
            $audio_path,
            $expiry
        ]);

        $successMessage = "Lead added successfully!";
    }
}

// Fetch interests
$interestList = $pdo->query("SELECT interest_id, name FROM leads_interests")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Lead</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="index.css">
    <style>
    
    </style>
</head>
<body>
<?php include("sidebar.php"); ?>

<!-- Toggle Button for Mobile -->
<button class="toggle-btn" onclick="toggleSidebar()">☰</button>

<div class="main-content p-5">
    

    <div style="background-color:rgba(248, 249, 250, 0.7); color: #212529;" class="p-5 rounded shadow-sm">
    <h2 class="text-center "><b>Add New Lead</b></h2>
        <?php if ($successMessage): ?>
            <div class="alert alert-success"><?= $successMessage ?></div>
        <?php endif; ?>

        <div class="text-end mb-3">
    <a href="all_leads.php" class="btn btn-success">All Leads</a>
</div>

        <form method="POST" enctype="multipart/form-data" class="mt-4">
            <div class="mb-3">
                <label class="form-label">Title *</label>
                <input type="text" name="task_title" class="form-control" required>
                <small class="text-danger"><?= $titleErr ?></small>
            </div>

            <div class="mb-3">
                <label class="form-label">Lead URL </label>
                <input type="text" name="task_description" class="form-control" >
                <small class="text-danger"><?= $descriptionErr ?></small>
            </div>

            <div class="mb-3">
                <label class="form-label">Reward Amount *</label>
                <input type="number" name="reward_amount" class="form-control" required>
                <small class="text-danger"><?= $rewardErr ?></small>
            </div>

            <div class="mb-3">
                <label class="form-label">Instructions</label>
                <textarea name="instructions" class="form-control"></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Expiry Date *</label>
                <input type="date" name="expiry_date" class="form-control" required>
                <small class="text-danger"><?= $expiryErr ?></small>
            </div>

            <div class="mb-3">
                <label class="form-label">Max Submissions</label>
                <input type="number" name="max_submissions" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Select Interest *</label><br>
                <?php foreach ($interestList as $interest): ?>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="interests"
                               value="<?= $interest['interest_id'] ?>" required>
                        <label class="form-check-label"><?= htmlspecialchars($interest['name']) ?></label>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- <div class="mb-3">
                <label class="form-label">Video Instruction (optional)</label>
                <input type="file" name="video_instruction" class="form-control">
            </div> -->

            <!-- <div class="mb-3">
                <label class="form-label">Audio Instruction (optional)</label>
                <input type="file" name="audio_instruction" class="form-control">
            </div> -->

            <button type="submit" class="btn btn-primary">Add Lead</button>
        </form>
    </div>
</div>

<script>
    function toggleSidebar() {
        document.getElementById("sidebar").classList.toggle("active");
    }
</script>


    <!-- FontAwesome for Icons -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq"
        crossorigin="anonymous"></script>
</body>

</html>