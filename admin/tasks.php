<?php
require_once 'common.php';
if (!isAdminLoggedIn()) {
    adminRedirect('index.php');
}
require_once '../db.php';

// Approve / Reject Logic
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $submission_id = intval($_POST['submission_id']);
    $action = $_POST['action'];
    $comment = $_POST['comment'] ?? '';

    $stmt = $pdo->prepare("SELECT user_id, task_id FROM submissions WHERE id = ?");
    $stmt->execute([$submission_id]);
    $submission = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($submission) {
        $status = $action === 'approve' ? 'approved' : 'rejected';

        if ($status === 'approved') {
            $stmt = $pdo->prepare("SELECT reward_amount FROM tasks WHERE id = ?");
            $stmt->execute([$submission['task_id']]);
            $reward = $stmt->fetchColumn();

            $stmt = $pdo->prepare("INSERT INTO wallet_transactions (user_id, transaction_type, amount, description, transaction_date) 
                                   VALUES (?, 'credit', ?, 'Task Reward', NOW())");
            $stmt->execute([$submission['user_id'], $reward]);
        }

        $stmt = $pdo->prepare("UPDATE submissions SET status = ?, admin_comment = ? WHERE id = ?");
        $stmt->execute([$status, $comment, $submission_id]);
    }
}

// Filters
$searchQuery = $_GET['search'] ?? '';
$taskFilter = $_GET['task_id'] ?? '';
$dateFilter = $_GET['date'] ?? '';

$where = "WHERE 1";
$params = [];

if ($searchQuery) {
    $where .= " AND (u.username LIKE :search OR t.title LIKE :search OR s.id LIKE :search)";
    $params[':search'] = "%$searchQuery%";
}
if ($taskFilter) {
    $where .= " AND t.id = :task_id";
    $params[':task_id'] = $taskFilter;
}
if ($dateFilter) {
    $where .= " AND DATE(s.submitted_at) = :date";
    $params[':date'] = $dateFilter;
}

// Pagination
$limit = 12;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM submissions s JOIN users u ON s.user_id = u.id JOIN tasks t ON s.task_id = t.id $where");
foreach ($params as $key => $val)
    $countStmt->bindValue($key, $val);
$countStmt->execute();
$total = $countStmt->fetchColumn();
$totalPages = ceil($total / $limit);

$query = "SELECT s.*, u.username, t.title 
          FROM submissions s 
          JOIN users u ON s.user_id = u.id 
          JOIN tasks t ON s.task_id = t.id 
          $where 
          ORDER BY FIELD(s.status, 'pending', 'approved', 'rejected'), s.submitted_at DESC 
          LIMIT :offset, :limit";

$stmt = $pdo->prepare($query);
foreach ($params as $key => $val)
    $stmt->bindValue($key, $val);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$tasks = $pdo->query("SELECT id, title FROM tasks ORDER BY title ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Review Submissions</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="index.css">
    <style>
        body {
            background-color: #f4f7fa;
        }

        .card {
            height: 100%;
            min-height: 480px;
            /* You can adjust this height as per your content */
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: 0.3s ease;
        }

        .card-body {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
        }

        .card:hover {
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }


        .submission-image {
            max-width: 100px;
            max-height: 100px;
            object-fit: contain;
        }

        .filter-bar .form-control,
        .filter-bar .form-select {
            font-size: 14px;
        }
    </style>
</head>

<body>
    <?php include("sidebar.php"); ?>
    <button class="toggle-btn btn btn-light" onclick="toggleSidebar()">☰</button>

    <div class="main-content">
        <h2 class="text-center mb-4">Review Task Submissions</h2>

        <!-- Filters -->
        <form class="row g-2 filter-bar mb-4" method="GET">
            <div class="col-sm-4">
                <input type="text" name="search" class="form-control" placeholder="Search by username, task, or ID"
                    value="<?= htmlspecialchars($searchQuery) ?>">
            </div>
            <div class="col-sm-3">
                <select name="task_id" class="form-select">
                    <option value="">All Tasks</option>
                    <?php foreach ($tasks as $task): ?>
                        <option value="<?= $task['id'] ?>" <?= ($taskFilter == $task['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($task['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-3">
                <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($dateFilter) ?>">
            </div>
            <div class="col-sm-2">
                <button class="btn btn-primary w-100">Filter</button>
            </div>
        </form>

        <?php if (!$submissions): ?>
            <div class="alert alert-warning text-center">No submissions found.</div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($submissions as $sub): ?>
                    <div class="col-md-4">
                        <div class="card shadow-sm">
                            <div class="card-body d-flex flex-column justify-content-between">
                                <h5 class="task-title">
                                    <a href="task_details2.php?task_title=<?= urlencode($sub['task_id']) ?>">
                                        <?= htmlspecialchars($sub['title']) ?>
                                    </a>
                                </h5>
                                <p class="mb-1"><strong>ID:</strong> <?= $sub['id'] ?></p>
                                <p class="mb-1"><strong>User:</strong> <?= htmlspecialchars($sub['username']) ?></p>
                                <p class="mb-1"><strong>Submitted At:</strong> <?= $sub['submitted_at'] ?></p>
                                <p class="mb-1"><strong>Details:</strong> <?= $sub['details'] ?></p>
                                <p class="mb-1"><strong>Status:</strong>
                                    <?= match ($sub['status']) {
                                        'pending' => '<span class="badge bg-warning text-dark">Pending</span>',
                                        'approved' => '<span class="badge bg-success">Approved</span>',
                                        'rejected' => '<span class="badge bg-danger">Rejected</span>',
                                        default => $sub['status']
                                    } ?>
                                </p>
                                <p><strong>URLs:</strong><br>
                                    <?php
                                    $stmt = $pdo->prepare("SELECT url FROM submission_urls WHERE submission_id = ?");
                                    $stmt->execute([$sub['id']]);
                                    $urls = $stmt->fetchAll(PDO::FETCH_COLUMN);

                                    if ($urls && count($urls) > 0) {
                                        foreach ($urls as $url) {
                                            $safeUrl = htmlspecialchars($url);
                                            echo "<a href=\"$safeUrl\" target=\"_blank\">$safeUrl</a><br>";
                                        }
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </p>


                                <p><strong>Screenshot:</strong><br>
                                    <?php
                                    $screenshot = trim($sub['screenshot_path']);
                                    $screenshot_path = "../uploads/screenshots/" . basename($screenshot);

                                    if (!empty($screenshot) && file_exists($screenshot_path)) {
                                        $safe_path = htmlspecialchars($screenshot_path, ENT_QUOTES, 'UTF-8');
                                        echo '<img src="' . $safe_path . '" 
        width="100" 
        class="img-thumbnail"
        style="cursor:pointer;" 
        onclick="showImageModal(\'' . $safe_path . '\')">';
                                    } else {
                                        echo "N/A";
                                    }
                                    ?>


                                </p>
                                <p><strong>Admin Comment:</strong><br><?= htmlspecialchars($sub['admin_comment']) ?></p>

                                <?php if ($sub['status'] === 'pending'): ?>
                                    <form method="POST" class="mt-3">
                                        <input type="hidden" name="submission_id" value="<?= $sub['id'] ?>">
                                        <textarea name="comment" class="form-control mb-2" placeholder="Add comment..."></textarea>
                                        <div class="d-flex justify-content-between">
                                            <button name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
                                            <button name="action" value="reject" class="btn btn-danger btn-sm">Reject</button>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Bootstrap Image Modal (Only Once) -->
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
            </script>
            <style>
                .img-thumbnail {
                    transition: transform 0.3s ease;
                }

                .img-thumbnail:hover {
                    transform: scale(1.2);
                    z-index: 10;
                }
            </style>


            <!-- Pagination -->
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $page == $i ? 'active' : '' ?>">
                            <a class="page-link"
                                href="?page=<?= $i ?>&search=<?= urlencode($searchQuery) ?>&task_id=<?= $taskFilter ?>&date=<?= $dateFilter ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("active");
        }
    </script>
</body>

</html>