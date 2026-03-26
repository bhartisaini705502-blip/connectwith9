<?php
// admin/view_transactions.php
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
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Transactions</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="index.css">
    <style>
        /* Filter Form */
        .filter-form {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .filter-form .form-select,
        .filter-form .btn {
            min-width: 160px;
        }

        /* Remove manual flex for Bootstrap grid to work properly */
        .transactions-list {
            margin-top: 20px;
        }

        /* Transaction Card */
        .transaction-card {
            border-left: 5px solid #ccc;
            padding: 15px;
            border-radius: 10px;
            background-color: #fff;
            transition: box-shadow 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .transaction-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .transaction-card .badge {
            font-size: 14px;
            padding: 6px 12px;
        }

        .transaction-card .card-title {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .transaction-card .card-text {
            margin-bottom: 6px;
        }

        .transaction-card.credit {
            border-left-color: #28a745;
        }

        .transaction-card.debit {
            border-left-color: #dc3545;
        }

        @media (max-width: 768px) {
            .filter-form {
                flex-direction: column;
                align-items: stretch;
            }
        }

        .text-center.mt-3 {
            margin-top: 40px;
        }

        body {
            background-color: #f8f9fa;
        }

        h2 {
            font-weight: 600;
            color: #333;
        }

        .main-content {
            padding: 20px;
        }
    </style>

</head>

<body>
    <?php include("sidebar.php"); ?>

    <!-- Toggle Button for Mobile -->
    <button class="toggle-btn" onclick="toggleSidebar()">☰</button>

    <div class="main-content">
        <div class="container mt-4">
            <h2 class="text-center mb-4">Transactions of <?php echo htmlspecialchars($user['username']); ?></h2>

            <!-- Filter Form -->
            <form method="GET" action="view_transactions.php" class="filter-form">
            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">

<div class="col-auto">
    <select name="transaction_type" class="form-select">
        <option value="">All Transactions</option>
        <option value="credit" <?php if ($transaction_type == 'credit') echo 'selected'; ?>>Credit</option>
        <option value="debit" <?php if ($transaction_type == 'debit') echo 'selected'; ?>>Debit</option>
    </select>
</div>

<div class="col-auto">
    <button type="submit" class="btn btn-primary">Filter</button>
</div>

<div class="col-auto">
    <a href="view_transactions.php?user_id=<?php echo $user_id; ?>" class="btn btn-secondary">Reset</a>
</div>

            </form>

            <!-- Transactions List -->
            <div class="row transactions-list g-4">
                <?php if (empty($transactions)) { ?>
                    <div class="col-12">
                        <div class="alert alert-info text-center">No transactions found.</div>
                    </div>
                <?php } else { ?>
                    <?php foreach ($transactions as $txn) { ?>
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="card transaction-card <?php echo $txn['transaction_type']; ?>">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <span class="badge bg-<?php echo ($txn['transaction_type'] == 'credit') ? 'success' : 'danger'; ?>">
                                            <?php echo ucfirst($txn['transaction_type']); ?>
                                        </span>
                                        Rs. <?php echo number_format($txn['amount'], 2); ?>
                                    </h5>
                                    <p class="card-text"><strong>Description:</strong> <?php echo htmlspecialchars($txn['description']); ?></p>
                                    <p class="card-text"><strong>Date:</strong> <?php echo date("d M Y, h:i A", strtotime($txn['transaction_date'])); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>

            <!-- Back Button -->
            <div class="text-center mt-3">
                <a href="manage_users.php" class="btn btn-secondary">Back to Users</a>
            </div>
        </div>
    </div>

    <!-- FontAwesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>

    <!-- Sidebar Toggle -->
    <script>
        function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("active");
        }
    </script>
</body>

</html>
