<?php
require_once "../db.php";

$leadId = isset($_GET["id"]) ? intval($_GET["id"]) : 0;

if ($leadId <= 0) {
    die("Invalid lead ID.");
}

// Fetch existing lead data
$stmt = $pdo->prepare("SELECT * FROM leads WHERE id = ?");
$stmt->execute([$leadId]);
$lead = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lead) {
    die("Lead not found.");
}

// Fetch interests for dropdown
$interests = $pdo->query("SELECT interest_id, name FROM leads_interests")->fetchAll(PDO::FETCH_ASSOC);

$errors = [];
$successMessage = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["task_title"]);
    $url = trim($_POST["task_description"]);
    $reward = trim($_POST["reward_amount"]);
    $instructions = trim($_POST["instructions"]);
    $expiry = trim($_POST["expiry_date"]);
    $max_submissions = trim($_POST["max_submissions"]);
    $interest_id = $_POST["interest_id"];

    // File handling
    $video_path = $lead["videoinstructions"];
    $audio_path = $lead["audioinstructions"];

    if (!empty($_FILES["video_instruction"]["name"])) {
        $video_path = "uploads/" . uniqid() . "_" . basename($_FILES["video_instruction"]["name"]);
        move_uploaded_file($_FILES["video_instruction"]["tmp_name"], $video_path);
    }

    if (!empty($_FILES["audio_instruction"]["name"])) {
        $audio_path = "uploads/" . uniqid() . "_" . basename($_FILES["audio_instruction"]["name"]);
        move_uploaded_file($_FILES["audio_instruction"]["tmp_name"], $audio_path);
    }

    // Validations
    if (empty($title)) $errors[] = "Title is required.";
    if (!is_numeric($reward)) $errors[] = "Reward must be numeric.";
    if (empty($expiry)) $errors[] = "Expiry date is required.";

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE leads SET title = ?, url = ?, instructions = ?, reward = ?, interest_id = ?, maxsubmissions = ?, audioinstructions = ?, videoinstructions = ?, expirydate = ? WHERE id = ?");
        $stmt->execute([
            $title,
            $url,
            $instructions,
            $reward,
            $interest_id,
            $max_submissions,
            $audio_path,
            $video_path,
            $expiry,
            $leadId
        ]);

        $successMessage = "Lead updated successfully!";
        // Refresh data
        $stmt = $pdo->prepare("SELECT * FROM leads WHERE id = ?");
        $stmt->execute([$leadId]);
        $lead = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Lead</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="index.css">
</head>
<body>

<?php include("sidebar.php"); ?>
<button class="toggle-btn" onclick="toggleSidebar()">☰</button>

<div class="main-content p-5">
<h2 class="text-center"> <b>Edit Lead </b></h2>
<div style="background-color:rgba(248, 249, 250, 0.7); color: #212529;" class="p-5 rounded shadow-sm">

    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success"><?= $successMessage ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $e): ?><li><?= $e ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="mt-4">
        <div class="mb-3">
            <label class="form-label">Title *</label>
            <input type="text" name="task_title" class="form-control" value="<?= htmlspecialchars($lead["title"]) ?>" required>
        </div>

        <div class="mb-3">
    <label class="form-label">Lead URL</label>
    <input type="text" name="task_description" class="form-control" value="<?= htmlspecialchars($lead["url"]) ?>">
</div>


        <div class="mb-3">
            <label class="form-label">Reward Amount *</label>
            <input type="number" name="reward_amount" class="form-control" value="<?= htmlspecialchars($lead["reward"]) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Instructions</label>
            <textarea name="instructions" class="form-control"><?= htmlspecialchars($lead["instructions"]) ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Expiry Date *</label>
            <input type="date" name="expiry_date" class="form-control" value="<?= $lead["expirydate"] ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Max Submissions</label>
            <input type="number" name="max_submissions" class="form-control" value="<?= htmlspecialchars($lead["maxsubmissions"]) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Select Interest *</label>
            <select name="interest_id" class="form-select" required>
                <?php foreach ($interests as $i): ?>
                    <option value="<?= $i["interest_id"] ?>" <?= $lead["interest_id"] == $i["interest_id"] ? "selected" : "" ?>>
                        <?= htmlspecialchars($i["name"]) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
       

        <button type="submit" class="btn btn-primary">Update Lead</button>
        <a href="all_leads.php" class="btn btn-secondary">Back</a>
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
