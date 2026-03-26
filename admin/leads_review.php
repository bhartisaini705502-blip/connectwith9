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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['submission_id'])) {
        $submission_id = $_POST['submission_id'];

        if (isset($_POST['work_status'])) {
            $workStatus = $_POST['work_status'];
            $allowedStatuses = ['started', 'pending', 'on progress', 'completed', 'to_other'];

            if (in_array($workStatus, $allowedStatuses)) {
                $stmt = $pdo->prepare("UPDATE leads_submissions SET work_status = ? WHERE id = ?");
                $stmt->execute([$workStatus, $submission_id]);
                $message = "Work status updated to '{$workStatus}'.";
            }
        }

        if (isset($_POST['action']) && $_POST['action'] === 'update_status' && isset($_POST['new_status'])) {
            $new_status = $_POST['new_status'];
            $remarks = isset($_POST['remarks']) ? trim($_POST['remarks']) : '';


            if (in_array($new_status, ['pending', 'approved', 'rejected'])) {
                // Fetch previous submission details
                $stmt = $pdo->prepare("SELECT ls.status, ls.user_id, l.reward 
                                       FROM leads_submissions ls
                                       LEFT JOIN leads l ON ls.lead_id = l.id
                                       WHERE ls.id = ?");
                $stmt->execute([$submission_id]);
                $submission = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($submission) {
                    $previous_status = $submission['status'];
                    $user_id = $submission['user_id'];
                    $reward_amount = $submission['reward'];

                    if ($previous_status !== $new_status) {
                        // If going from approved -> pending/rejected, DEBIT
                        if ($previous_status === 'approved' && in_array($new_status, ['pending', 'rejected'])) {
                            $stmt = $pdo->prepare("INSERT INTO wallet_transactions 
                                (user_id, transaction_type, amount, description, transaction_date) 
                                VALUES (?, 'debit', ?, 'Reward reversal for submission update', NOW())");
                            $stmt->execute([$user_id, $reward_amount]);
                        }

                        // If going from pending/rejected -> approved, CREDIT
                        if (in_array($previous_status, ['pending', 'rejected']) && $new_status === 'approved') {
                            $stmt = $pdo->prepare("INSERT INTO wallet_transactions 
                                (user_id, transaction_type, amount, description, transaction_date) 
                                VALUES (?, 'credit', ?, 'Lead Submission Reward', NOW())");
                            $stmt->execute([$user_id, $reward_amount]);
                        }
                    }
                }

                // Now update the leads_submissions table
                $stmt = $pdo->prepare("UPDATE leads_submissions SET status = ?, admin_remarks = ? WHERE id = ?");
                $stmt->execute([$new_status, $remarks, $submission_id]);
                $message = "Submission status updated to '{$new_status}'.";
            }
        }


    }
}

$perPage = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $perPage;

$totalStmt = $pdo->query("SELECT COUNT(*) FROM leads_submissions");
$totalSubmissions = $totalStmt->fetchColumn();
$totalPages = ceil($totalSubmissions / $perPage);

$stmt = $pdo->prepare("SELECT ls.*, u.username AS user_name, l.title AS lead_title, l.reward
                        FROM leads_submissions ls
                        LEFT JOIN users u ON ls.user_id = u.id
                        LEFT JOIN leads l ON ls.lead_id = l.id
                        ORDER BY ls.submitted_at DESC
                        LIMIT :limit OFFSET :offset");

$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leads Review</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="index.css">
    <style>
        .card {
            height: 100%;
            display: flex;
            flex-direction: column;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .card-body {
            flex: 1;
            overflow-y: auto;
        }

        .img-thumbnail {
            transition: transform 0.3s ease;
        }

        .img-thumbnail:hover {
            transform: scale(1.2);
            z-index: 10;
        }

        @media (max-width: 768px) {
            .card {
                height: auto;
            }

            .card-header {
                font-size: 14px;
            }

            .card-body {
                padding: 1rem;
            }
        }

        @media (min-width: 768px) {
            .card {
                height: 400px;
            }
        }
    </style>
</head>

<body>
    <?php include("sidebar.php"); ?>
    <button class="toggle-btn" onclick="toggleSidebar()">☰</button>
    <div class="main-content">
        <h2 class="mb-4 text-center"><b>Review Lead Submissions</b></h2>
        <div class="container">
            <?php if (!empty($message)): ?>
                <div class="alert alert-success text-center"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <?php if (count($submissions) === 0): ?>
                <div class="alert alert-info text-center">No lead submissions found.</div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($submissions as $submission): ?>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    Lead Title: <?= htmlspecialchars($submission['lead_title']) ?><br>
                                    Submitted By: <?= htmlspecialchars($submission['user_name']) ?><br>
                                    <small>Submitted On:
                                        <?= date("d M Y", strtotime($submission['submitted_at'])) ?></small><br>
                                    Reward: <?= htmlspecialchars($submission['reward']) ?>
                                </div>
                                <div class="card-body">
                                    <p><strong>Client Name:</strong> <?= htmlspecialchars($submission['client_name']) ?></p>
                                    <p><strong>Client Contact:</strong> <?= htmlspecialchars($submission['client_contact']) ?>
                                    </p>
                                    <p><strong>Client Details:</strong>
                                        <?= nl2br(htmlspecialchars($submission['client_details'])) ?></p>

                                    <?php if (!empty($submission['url'])): ?>
                                        <p><strong>Submitted URL:</strong> <a href="<?= htmlspecialchars($submission['url']) ?>"
                                                target="_blank">View URL</a></p>
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
                                            <img src="<?= htmlspecialchars($filePath) ?>" class="img-fluid rounded border img-thumbnail"
                                                style="max-width: 300px;"
                                                onclick="showImageModal('<?= htmlspecialchars($filePath) ?>')">
                                        <?php else: ?>
                                            <strong>File:</strong>
                                            <a href="<?= htmlspecialchars($filePath) ?>" target="_blank" class="btn btn-primary">
                                                View / Download File
                                            </a>
                                        <?php endif; ?>
                                        </p>
                                    <?php endif; ?>


                                    <p><strong>Status:</strong>
                                        <span
                                            class="badge bg-<?= $submission['status'] === 'approved' ? 'success' : ($submission['status'] === 'rejected' ? 'danger' : 'secondary') ?>">
                                            <?= ucfirst($submission['status']) ?>
                                        </span>
                                    </p>

                                    <p><strong>Work Status:</strong></p>
                                    <form method="post" class="mb-3">
                                        <input type="hidden" name="submission_id" value="<?= $submission['id'] ?>">
                                        <select name="work_status" onchange="this.form.submit()"
                                            class="form-select form-select-sm">
                                            <?php
                                            $statuses = ['started', 'pending', 'on progress', 'completed', 'to_other'];
                                            foreach ($statuses as $statusOpt): ?>
                                                <option value="<?= $statusOpt ?>" <?= $submission['work_status'] === $statusOpt ? 'selected' : '' ?>>
                                                    <?= ucfirst($statusOpt) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>

                                    <?php if (!empty($submission['admin_remarks'])): ?>
                                        <p><strong>Admin Remarks:</strong> <?= htmlspecialchars($submission['admin_remarks']) ?></p>
                                    <?php endif; ?>

                                    <a href="lead_submission_details.php?submission_id=<?= $submission['id'] ?>">View
                                        Details</a>


                                    <?php if (in_array($submission['status'], ['pending', 'approved', 'rejected'])): ?>
                                        <form method="post">
                                            <input type="hidden" name="submission_id" value="<?= $submission['id'] ?>">

                                            <div class="mb-2">
                                                <textarea name="remarks" class="form-control"
                                                    placeholder="Admin remarks (optional)"></textarea>
                                            </div>

                                            <div class="mb-2">
                                                <p>Verify Status:</p>
                                                <select name="new_status" class="form-select">
                                                    <option value="pending" <?= $submission['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                                    <option value="approved" <?= $submission['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
                                                    <option value="rejected" <?= $submission['status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                                                </select>
                                            </div>

                                            <div>
                                                <button type="submit" name="action" value="update_status"
                                                    class="btn btn-primary">Update Status</button>
                                            </div>
                                        </form>
                                    <?php endif; ?>


                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <nav class="mt-5">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>

            <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-body text-center p-2">
                            <img id="modalImage" src="" class="img-fluid rounded"
                                style="max-height: 80vh; width: auto;">
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
        </div>
    </div>
</body>

</html>