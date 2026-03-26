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

    </style>
</head>

<body>
    <?php
    include("sidebar.php");
    ?>

    <!-- Toggle Button for Mobile -->
    <button class="toggle-btn" onclick="toggleSidebar()">☰</button>


    <?php
    // admin/all_tasks.php
// Admin panel to manage all tasks
    
    require_once 'common.php';
    require_once '../db.php'; // Database connection
    
    if (!isAdminLoggedIn()) {
        adminRedirect('index.php');
    }

    $limit = 9; // Number of tasks per page
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $offset = ($page - 1) * $limit;

    $search = "";
    $status_filter = "";

    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';
    }

    // Fetch tasks based on search and status filter
    $query = "SELECT * FROM tasks WHERE 1";

    // Calculate total number of tasks
    $total_tasks_query = "SELECT COUNT(*) FROM tasks";
    $total_tasks_stmt = $pdo->query($total_tasks_query);
    $total_tasks = $total_tasks_stmt->fetchColumn();

    // Calculate total pages
    $total_pages = ceil($total_tasks / $limit);


    if (!empty($search)) {
        $query .= " AND (id = :search OR title LIKE :search_like)";
    }

    if (!empty($status_filter) && in_array($status_filter, ['active', 'completed'])) {
        $query .= " AND status = :status_filter";
    }

    // $query .= " ORDER BY created_at DESC";
    $query .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($query);

    if (!empty($search)) {
        $stmt->bindValue(':search', $search, PDO::PARAM_INT);
        $stmt->bindValue(':search_like', "%$search%", PDO::PARAM_STR);
    }

    if (!empty($status_filter)) {
        $stmt->bindValue(':status_filter', $status_filter, PDO::PARAM_STR);
    }

    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);


    // Handle task deletion
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_task'])) {
        $task_id = intval($_POST['task_id']);

        $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
        $stmt->execute([$task_id]);

        header("Location: all_tasks.php?success=Task+Deleted+Successfully");
        exit();
    }
    ?>

    <style>



    </style>
    <div class="main-content">
        <div class="header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 style="text-align: center; padding-left:20px;"><b>ALL TASKS</b></h2>
            </div>
            <div class="text-end">
                <a href="add_task.php"><button class="btn btn-success">+ ADD TASK</button></a>
            </div>
          
        </div>

        <div>
            <form method="GET" action="all_tasks.php" class="search-form d-flex justify-content-end mb-3">
                <input type="text" name="search" class="form-control d-inline " placeholder="Search by Task ID or Title"
                    value="<?php echo htmlspecialchars($search); ?>" required>
                <select name="status" class="status-select d-inline  ms-2">
                    <option value="">All Status</option>
                    <option value="active" <?php if ($status_filter == 'active')
                        echo 'selected'; ?>>Active</option>
                    <option value="completed" <?php if ($status_filter == 'completed')
                        echo 'selected'; ?>>Completed
                    </option>
                </select>
                <button type="submit" class="btn btn-primary ms-2">Search</button>
            </form>
        </div>

        <?php if (isset($_GET['success'])) { ?>
            <div class="alert alert-success text-center"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php } ?>

        <div class="tasks-container">
            <?php if (empty($tasks)) { ?>
                <div class="no-tasks">
                    <div class="alert alert-warning">No tasks found.</div>
                </div>
            <?php } else { ?>
                <?php foreach ($tasks as $task) { ?>
                    <div class="task-card">
                        <div class="task-content">
                            <h5 class="task-title">
                            <a href="<?php echo ($task['is_lead'] === 'yes') ? 'lead_details.php' : 'task_details.php'; ?>?task_id=<?php echo $task['id']; ?>" class="task-link">
    <?php echo htmlspecialchars($task['title']); ?>
    <?php if ($task['is_lead'] === 'yes'): ?>
        <span class="badge bg-warning text-dark ms-2">Lead</span>
    <?php endif; ?>
</a>


<style>
.badge {
    font-size: 12px;
    vertical-align: middle;
    padding: 5px 8px;
    border-radius: 12px;
}
</style>



                                </h5>
                            <p><strong>ID:</strong> <?php echo htmlspecialchars($task['id']); ?></p>
                            <p><strong>Created At:</strong> <?php echo htmlspecialchars($task['created_at']); ?></p>
                            <p><strong>Status:</strong>
                                <span
                                    class="status-badge <?php echo ($task['status'] == 'active') ? 'active' : 'completed'; ?>">
                                    <?php echo ucfirst($task['status']); ?>
                                </span>
                            </p>
                            <div class="task-actions">
                                <a href="edit_task.php?task_id=<?php echo $task['id']; ?>"
                                    class="btn btn-success btn-sm">Edit</a>
                                <form method="POST" action="all_tasks.php" class="delete-form">
                                    <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                    <button type="submit" name="delete_task" class="btn btn-danger btn-sm"
                                        onclick="return confirm('Are you sure?');">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            <?php } ?>
        </div>
        <!-- Pagination Controls -->
        <nav aria-label="Page navigation example">
            <ul class="pagination justify-content-center mt-4">
                <li class="page-item <?php if ($page <= 1)
                    echo 'disabled'; ?>">
                    <a class="page-link" href="?page=<?php echo max(1, $page - 1); ?>" tabindex="-1"><</a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                    <li class="page-item <?php if ($i == $page)
                        echo 'active'; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php } ?>
                <li class="page-item <?php if ($page >= $total_pages)
                    echo 'disabled'; ?>">
                    <a class="page-link" href="?page=<?php echo min($total_pages, $page + 1); ?>">></a>
                </li>
            </ul>
        </nav>

    </div>

    <style>
        /* General Layout */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        a {
            text-decoration: none;
        }

        .header h2 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }

        .search-form {
            display: flex;
            justify-content: flex-end;
            align-items: center;
        }

        .search-form input,
        .search-form select {
            padding: 10px;
            margin-right: 10px;
            font-size: 14px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }

        .search-form button {
            padding: 10px 20px;
            font-size: 14px;
            border-radius: 8px;
            border: none;
            background-color: #4e73df;
            color: white;
            cursor: pointer;
        }

        .search-form button:hover {
            background-color: #2e59d9;
        }

        /* Tasks Container */
        .tasks-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .task-card {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            transition: transform 0.3s ease;
        }

        .task-card:hover {
            transform: scale(1.05);
        }

        .task-content {
            display: flex;
            flex-direction: column;
        }

        .task-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .task-content p {
            font-size: 14px;
            margin-bottom: 10px;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 12px;
            color: white;
            font-size: 14px;
        }

        .status-badge.active {
            background-color: #28a745;
        }

        .status-badge.completed {
            background-color: #6c757d;
        }

        .task-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }

        .task-actions .btn-sm {
            font-size: 12px;
        }

        .delete-form {
            display: inline;
        }

        .no-tasks {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Responsive Design */
        @media (max-width: 768px) {

            .search-form input,
            .search-form select,
            .search-form button {
                width: 100%;
                margin-right: 0;
                margin-bottom: 10px;
            }

            .tasks-container {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 480px) {
            .tasks-container {
                grid-template-columns: 1fr;
            }

            .task-card {
                padding: 15px;
            }
        }
    </style>


    <!-- FontAwesome for Icons -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>

    <!-- Sidebar Toggle Script -->
    <script>
        function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("active");
        }
    </script>



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