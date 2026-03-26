<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user'];
$submission_id = $_GET['id'] ?? null;

if (!$submission_id) {
    echo "Invalid Request.";
    exit();
}

// Fetch submission details
$query = "SELECT ls.*, l.title AS lead_title 
          FROM leads_submissions ls 
          JOIN leads l ON ls.lead_id = l.id 
          WHERE ls.id = ? AND ls.user_id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$submission_id, $user_id]);
$submission = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$submission) {
    echo "Submission not found or access denied.";
    exit();
}

$status = ucfirst($submission['status']);
$badgeClass = match ($status) {
    'Approved' => 'bg-success',
    'Rejected' => 'bg-danger',
    'Pending' => 'bg-warning text-dark',
    default => 'bg-secondary',
};

$commentsQuery = "SELECT * FROM leads_comments 
                  WHERE submission_id = ? AND parent_id IS NULL 
                  ORDER BY created_at DESC";
$commentsStmt = $pdo->prepare($commentsQuery);
$commentsStmt->execute([$submission_id]);
$topLevelComments = $commentsStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Lead Submission Details</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">

    <style>
        body {
            background: #f8f9fa;
            font-family: 'Poppins', sans-serif;
        }

        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .badge-status {
            font-size: 0.9rem;
            padding: 0.4em 0.7em;
            border-radius: 5px;
        }

      

        .comment-box {
            background: #ffffff;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.06);
            margin-bottom: 15px;
        }

        .comment-author {
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .comment-meta {
            font-size: 0.8rem;
            color: #777;
        }

        .comment-form textarea {
            border-radius: 10px;
        }

        .comment-form .btn {
            border-radius: 8px;
        }

        .badge-admin {
            background-color: #0d6efd;
            font-size: 0.7rem;
        }

        .badge-user {
            background-color: #6c757d;
            font-size: 0.7rem;
        }

        .toggle-btn {
            display: none;
        }

        @media (max-width: 991px) {
            .main-content {
                margin: 0;
                padding: 15px;
            }

            .toggle-btn {
                display: block;
                background: #0d6efd;
                color: #fff;
                border: none;
                font-size: 24px;
                padding: 5px 10px;
                margin: 10px;
                border-radius: 8px;
            }
        }
    </style>
</head>

<body>

    <?php include("sidebar2.php"); ?>

    <button class="toggle-btn" onclick="toggleSidebar()">☰</button>

    <div class="main-content">
        <div class="container-fluid">

            <!-- Submission Card -->
            <div class="card mb-4">
                <div class="card-body">
                    <h3 class="text-primary"><strong><?= htmlspecialchars($submission['lead_title']) ?></strong></h3>

                    <div class="mt-3">
                        <span class="fw-bold">Status: </span>
                        <span class="badge <?= $badgeClass ?> badge-status"><?= $status ?></span>
                    </div>

                    <?php if ($status === 'Approved' && !empty($submission['work_status'])): ?>
                        <p class="mt-2"><strong>Work Status:</strong> <?= htmlspecialchars($submission['work_status']) ?>
                        </p>
                    <?php endif; ?>

                    <div class="mt-3">
                        <p><strong>Client Name:</strong> <?= htmlspecialchars($submission['client_name']) ?></p>
                        <p><strong>Client Contact:</strong> <?= htmlspecialchars($submission['client_contact']) ?></p>
                        <p><strong>Client Details:</strong>
                            <?= nl2br(htmlspecialchars($submission['client_details'])) ?></p>
                    </div>

                    <?php if (!empty($submission['url'])): ?>
                        <div class="mt-3">
                            <p><strong>URL:</strong>
                                <a href="<?= htmlspecialchars($submission['url']) ?>" target="_blank"
                                    class="text-decoration-underline text-primary">
                                    <?= htmlspecialchars($submission['url']) ?>
                                </a>
                            </p>
                        </div>
                    <?php endif; ?>
                    <?php
                    $filePath = $submission['screenshot']; // Already includes "uploads/"
                    if (file_exists($filePath)):
                        ?>
                        <div class="mt-4">
                            <p><strong>Screenshot:</strong></p>
                            <img src="<?= htmlspecialchars($filePath) ?>" alt="Screenshot"
                                onclick="showImageModal('<?= htmlspecialchars($filePath) ?>  ?>')" style="height: 200px; width: auto;">
                        </div>
                    
                    <?php endif; ?>

                    <p class="text-muted mt-3">Submitted on:
                        <?= date("M d, Y", strtotime($submission['submitted_at'])) ?></p>
                </div>
            </div>

            <!-- Comments Section -->
            <div class="card">
                <div class="card-body">
                    <h4>Comments</h4>

                    <!-- New Comment Form -->
                    <form action="submit_comment.php" method="post" class="comment-form mb-4">
                        <input type="hidden" name="lead_id" value="<?= $submission['lead_id'] ?>">
                        <input type="hidden" name="submission_id" value="<?= $submission['id'] ?>">
                        <input type="hidden" name="parent_id" value="">

                        <div class="mb-3">
                            <textarea name="comment" class="form-control" rows="3" placeholder="Write a comment..."
                                required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Post Comment
                        </button>
                    </form>

                    <!-- Existing Comments -->
                    <?php foreach ($topLevelComments as $comment): ?>
                        <?php
                        // Fetch author name
                        $authorName = $comment['is_admin'] ? 'Admin' : '';
                        if (!$comment['is_admin']) {
                            $stmtUser = $pdo->prepare("SELECT username FROM users WHERE id = ?");
                            $stmtUser->execute([$comment['user_id']]);
                            $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
                            $authorName = $user ? htmlspecialchars($user['username']) : 'Admin';
                        }
                        ?>
                        <div class="card mb-2">
                            <div class="card-header">
                                Commented by: <?= htmlspecialchars($authorName) ?> on
                                <?= date("d M Y, H:i", strtotime($comment['created_at'])) ?>
                                <?php if (!$comment['is_admin'] && $comment['user_id'] == $user_id): ?>

                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <p><?= nl2br(htmlspecialchars($comment['comment'] ?? '')) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>

                </div>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>