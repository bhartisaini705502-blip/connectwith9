<?php
session_start();
require_once 'db.php';
require_once 'functions.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user'];
$lead_id = $_GET["lead_id"] ?? null;
$errors = [];
$success = "";

if (!$user_id) {
    die("You must be logged in to submit a lead.");
}

// Fetch lead details
$lead = null;
if ($lead_id) {
    $stmt = $pdo->prepare("SELECT * FROM leads WHERE id = ?");
    $stmt->execute([$lead_id]);
    $lead = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$lead) {
        die("Lead not found.");
    }
}

// Check current submissions
$currentSubmissions = 0;
$maxSubmissionsReached = false;

if ($lead_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM leads_submissions WHERE lead_id = ?");
    $stmt->execute([$lead_id]);
    $currentSubmissions = $stmt->fetchColumn();

    if ($lead && $lead['maxsubmissions'] && $currentSubmissions >= $lead['maxsubmissions']) {
        $maxSubmissionsReached = true;
    }
}

// Handle submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && !$maxSubmissionsReached) {
    $lead_id = $_POST["lead_id"] ?? null;
    $url = trim($_POST["url"]);
    $client_name = trim($_POST["client_name"]);
    $client_contact = trim($_POST["client_contact"]);
    $client_details = trim($_POST["client_details"]);
    $screenshot_path = null;
    
    if (!empty($_FILES["screenshot"]["name"])) {
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf']; // allow images + PDF
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'];
    
        $fileType = mime_content_type($_FILES["screenshot"]["tmp_name"]);
        $fileExtension = strtolower(pathinfo($_FILES["screenshot"]["name"], PATHINFO_EXTENSION));
    
        if (in_array($fileType, $allowedMimeTypes) && in_array($fileExtension, $allowedExtensions)) {
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true); // create the uploads folder if it doesn't exist
            }
            $screenshot_path = $target_dir . uniqid() . "_" . basename($_FILES["screenshot"]["name"]);
            if (!move_uploaded_file($_FILES["screenshot"]["tmp_name"], $screenshot_path)) {
                $errors[] = "Failed to upload file. Please try again.";
            }
        } else {
            $errors[] = "Invalid file type. Only JPG, PNG, GIF, WEBP images and PDF files are allowed.";
        }
    }
    
    if (!$lead_id || !$client_name || !$client_contact || !$client_details) {
        $errors[] = "Please fill in all required fields.";
    }

    if (empty($errors)) {
        $reward_amount = $lead["reward"] ?? 0;

        $stmt = $pdo->prepare("INSERT INTO leads_submissions 
            (lead_id, user_id, url, screenshot, client_name, client_contact, client_details, reward_amount, submitted_at, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'pending')");
        $stmt->execute([
            $lead_id, $user_id, $url, $screenshot_path, $client_name, $client_contact, $client_details, $reward_amount
        ]);

        $success = "Lead submitted successfully!";
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Lead</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <link rel="stylesheet" href="css/style.css"> <!-- Include global styles -->

    <style>
        strong{
            color:rgb(0, 78, 235);
        }
    </style>
</head>


<body>

    <?php include("sidebar.php"); ?>
    <button class="toggle-btn" onclick="toggleSidebar()">☰</button>

    <div class="main-content p-4">
    <div style="background-color:rgba(248, 249, 250, 0.8); color: #212529;" class="p-5 rounded shadow-sm">
        <h2><b>Submit Lead</b></h2>

        <?php if ($lead): ?>
            
                <div class="card-body">
                    <h4 class="card-title"><strong><?= htmlspecialchars($lead["title"]) ?></strong></h4>
                    <br>
                    <p><strong>Reward:</strong> ₹<?= htmlspecialchars($lead["reward"]) ?></p>
                    <p><strong>Created At:</strong> <?= date("d M Y", strtotime($lead["createdat"])) ?></p>
                    <p><strong>Expiry Date:</strong> <?= date("d M Y", strtotime($lead["expirydate"])) ?></p>
                    <p><strong>Max Submissions:</strong> <?= htmlspecialchars($lead["maxsubmissions"]) ?></p>
                    <p><strong>Instructions:</strong> <?= nl2br(htmlspecialchars($lead["instructions"])) ?></p>
                    <?php if (!empty($lead["url"])): ?>
                        <p><strong>URL:</strong> <a href="<?= htmlspecialchars($lead["url"]) ?>" target="_blank"><?= htmlspecialchars($lead["url"]) ?></a></p>
                    <?php endif; ?>
                
            </div>
        <?php endif; ?>

        <?php if ($maxSubmissionsReached): ?>
            <div class="alert alert-warning">
                This lead has reached its maximum number of submissions. You cannot submit more.
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul><?php foreach ($errors as $e): ?><li><?= $e ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <?php if (!$maxSubmissionsReached): ?>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="lead_id" value="<?= htmlspecialchars($lead_id) ?>">

                <div class="mb-3">
                    <label class="form-label">Lead URL (optional)</label>
                    <input type="url" name="url" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Upload Screenshot (optional)</label>
                    <!-- <input type="file" name="screenshot" class="form-control"> -->
                    <input type="file" name="screenshot" class="form-control" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf">

                </div>

                <div class="mb-3">
                    <label class="form-label">Client Name *</label>
                    <input type="text" name="client_name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Client Contact *</label>
                    <input type="text" name="client_contact" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Client Details *</label>
                    <textarea name="client_details" class="form-control" rows="3" required></textarea>
                </div>

                <button type="submit" class="btn btn-success" <?= $maxSubmissionsReached ? 'disabled' : '' ?>>Submit Lead</button>
                
            </form>
            
        <?php endif; ?>
    </div>
    <br>
    <p class="text-center"><a href="all_leads.php" class="btn btn-primary ">Back to all Tasks</a></p>
    
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("active");
        }
    </script>

</body>

</html>