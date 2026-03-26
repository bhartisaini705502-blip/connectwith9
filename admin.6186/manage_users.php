<?php
// admin/manage_users.php
// Manage Users: Search, View, Activate/Deactivate, and View Transactions

require_once 'common.php';
require_once '../db.php'; // Database connection

if (!isAdminLoggedIn()) {
    adminRedirect('index.php'); // Redirect to login page if not logged in
}

$search = "";
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['search'])) {
    $search = trim($_GET['search']);
}

// Fetch users based on search input
if (!empty($search)) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? OR username LIKE ?");
    $stmt->execute([$search, "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY id DESC");
}

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 30px;
        }
        .search-box {
            max-width: 400px;
            margin: auto;
        }
        .table-container {
            margin-top: 20px;
            background: #fff;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .toggle-btn {
            width: 80px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center mb-4">Manage Users</h2>

        <form method="GET" action="manage_users.php" class="search-box text-center">
            <input type="text" name="search" class="form-control" placeholder="Search by User ID or Name" value="<?php echo htmlspecialchars($search); ?>" required>
            <button type="submit" class="btn btn-primary mt-2">Search</button>
            <a href="manage_users.php" class="btn btn-secondary mt-2">Reset</a>
        </form>

        <div class="table-container">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Wallet Balance</th>
                        <th>Status</th>
                        <th>Registered On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)) { ?>
                        <tr><td colspan="7" class="text-center">No users found.</td></tr>
                    <?php } else { ?>
                        <?php foreach ($users as $user) { ?>
                            <tr id="user_<?php echo $user['id']; ?>">
                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <?php 
                                        // Fetch wallet balance directly using SQL
                                        $stmt = $pdo->prepare("
                                            SELECT SUM(CASE WHEN transaction_type = 'credit' THEN amount ELSE -amount END) AS balance 
                                            FROM wallet_transactions 
                                            WHERE user_id = ?
                                        ");
                                        $stmt->execute([$user['id']]);
                                        $wallet_balance = $stmt->fetchColumn();
                                        echo "Rs. " . number_format($wallet_balance ?? 0, 2);
                                    ?>
                                </td>
                                <td>
                                    <button class="btn toggle-btn btn-<?php echo ($user['status'] == 'active') ? 'success' : 'secondary'; ?>" 
                                            onclick="toggleStatus(<?php echo $user['id']; ?>, '<?php echo $user['status']; ?>')">
                                        <?php echo ucfirst($user['status']); ?>
                                    </button>
                                </td>
                                <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                                <td>
                                    <a href="view_transactions.php?user_id=<?php echo $user['id']; ?>" class="btn btn-info btn-sm">View Transactions</a>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <p class="text-center mt-3"><a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a></p>
    </div>

    <script>
        function toggleStatus(userId, currentStatus) {
            let newStatus = (currentStatus === 'active') ? 'inactive' : 'active';

            $.ajax({
                url: 'toggle_user_status.php',
                type: 'POST',
                data: { user_id: userId, status: newStatus },
                success: function(response) {
                    if (response === 'success') {
                        let button = $('#user_' + userId).find('.toggle-btn');
                        button.text(newStatus.charAt(0).toUpperCase() + newStatus.slice(1));
                        button.removeClass('btn-success btn-secondary');
                        button.addClass((newStatus === 'active') ? 'btn-success' : 'btn-secondary');
                        button.attr('onclick', "toggleStatus(" + userId + ", '" + newStatus + "')");
                    } else {
                        alert("Error updating user status.");
                    }
                }
            });
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
