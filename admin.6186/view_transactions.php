<?php
// admin/view_transactions.php
// View User Transactions

require_once 'common.php';
require_once '../db.php'; // Database connection

if (!isAdminLoggedIn()) {
    adminRedirect('index.php'); // Redirect to login page if not logged in
}

if (!isset($_GET['user_id']) || empty($_GET['user_id'])) {
    die("User ID is required.");
}

$user_id = intval($_GET['user_id']);

// Fetch user details
$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found.");
}

// Fetch transaction type filter
$transaction_type = isset($_GET['transaction_type']) ? $_GET['transaction_type'] : '';

// Fetch user transactions based on type filter
if (!empty($transaction_type) && in_array($transaction_type, ['credit', 'debit'])) {
    $stmt = $pdo->prepare("SELECT * FROM wallet_transactions WHERE user_id = ? AND transaction_type = ? ORDER BY transaction_date DESC");
    $stmt->execute([$user_id, $transaction_type]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM wallet_transactions WHERE user_id = ? ORDER BY transaction_date DESC");
    $stmt->execute([$user_id]);
}

$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Transactions</title>
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
        <h2 class="text-center mb-4">Transactions of <?php echo htmlspecialchars($user['username']); ?></h2>

        <form method="GET" action="view_transactions.php" class="text-center mb-3">
            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
            <select name="transaction_type" class="form-select d-inline w-auto">
                <option value="">All Transactions</option>
                <option value="credit" <?php if ($transaction_type == 'credit') echo 'selected'; ?>>Credit</option>
                <option value="debit" <?php if ($transaction_type == 'debit') echo 'selected'; ?>>Debit</option>
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="view_transactions.php?user_id=<?php echo $user_id; ?>" class="btn btn-secondary">Reset</a>
        </form>

        <div class="table-container">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Description</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transactions)) { ?>
                        <tr><td colspan="5" class="text-center">No transactions found.</td></tr>
                    <?php } else { ?>
                        <?php foreach ($transactions as $txn) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($txn['id']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo ($txn['transaction_type'] == 'credit') ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($txn['transaction_type']); ?>
                                    </span>
                                </td>
                                <td>Rs. <?php echo number_format($txn['amount'], 2); ?></td>
                                <td><?php echo htmlspecialchars($txn['description']); ?></td>
                                <td><?php echo htmlspecialchars($txn['transaction_date']); ?></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <p class="text-center mt-3"><a href="manage_users.php" class="btn btn-secondary">Back to Users</a></p>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
