<?php
// admin/tasks.php
// Review and process task submissions

require_once 'common.php';
if (!isAdminLoggedIn()) {
    adminRedirect('index.php');
}

require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Approve or reject a submission
    $submission_id = intval($_POST['submission_id']);
    $action = $_POST['action'];
    $comment = isset($_POST['comment']) ? $_POST['comment'] : '';
    if ($action == 'approve') {
        $status = 'approved';
        // Credit the user's wallet with the task reward
        $stmt = $pdo->prepare("SELECT user_id, task_id FROM submissions WHERE id = ?");
        $stmt->execute([$submission_id]);
        $submission = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($submission) {
            $stmt = $pdo->prepare("SELECT reward_amount FROM tasks WHERE id = ?");
            $stmt->execute([$submission['task_id']]);
            $task = $stmt->fetch(PDO::FETCH_ASSOC);
            $reward = $task ? $task['reward_amount'] : 0;
            $stmt = $pdo->prepare("INSERT INTO wallet_transactions (user_id, transaction_type, amount, description, transaction_date) VALUES (?, 'credit', ?, 'Task Reward', NOW())");
            $stmt->execute([$submission['user_id'], $reward]);
        }
    } elseif ($action == 'reject') {
        $status = 'rejected';
    }
    $stmt = $pdo->prepare("UPDATE submissions SET status = ?, admin_comment = ? WHERE id = ?");
    $stmt->execute([$status, $comment, $submission_id]);
}

$stmt = $pdo->query("SELECT s.*, u.username, t.title FROM submissions s JOIN users u ON s.user_id = u.id JOIN tasks t ON s.task_id = t.id WHERE s.status = 'pending' ORDER BY s.submitted_at DESC");
$submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Review Task Submissions</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h2 class="text-center">Review Task Submissions</h2>
        <p class="text-center"><a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a> <a href="logout.php" class="btn btn-danger">Logout</a></p>
        <?php if (empty($submissions)) { ?>
            <div class="alert alert-info text-center">No pending submissions.</div>
        <?php } else { ?>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Submission ID</th>
                        <th>User</th>
                        <th>Task</th>
                        <th>Submission URL</th>
                        <th>Screenshot</th>
                        <th>Submitted At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($submissions as $sub) { ?>
                    <tr>
                        <td><?php echo $sub['id']; ?></td>
                        <td><?php echo $sub['username']; ?></td>
                        <td><?php echo $sub['title']; ?></td>
                        <td><a href="<?php echo $sub['submission_url']; ?>" target="_blank">View</a></td>
                        <td>
                            <?php if ($sub['screenshot_path']) { ?>
                                <img src="../<?php echo $sub['screenshot_path']; ?>" width="100">
                            <?php } else { echo "N/A"; } ?>
                        </td>
                        <td><?php echo $sub['submitted_at']; ?></td>
                        <td>
                            <form method="POST" action="tasks.php">
                                <input type="hidden" name="submission_id" value="<?php echo $sub['id']; ?>">
                                <textarea name="comment" class="form-control mb-2" placeholder="Add comment"></textarea>
                                <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
                                <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">Reject</button>
                            </form>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>