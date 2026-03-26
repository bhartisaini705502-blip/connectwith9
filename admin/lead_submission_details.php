<?php
session_start();
require_once '../db.php';
require_once '../functions.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

$message = "";

if (isset($_GET['submission_id'])) {
    $submission_id = $_GET['submission_id'];

    // Fetch submission details
    $stmt = $pdo->prepare("SELECT ls.*, u.username AS user_name, l.title AS lead_title, l.reward
                            FROM leads_submissions ls
                            LEFT JOIN users u ON ls.user_id = u.id
                            LEFT JOIN leads l ON ls.lead_id = l.id
                            WHERE ls.id = ?");
    $stmt->execute([$submission_id]);
    $submission = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$submission) {
        header("Location: leads_review.php");
        exit();
    }

    // Fetch comments
    $stmt = $pdo->prepare("SELECT lc.*, u.username AS commenter_name
                            FROM leads_comments lc
                            LEFT JOIN users u ON lc.user_id = u.id
                            WHERE lc.submission_id = ?
                            ORDER BY lc.created_at DESC");
    $stmt->execute([$submission_id]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

   // Check if the lead_id exists before inserting the comment
$stmt = $pdo->prepare("SELECT id FROM leads WHERE id = ?");
$stmt->execute([$submission['lead_id']]);
$lead = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lead) {
    $message = "Error: The lead associated with this submission does not exist.";
    header("Location: lead_submission_details.php?submission_id=" . $submission_id);
    exit();
}

// Proceed with inserting the comment
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['comment'])) {
    $commentText = trim($_POST['comment']);
    if ($commentText != '') {
        $admin_id = $_SESSION['admin']['id'] ?? null;
        $stmt = $pdo->prepare("INSERT INTO leads_comments (submission_id, user_id, comment, created_at, lead_id) 
                               VALUES (?, ?, ?, NOW(), ?)");
        $stmt->execute([$submission_id, $admin_id, $commentText, $submission['lead_id']]);
        $message = "Comment added successfully.";
        header("Location: lead_submission_details.php?submission_id=" . $submission_id);
        exit();
    }
}


    // Delete comment
    if (isset($_GET['delete_comment_id'])) {
        $comment_id = $_GET['delete_comment_id'];
        $stmt = $pdo->prepare("DELETE FROM leads_comments WHERE id = ? AND submission_id = ?");
        $stmt->execute([$comment_id, $submission_id]);
        header("Location: lead_submission_details.php?submission_id=" . $submission_id);
        exit();
    }

} else {
    header("Location: leads_review.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lead Submission Details</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="index.css">
</head>
<body>
    <?php include("sidebar.php"); ?>
    <button class="toggle-btn" onclick="toggleSidebar()">☰</button>

    <div class="main-content">
        <h2 class="mb-4 text-center"><b>Lead Submission Details</b></h2>
        <div class="container">
            <?php if (!empty($message)): ?>
                <div class="alert alert-success text-center"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    Lead Title: <?= htmlspecialchars($submission['lead_title'] ?? '') ?><br>
                    Submitted By: <?= htmlspecialchars($submission['user_name'] ?? '') ?><br>
                    <small>Submitted On: <?= date("d M Y", strtotime($submission['submitted_at'])) ?></small><br>
                    Reward: <?= htmlspecialchars($submission['reward'] ?? '') ?>
                </div>
                <div class="card-body">
                    <p><strong>Client Name:</strong> <?= htmlspecialchars($submission['client_name'] ?? '') ?></p>
                    <p><strong>Client Contact:</strong> <?= htmlspecialchars($submission['client_contact'] ?? '') ?></p>
                    <p><strong>Client Details:</strong> <?= nl2br(htmlspecialchars($submission['client_details'] ?? '')) ?></p>

                    <?php if (!empty($submission['url'])): ?>
                        <p><strong>Submitted URL:</strong> <a href="<?= htmlspecialchars($submission['url']) ?>" target="_blank">View URL</a></p>
                    <?php endif; ?>

                    <?php if (!empty($submission['screenshot'])): ?>
    <?php
    $filePath = "../" . $submission['screenshot'];
    $fileExtension = strtolower(pathinfo($submission['screenshot'], PATHINFO_EXTENSION));
    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
    ?>
    
    <p>
    
        <?php if (in_array($fileExtension, $imageExtensions)): ?>
            <p><strong>Media:</strong></p>
            <img src="<?= htmlspecialchars($filePath) ?>" class="img-fluid rounded border img-thumbnail" style="max-width: 300px;" onclick="showImageModal('<?= htmlspecialchars($filePath) ?>')">
        <?php else: ?>
            <strong>File:</strong>
            <a href="<?= htmlspecialchars($filePath) ?>" target="_blank" class="btn btn-primary">
                View / Download File
            </a>
        <?php endif; ?>
    </p>
<?php endif; ?>


                    <p><strong>Status:</strong>
                        <span class="badge bg-<?= $submission['status'] === 'approved' ? 'success' : ($submission['status'] === 'rejected' ? 'danger' : 'secondary') ?>">
                            <?= ucfirst($submission['status'] ?? 'pending') ?>
                        </span>
                    </p>

                    <p><strong>Work Status:</strong> <?= ucfirst($submission['work_status'] ?? 'N/A') ?></p>

                    <p><strong>Admin Remarks:</strong> <?= htmlspecialchars($submission['admin_remarks'] ?? '') ?></p>
                </div>
            </div>

            <h4>Comments</h4>
            <form method="post">
                <div class="mb-3">
                    <textarea name="comment" class="form-control" placeholder="Add your comment" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Add Comment</button>
            </form>

            <div class="mt-4">
                <?php if (count($comments) > 0): ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="card mb-2">
                            <div class="card-header">
                                Commented by: <?= htmlspecialchars($comment['commenter_name'] ?? 'Admin') ?> 
                                on <?= date("d M Y, H:i", strtotime($comment['created_at'])) ?>
                                <a href="lead_submission_details.php?submission_id=<?= $submission_id ?>&delete_comment_id=<?= $comment['id'] ?>" class="btn btn-danger btn-sm float-end">Delete</a>

                            </div>
                            <div class="card-body">
                                <p><?= nl2br(htmlspecialchars($comment['comment'] ?? '')) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-info">No comments yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-body text-center p-2">
                            <img id="modalImage" src="" class="img-fluid rounded" style="max-height: 80vh; width: auto;">
                        </div>
                        <div class="modal-footer justify-content-center">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                function showImageModal(src) {
                    const modalImage = document.getElementById('modalImage');
                    modalImage.src = src;
                    const modal = new bootstrap.Modal(document.getElementById('imageModal'));
                    modal.show();
                }

                function toggleSidebar() {
                    document.getElementById("sidebar").classList.toggle("active");
                }
            </script>
            </body>
</html>
