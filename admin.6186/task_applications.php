<?php
// admin/task_applications.php
// Admin panel to approve or reject task applications

require_once 'common.php';
require_once '../db.php'; // Database connection

if (!isAdminLoggedIn()) {
    adminRedirect('index.php');
}

// Fetch all pending task applications
$stmt = $pdo->query("
    SELECT * FROM task_applications 
    WHERE task_status = 'pending'
    ORDER BY applied_at DESC
");

$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle application approval/rejection
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $application_id = intval($_POST['application_id']);
    $action = $_POST['action'];

    if ($action === 'approve') {
        $status = 'approved';
    } elseif ($action === 'reject') {
        $status = 'rejected';
    }

    $stmt = $pdo->prepare("UPDATE task_applications SET task_status = ? WHERE id = ?");
    $stmt->execute([$status, $application_id]);

    // Redirect after processing
    header("Location: task_applications.php?success=Application+Updated");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Task Applications</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 30px;
        }
        .table-container {
            margin-top: 20px;
            background: #fff;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center mb-4">Task Applications Approval</h2>

        <?php if (isset($_GET['success'])) { ?>
            <div class="alert alert-success text-center"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php } ?>

        <div class="table-container">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Applicant</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Task</th>
                        <th>Applied At</th>
                        <th>Task Status</th>
                        <th>Overall Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($applications)) { ?>
                        <tr><td colspan="9" class="text-center">No pending applications.</td></tr>
                    <?php } else { ?>
                        <?php foreach ($applications as $app) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($app['id']); ?></td>
                                <td><?php echo htmlspecialchars($app['user_id']); ?></td>
                                <td><?php echo htmlspecialchars($app['email']); ?></td>
                                <td><?php echo htmlspecialchars($app['phone_no']); ?></td>
                                <td><?php echo htmlspecialchars($app['task_title']); ?></td>
                                <td><?php echo htmlspecialchars($app['applied_at']); ?></td>
                                <td>
                                    <span class="badge bg-warning">Pending</span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo ($app['status'] == 'active') ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($app['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" action="task_applications.php" style="display:inline;">
                                        <input type="hidden" name="application_id" value="<?php echo $app['id']; ?>">
                                        <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
                                        <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">Reject</button>
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <p class="text-center mt-3"><a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a></p>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
