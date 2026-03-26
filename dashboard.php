<?php
session_start();
require_once 'db.php';
require_once 'functions.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user'];

// Get interest_id from query (optional filter)
$interestId = isset($_GET['interest']) ? (int) $_GET['interest'] : null;

// Pagination Logic
$tasksPerPage = 9;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$page = max($page, 1);
$offset = ($page - 1) * $tasksPerPage;

// ✅ Total Tasks Count for Pagination (based on user interests)
if ($interestId) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM tasks 
        WHERE interest_id = ? 
        AND interest_id IN (
            SELECT interest_id FROM user_interests WHERE user_id = ?
        )
    ");
    $stmt->execute([$interestId, $user_id]);
} else {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM tasks 
        WHERE interest_id IN (
            SELECT interest_id FROM user_interests WHERE user_id = ?
        )
    ");
    $stmt->execute([$user_id]);
}
$totalTasks = $stmt->fetchColumn();
$totalPages = ceil($totalTasks / $tasksPerPage);

// ✅ Fetch only interests of the logged-in user for dropdown
$interestStmt = $pdo->prepare("
    SELECT i.id, i.name 
    FROM interests i 
    INNER JOIN user_interests ui ON i.id = ui.interest_id 
    WHERE ui.user_id = ?
");
$interestStmt->execute([$user_id]);
$allInterests = $interestStmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ Helper Functions
function hasUserApplied($user_id, $task_id, $pdo)
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM task_applications WHERE user_id = ? AND task_id = ?");
    $stmt->execute([$user_id, $task_id]);
    return $stmt->fetchColumn() > 0;
}

function hasUserSubmitted($user_id, $task_id, $pdo)
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM submissions WHERE user_id = ? AND task_id = ?");
    $stmt->execute([$user_id, $task_id]);
    return $stmt->fetchColumn() > 0;
}

function isApproved($user_id, $task_id, $pdo)
{
    $stmt = $pdo->prepare("SELECT task_status FROM task_applications WHERE user_id = ? AND task_id = ?");
    $stmt->execute([$user_id, $task_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result && $result['task_status'] === 'approved';
}

// ✅ Get Paginated Tasks (based on user interests)
$tasks = getAllTasksPaginated($pdo, $tasksPerPage, $offset, $interestId, $user_id);

// Get User Info
$user = getUser($user_id, $pdo);

function getAllTasksPaginated($pdo, $limit = 6, $offset = 0, $interestId = null, $userId)
{
    $limit = (int) $limit;
    $offset = (int) $offset;

    if ($interestId) {
        $stmt = $pdo->prepare("
            SELECT * 
            FROM tasks 
            WHERE interest_id = ? 
            AND interest_id IN (
                SELECT interest_id FROM user_interests WHERE user_id = ?
            )
            ORDER BY created_at DESC 
            LIMIT $limit OFFSET $offset
        ");
        $stmt->execute([$interestId, $userId]);
    } else {
        $stmt = $pdo->prepare("
            SELECT * 
            FROM tasks 
            WHERE interest_id IN (
                SELECT interest_id FROM user_interests WHERE user_id = ?
            )
            ORDER BY created_at DESC 
            LIMIT $limit OFFSET $offset
        ");
        $stmt->execute([$userId]);
    }

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <style>
        .tasks-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            padding: 10px;
        }

        .task-wrapper {
            flex: 1 1 calc(33.333% - 20px);
            /* 👈 3 in a row with gap */
            max-width: calc(33.333% - 20px);
            display: flex;
        }

        .task-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            padding: 15px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            width: 100%;
            box-sizing: border-box;
            min-height: 300px;
            position: relative;
        }

        .task-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .task-title {
            font-size: 18px;
            font-weight: bold;
            text-decoration: none;
            color: #007bff;
        }

        .task-title:hover {
            text-decoration: underline;
            color: #0056b3;
        }

        .reward {
            background: #4CAF50;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 14px;
            margin: 8px 0;
        }

        .expiry,
        .submissions {
            font-size: 14px;
            color: #555;
            margin: 4px 0;
        }

        .task-action {
            margin-top: auto;
            position: absolute;
            bottom: 15px;
            left: 15px;
            right: 15px;
        }

        .btn {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            text-align: center;
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background: #0056b3;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-warning {
            background: #ffc107;
            color: black;
        }

        .btn-applied {
            background: #28a745;
            color: white;
            padding: 10px;
            border-radius: 5px;
            font-size: 14px;
        }

        /* 📱 Responsive Breakpoints */
        @media (max-width: 992px) {
            .task-wrapper {
                flex: 1 1 calc(50% - 20px);
                /* 2 in a row on tablets */
                max-width: calc(50% - 20px);
            }
        }

        @media (max-width: 576px) {
            .task-wrapper {
                flex: 1 1 100%;
                max-width: 100%;
            }
        }

        .badge {
            font-size: 12px;
            vertical-align: middle;
            padding: 5px 8px;
            border-radius: 12px;
        }
    </style>
</head>

<body>

    <?php include("sidebar.php"); ?>
    <button class="toggle-btn" onclick="toggleSidebar()">☰</button>

    <div class="main-content">
        <h2 style="text-align:center;">Welcome, <?php echo htmlspecialchars($user['username']); ?></h2>

        <div>
            <h3 style="text-align: center;">Available Tasks</h3>

            <!-- ✅ Interest Filter Dropdown -->
            <form method="GET" class="text-center mb-3">
                <label for="interest" class="form-label">Filter by Interest:</label>
                <select name="interest" id="interest" class="form-select w-auto d-inline-block"
                    onchange="this.form.submit()">
                    <option value="">All</option>
                    <?php foreach ($allInterests as $interest): ?>
                        <option value="<?= $interest['id']; ?>" <?= ($interestId == $interest['id']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($interest['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>

            <div class="tasks-container ">





                <?php foreach ($tasks as $task) {
                    $applied = hasUserApplied($user_id, $task['id'], $pdo);
                    $approved = isApproved($user_id, $task['id'], $pdo);
                    $expired = strtotime($task['expiry_date']) < time();
                    $maxReached = $task['current_submissions'] >= $task['max_submissions'];
                    ?>
                    <div class="task-wrapper">
                        <div class="task-card">
                            <div class="task-header">
                                <h6>
                                    <a href="task_details.php?task_id=<?php echo $task['id']; ?>" class="task-link">
                                        <?php echo htmlspecialchars($task['title']); ?>
                                    </a>
                                </h6>
                            </div>
                            <p class="r">
                                Reward: <span class="reward">Rs. <?= number_format($task['reward_amount'], 2); ?></span>
                            </p>
                            <p class="expiry">Expiry: <?= date('d-m-Y H:i', strtotime($task['expiry_date'])); ?></p>
                            <p class="submissions">Submissions:
                                <?= $task['current_submissions'] . '/' . $task['max_submissions']; ?>
                            </p>
                            <?php
                            // Get application status for the user & task
                            $stmt = $pdo->prepare("SELECT task_status FROM task_applications WHERE user_id = ? AND task_id = ?");
                            $stmt->execute([$user_id, $task['id']]);
                            $app = $stmt->fetch(PDO::FETCH_ASSOC);
                            $taskStatus = $app['task_status'] ?? null;
                            ?>
                            <p>
                                <span class="text-muted">
                                    <?php
                                    echo "Status: ";
                                    if ($taskStatus === 'rejected') {
                                        echo "rejected";
                                    } elseif ($applied && $approved) {
                                        $submitted = hasUserSubmitted($user_id, $task['id'], $pdo);
                                        echo $submitted ? "submitted" : "applied";
                                    } elseif ($applied && !$approved) {
                                        echo "Approval Pending";
                                    } else {
                                        echo "new";
                                    }
                                    ?>
                                </span>
                            </p>

                            <div class="task-action">
                                <?php
                                if ($taskStatus === 'rejected') { ?>
                                    <form method="POST" action="reapply_task1.php">
                                        <input type="hidden" name="task_id" value="<?= $task['id']; ?>">
                                        <button type="submit" class="btn btn-primary">Reapply</button>
                                    </form>
                                <?php } elseif ($applied && !$approved) { ?>
                                    <button class="btn btn-secondary" disabled>Pending Approval</button>
                                <?php } elseif ($applied && $approved) {
                                    $submitted = hasUserSubmitted($user_id, $task['id'], $pdo);
                                    if ($submitted) { ?>
                                        <button class="btn btn-applied btn-success" disabled>Already Submitted</button>
                                    <?php } else { ?>
                                        <a href="task_details.php?task_id=<?= $task['id']; ?>" class="btn btn-success">Submit
                                            Task</a>
                                    <?php }
                                } elseif ($expired) { ?>
                                    <button class="btn btn-secondary" disabled>EXPIRED</button>
                                <?php } elseif ($maxReached) { ?>
                                    <button class="btn btn-warning" disabled>FULL</button>
                                <?php } else { ?>
                                    <form method="POST" action="apply_task.php">
                                        <input type="hidden" name="task_id" value="<?= $task['id']; ?>">
                                        <button type="submit" class="btn btn-primary">Apply</button>
                                    </form>
                                <?php } ?>

                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>

            <!-- ✅ Pagination with Interest preserved -->
            <div class="d-flex justify-content-center mt-4">
                <nav>
                    <ul class="pagination">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page - 1; ?>&interest=<?= $interestId ?>">Previous</a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= ($i === $page) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?= $i; ?>&interest=<?= $interestId ?>"><?= $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page + 1; ?>&interest=<?= $interestId ?>">Next</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
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