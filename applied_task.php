<?php
session_start();
require_once 'db.php'; // Database connection
require_once 'functions.php'; // Include functions

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user'];

// Pagination setup
$limit = 6; // Tasks per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$page = max($page, 1); // Ensure at least page 1
$start = ($page - 1) * $limit;

// ✅ Securely Fetch Applied Tasks with Expiry & Submission Limits

$sql = "SELECT ta.task_id, ta.task_status, t.title as task_title, t.expiry_date, 
               t.max_submissions, t.current_submissions

        FROM task_applications ta 
        JOIN tasks t ON ta.task_id = t.id 
        WHERE ta.user_id = :user_id
        ORDER BY ta.task_id DESC 
        LIMIT :start, :limit";



$stmt = $pdo->prepare($sql);
$stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
$stmt->bindParam(":start", $start, PDO::PARAM_INT);
$stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
$stmt->execute();
$appliedTasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ Get total applied tasks for pagination
$total_sql = "SELECT COUNT(*) FROM task_applications WHERE user_id = :user_id";
$total_stmt = $pdo->prepare($total_sql);
$total_stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
$total_stmt->execute();
$total_records = $total_stmt->fetchColumn();

$total_pages = max(ceil($total_records / $limit), 1); // Ensure at least 1 page
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Applied Tasks</title>
    <link rel="stylesheet" href="css/style.css"> <!-- Include global styles -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</head>

<body>

    <!-- Include Sidebar -->
    <?php include("sidebar.php"); ?>

    <!-- Toggle Button -->
    <button class="toggle-btn" onclick="toggleSidebar()">☰</button>

    <!-- Main Content -->
    <div class="main-content">

        <div class="applied-tasks-container p-4 ">
            <h2 class="text-center mb-4">My Applied Tasks</h2>

            <?php if (empty($appliedTasks)) { ?>
                <p class="text-center">You haven't applied for any tasks yet.</p>
            <?php } else { ?>
                <div class="row">
                    <?php foreach ($appliedTasks as $appliedTask) {
                        // Check if the user has already submitted work for this task
                        $stmt = $pdo->prepare("SELECT id FROM submissions WHERE user_id = ? AND task_id = ?");
                        $stmt->execute([$user_id, $appliedTask['task_id']]);
                        $isSubmitted = $stmt->fetch(PDO::FETCH_ASSOC);

                        // Define status class for better visibility
                        $statusClass = ($appliedTask['task_status'] === 'approved') ? 'badge bg-success'
                            : (($appliedTask['task_status'] === 'pending') ? 'badge bg-warning text-dark'
                                : 'badge bg-danger');

                        // Expiry check
                        $isExpired = (!empty($appliedTask['expiry_date']) && strtotime($appliedTask['expiry_date']) < time());

                        // Submission limit check
                        $isFull = ($appliedTask['current_submissions'] >= $appliedTask['max_submissions']);
                        ?>
                        <div class="col-md-6">
                            <div class="task-card p-3 mb-3 border rounded shadow-sm">
                                <h6 class="task-title mb-2">

                                    <a href="applied_task_details.php?task_id=<?php echo $appliedTask['task_id']; ?>"
                                        class="task-link">
                                        <?php echo htmlspecialchars($appliedTask['task_title']); ?>
                                    </a>


                                </h6>

                                <p class="mb-1"><strong>Status:</strong>
                                    <?php
                                    if ($appliedTask['task_status'] === 'approved') {
                                        echo $isSubmitted ? 'Submitted' : 'Approved';
                                    } elseif ($appliedTask['task_status'] === 'pending') {
                                        echo 'Approval Pending';
                                    } elseif ($appliedTask['task_status'] === 'rejected') {
                                        echo 'Rejected';
                                    } else {
                                        echo ucfirst($appliedTask['task_status']);
                                    }
                                    ?>
                                </p>

                                <p class="mb-1"><strong>Expiry:</strong>
                                    <?php echo $isExpired ? '<span class="badge bg-danger">Expired</span>' : date('d M Y', strtotime($appliedTask['expiry_date'])); ?>
                                </p>
                                <p class="mb-2"><strong>Submissions:</strong>
                                    <?php echo $appliedTask['current_submissions'] . " / " . $appliedTask['max_submissions']; ?>
                                </p>

                                <div class="d-flex justify-content-between">
                                    <?php if ($isExpired || $isFull) { ?>
                                        <button class="btn btn-secondary btn-sm" disabled style="width: 100%;">Unavailable</button>
                                    <?php } elseif ($appliedTask['task_status'] === 'approved') { ?>
                                        <?php if ($isSubmitted) { ?>
                                            <button class="btn btn-secondary btn-sm" disabled style="width: 100%;">Submitted</button>
                                        <?php } else { ?>
                                            <a href="submit_task.php?task_id=<?php echo $appliedTask['task_id']; ?>"
                                                class="btn btn-success btn-sm" style="width: 100%;">Submit Work</a>
                                        <?php } ?>
                                    <?php } elseif ($appliedTask['task_status'] === 'rejected') { ?>
                                        <form method="POST" action="reapply_task.php">
                                            <input type="hidden" name="task_id" value="<?php echo $appliedTask['task_id']; ?>">
                                            <button type="submit" class="btn btn-warning btn-sm"
                                                style="width: 100% !important;">Reapply</button>
                                        </form>
                                    <?php } else { ?>
                                        <button class="btn btn-success btn-sm" disabled style="width: 100%;">Pending</button>
                                    <?php } ?>
                                </div>

                            </div>
                        </div>
                    <?php } ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav class="text-center mt-4">
                        <ul class="pagination justify-content-center flex-wrap" id="pagination">
                            <!-- Previous -->
                            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo max($page - 1, 1); ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                    <span class="d-none d-sm-inline"> Prev</span>
                                </a>
                            </li>

                            <!-- Page Numbers -->
                            <?php
                            $adjacents = 1;
                            $start = max(1, $page - $adjacents);
                            $end = min($total_pages, $page + $adjacents);

                            if ($start > 1) {
                                echo '<li class="page-item"><a class="page-link" href="?page=1">1</a></li>';
                                if ($start > 2)
                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }

                            for ($i = $start; $i <= $end; $i++): ?>
                                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor;

                            if ($end < $total_pages) {
                                if ($end < $total_pages - 1)
                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . '">' . $total_pages . '</a></li>';
                            }
                            ?>

                            <!-- Next -->
                            <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo min($page + 1, $total_pages); ?>" aria-label="Next">
                                    <span class="d-none d-sm-inline">Next </span>
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>

            </div>
        <?php } ?>
    </div>

    <style>
        .badge {
            font-size: 12px;
            vertical-align: middle;
            padding: 5px 8px;
            border-radius: 12px;
        }

        /* Responsive Pagination */
        .pagination .page-item .page-link {
            color: #007bff;
            border: 1px solid #dee2e6;
            margin: 2px;
            padding: 6px 10px;
            font-size: 14px;
            border-radius: 6px;
            transition: 0.3s ease;
        }

        .pagination .page-item.active .page-link {
            background-color: #007bff;
            color: #fff;
            border-color: #007bff;
        }

        .pagination .page-item.disabled .page-link {
            background-color: #e9ecef;
            color: #6c757d;
            pointer-events: none;
        }

        /* Hide page numbers on very small screens */
        @media (max-width: 480px) {
            .pagination .page-item:not(.active):not(:first-child):not(:last-child):not(.disabled) {
                display: none;
            }

            .pagination .page-item:first-child,
            .pagination .page-item:last-child,
            .pagination .page-item.active {
                display: inline-block;
            }

            .pagination .page-link {
                padding: 6px 8px;
                font-size: 12px;
            }
        }

        /* Task Card Styling */
        .task-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            padding: 10px;
            width: 100%;
            max-width: 400px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .task-card:hover {
            transform: translateY(-5px);
            box-shadow: 0px 6px 15px rgba(0, 0, 0, 0.2);
        }

        .task-title {
            font-weight: bold;
            font-size: 18px;
            text-decoration: none;
            color: #007bff;
            transition: color 0.3s ease;
        }

        .task-title:hover {
            color: #0056b3;
            text-decoration: underline;
        }

        /* Pagination Styling */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            margin-top: 20px;
        }

        .pagination a {
            display: inline-block;
            padding: 8px 14px;
            border-radius: 6px;
            background-color: #f8f9fa;
            color: #007bff;
            font-weight: 500;
            text-decoration: none;
            transition: 0.3s ease;
            border: 1px solid #ddd;
        }

        .pagination a:hover {
            background-color: #007bff;
            color: white;
        }

        .pagination a.active {
            background-color: #007bff;
            color: white;
            border: none;
            font-weight: bold;
        }

        .pagination a.disabled {
            background-color: #e9ecef;
            color: #6c757d;
            cursor: not-allowed;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .task-card {
                max-width: 90%;
            }

            .pagination a {
                padding: 6px 12px;
            }
        }

        @media (max-width: 480px) {
            .task-card {
                max-width: 100%;
            }

            .pagination {
                flex-wrap: wrap;
                gap: 4px;
            }

            .pagination a {
                padding: 6px 10px;
                font-size: 14px;
            }
        }
    </style>

    </div>

    <script>
        function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("active");
        }
    </script>

</body>

</html>