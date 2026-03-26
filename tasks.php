<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="index.css">

    <style>
        /* General Layout */
        .submissions-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .submission-card {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            transition: transform 0.3s ease;
        }

        .submission-card:hover {
            transform: scale(1.05);
        }

        .submission-details,
        .submission-links,
        .submission-screenshot,
        .submission-actions {
            margin-bottom: 15px;
        }

        .submission-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .submission-details p,
        .submission-links a {
            font-size: 14px;
            margin-bottom: 10px;
        }

        .submission-actions {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .submission-actions textarea {
            width: 100%;
            padding: 10px;
            font-size: 14px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }

        .submission-actions .btn-sm {
            font-size: 12px;
        }

        .submission-screenshot img {
            border-radius: 8px;
        }

        .submission-links a {
            color: #4e73df;
            text-decoration: none;
        }

        .submission-links a:hover {
            text-decoration: underline;
        }

        .badge {
            font-size: 12px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .submissions-container {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 480px) {
            .submissions-container {
                grid-template-columns: 1fr;
            }

            .submission-card {
                padding: 15px;
            }
        }
    </style>
</head>

<body>
    <?php include("sidebar.php"); ?>

    <!-- Toggle Button for Mobile -->
    <button class="toggle-btn" onclick="toggleSidebar()">☰</button>

    <?php
    require_once 'common.php';
    if (!isAdminLoggedIn()) {
        adminRedirect('index.php');
    }
    require_once '../db.php';

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $submission_id = intval($_POST['submission_id']);
        $action = $_POST['action'];
        $comment = isset($_POST['comment']) ? $_POST['comment'] : '';

        if ($action == 'approve') {
            $status = 'approved';
            $stmt = $pdo->prepare("SELECT user_id, task_id FROM submissions WHERE id = ?");
            $stmt->execute([$submission_id]);
            $submission = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($submission) {
                $stmt = $pdo->prepare("SELECT reward_amount FROM tasks WHERE id = ?");
                $stmt->execute([$submission['task_id']]);
                $task = $stmt->fetch(PDO::FETCH_ASSOC);
                $reward = $task ? $task['reward_amount'] : 0;

                $stmt = $pdo->prepare("INSERT INTO wallet_transactions (user_id, transaction_type, amount, description, transaction_date) 
                                        VALUES (?, 'credit', ?, 'Task Reward', NOW())");
                $stmt->execute([$submission['user_id'], $reward]);
            }
        } elseif ($action == 'reject') {
            $status = 'rejected';
        }

        $stmt = $pdo->prepare("UPDATE submissions SET status = ?, admin_comment = ? WHERE id = ?");
        $stmt->execute([$status, $comment, $submission_id]);
    }

    $stmt = $pdo->query("SELECT s.*, u.username, t.title FROM submissions s 
                        JOIN users u ON s.user_id = u.id 
                        JOIN tasks t ON s.task_id = t.id 
                        ORDER BY FIELD(s.status, 'pending', 'approved', 'rejected'), s.submitted_at DESC");

    $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <div class="main-content">
        <div class="container mt-4">
            <h2 class="text-center">Review Task Submissions</h2>
            <?php if (empty($submissions)) { ?>
                <div class="alert alert-info text-center">No task submissions found.</div>
            <?php } else { ?>
                <div class="submissions-container">
                    <?php foreach ($submissions as $sub) { ?>
                        <div class="submission-card">
                            <div class="submission-details">
                                <h5 class="submission-title"><?php echo htmlspecialchars($sub['title'], ENT_QUOTES, 'UTF-8'); ?>
                                </h5>
                                <p><strong>Submission ID:</strong> <?php echo $sub['id']; ?></p>
                                <p><strong>User:</strong> <?php echo htmlspecialchars($sub['username'], ENT_QUOTES, 'UTF-8'); ?>
                                </p>
                                <p><strong>Submitted At:</strong> <?php echo $sub['submitted_at']; ?></p>
                                <p><strong>Status:</strong>
                                    <?php
                                    if ($sub['status'] == 'pending') {
                                        echo '<span class="badge bg-warning text-dark">Pending</span>';
                                    } elseif ($sub['status'] == 'approved') {
                                        echo '<span class="badge bg-success">Approved</span>';
                                    } elseif ($sub['status'] == 'rejected') {
                                        echo '<span class="badge bg-danger">Rejected</span>';
                                    }
                                    ?>
                                </p>
                            </div>
                            <div class="submission-links">
                                <h6>Submission URL(s):</h6>
                                <?php
                                // Fetch multiple submission URLs
                                $stmt_urls = $pdo->prepare("SELECT url FROM submission_urls WHERE submission_id = ?");
                                $stmt_urls->execute([$sub['id']]);
                                $urls = $stmt_urls->fetchAll(PDO::FETCH_COLUMN);

                                if (!empty($urls)) {
                                    foreach ($urls as $url) {
                                        echo '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" target="_blank">' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '</a><br>';
                                    }
                                } else {
                                    echo 'No URLs Provided';
                                }
                                ?>
                            </div>
                            <div class="submission-screenshot">
                                <h6>Screenshot:</h6>
                                <?php
                                $screenshot = trim($sub['screenshot_path']);
                                $screenshot_path = "../uploads/screenshots/" . basename($screenshot);
                                if (!empty($screenshot) && file_exists($screenshot_path)) {
                                    echo '<img src="' . htmlspecialchars($screenshot_path, ENT_QUOTES, 'UTF-8') . '" width="100">';
                                } else {
                                    echo "N/A";
                                }
                                ?>
                            </div>
                            <div class="submission-details">
                                <p class="submission-title">Admin Comment</p>
                                <p><strong></strong> <?php echo $sub['admin_comment']; ?></p>
                            </div>
                            <div class="submission-actions">
                                <?php if ($sub['status'] == 'pending') { ?>
                                    <form method="POST" action="tasks.php">
                                        <input type="hidden" name="submission_id" value="<?php echo $sub['id']; ?>">
                                        <textarea name="comment" class="form-control mb-2" placeholder="Add comment"></textarea>
                                        <button type="submit" name="action" value="approve"
                                            class="btn btn-success btn-sm">Approve</button>
                                        <button type="submit" name="action" value="reject"
                                            class="btn btn-danger btn-sm">Reject</button>
                                    </form>
                                <?php } else { ?>
                                    <span class="text-muted"><?php echo ucfirst($sub['status']); ?></span>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </div>

    <!-- FontAwesome for Icons -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>

    <!-- Sidebar Toggle Script -->
    <script>
        function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("active");
        }
    </script>
</body>

</html>