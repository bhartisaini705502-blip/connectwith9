<?php
session_start();
require_once 'db.php';
require_once 'functions.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user'];

// Filters
$transaction_type = $_GET['transaction_type'] ?? '';
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';
$task_name = $_GET['task_name'] ?? '';

// Pagination
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$page = max($page, 1);
$start = ($page - 1) * $limit;

// Build filter conditions
$conditions = ["wt.user_id = :user_id"];
$params = [":user_id" => $user_id];

if ($transaction_type) {
    $conditions[] = "wt.transaction_type = :type";
    $params[':type'] = $transaction_type;
}

if ($from_date) {
    $conditions[] = "DATE(wt.transaction_date) >= :from_date";
    $params[':from_date'] = $from_date;
}

if ($to_date) {
    $conditions[] = "DATE(wt.transaction_date) <= :to_date";
    $params[':to_date'] = $to_date;
}

if ($task_name && $transaction_type === 'credit') {
    $conditions[] = "t.title LIKE :task_name";
    $params[':task_name'] = '%' . $task_name . '%';
}

// Final WHERE clause
$where = implode(' AND ', $conditions);

// Fetch transactions
$sql = "SELECT 
            wt.transaction_type, 
            wt.amount, 
            wt.transaction_date, 
            wt.description,

            s.admin_comment,
            t.title AS task_title,
            DATE(s.submitted_at) AS submitted_date,

            wr.amount AS withdrawal_amount,
            wr.upi_id,
            wr.bank_account,
            wr.ifsc_code,
            wr.status AS withdrawal_status,
            wr.admin_comment AS withdrawal_comment,
            wr.processed_date

        FROM wallet_transactions wt
        LEFT JOIN submissions s ON wt.description REGEXP CONCAT('[^0-9]', s.id, '[^0-9]')
        LEFT JOIN tasks t ON s.task_id = t.id
        LEFT JOIN withdrawal_requests wr ON wt.description REGEXP CONCAT('WRID[: ]?', wr.id)
        WHERE $where
        ORDER BY wt.transaction_date DESC
        LIMIT $start, $limit";

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total count
$total_sql = "SELECT COUNT(*) FROM wallet_transactions wt 
              LEFT JOIN submissions s ON wt.description REGEXP CONCAT('[^0-9]', s.id, '[^0-9]')
              LEFT JOIN tasks t ON s.task_id = t.id
              WHERE $where";

$total_stmt = $pdo->prepare($total_sql);
foreach ($params as $key => $value) {
    $total_stmt->bindValue($key, $value);
}
$total_stmt->execute();
$total_records = $total_stmt->fetchColumn();

$total_pages = max(ceil($total_records / $limit), 1);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>My Wallet Transactions</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css"> <!-- Include global styles -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</head>

<body>

    <?php include("sidebar.php"); ?>
    <button class="toggle-btn" onclick="toggleSidebar()">☰</button>

    <div class="main-content">
        <h2 class="text-center mb-4">My Wallet Transactions</h2>

        <!-- Filter Form -->
        <form method="GET" class="row g-2 mb-4">
            <div class="col-md-2">
                <select name="transaction_type" class="form-select">
                    <option value="">All Types</option>
                    <option value="credit" <?= $transaction_type === 'credit' ? 'selected' : '' ?>>Credit</option>
                    <option value="debit" <?= $transaction_type === 'debit' ? 'selected' : '' ?>>Debit</option>
                </select>
            </div>
            <div class="col-md-2">
                <span class="mob">From:</span>
                <input type="date" name="from_date" value="<?= htmlspecialchars($from_date) ?>" class="form-control"
                    placeholder="From Date">

            </div>
            <div class="col-md-2">
                <span class="mob"> To:</span>
                <input type="date" name="to_date" value="<?= htmlspecialchars($to_date) ?>" class="form-control"
                    placeholder="To Date">
            </div>

            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>

        <?php if (!empty($result)) { ?>
            <div class="row g-3">
                <?php foreach ($result as $row) { ?>
                    <div class="col-sm-12 col-md-6 col-lg-4">
                        <div class="transaction-card h-100">
                            <div class="d-flex justify-content-between">
                                <h5
                                    class="transaction-type <?= $row['transaction_type'] == 'credit' ? 'text-success' : 'text-danger'; ?>">
                                    <?= ucfirst($row['transaction_type']); ?>
                                </h5>
                                <h6>
                                    <?= ($row['transaction_type'] == 'credit' ? '+' : '-') . 'Rs. ' . number_format($row['amount'], 2); ?>
                                </h6>
                            </div>
                            <div class="transaction-details">
                                <p class="transaction-description"><?= htmlspecialchars($row['description'] ?: 'N/A'); ?></p>
                                <p class="transaction-date"><?= date("d M Y, H:i", strtotime($row['transaction_date'])); ?></p>

                                <?php if (!empty($row['task_title'])): ?>
                                    <hr>
                                    <p><strong>Task:</strong> <?= htmlspecialchars($row['task_title']); ?></p>
                                    <p><strong>Submitted On:</strong> <?= htmlspecialchars($row['submitted_date']); ?></p>
                                    <p><strong>Admin Comment:</strong>
                                        <?= htmlspecialchars($row['admin_comment'] ?: 'No comment'); ?></p>
                                <?php elseif (!empty($row['withdrawal_amount'])): ?>
                                    <hr>
                                    <p><strong>Withdrawal:</strong> Rs. <?= number_format($row['withdrawal_amount'], 2); ?></p>
                                    <p><strong>Bank:</strong> <?= htmlspecialchars($row['bank_account']); ?></p>
                                    <p><strong>IFSC:</strong> <?= htmlspecialchars($row['ifsc_code']); ?></p>
                                    <p><strong>UPI:</strong> <?= htmlspecialchars($row['upi_id']); ?></p>
                                    <p><strong>Status:</strong> <?= ucfirst($row['withdrawal_status']); ?></p>
                                    <p><strong>Admin Comment:</strong>
                                        <?= htmlspecialchars($row['withdrawal_comment'] ?: 'No comment'); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>

            <!-- Pagination -->
            <div class="pagination text-center mt-4" style="justify-content: center;">
                <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"
                        class="btn btn-primary <?= ($page == $i) ? 'active' : ''; ?>">
                        <?= $i ?>
                    </a>
                <?php } ?>
            </div>

        <?php } else { ?>
            <p class="text-center">No transactions found.</p>
        <?php } ?>
    </div>

    <style>
        .transaction-card {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            transition: 0.3s ease-in-out;
            height: 100%;
        }

        .transaction-card:hover {
            transform: scale(1.02);
        }

        .pagination a {
            margin: 0 5px;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            background-color: #007bff;
        }

        .pagination a.active {
            background-color: #0056b3;
        }

        .mob{
            display: none;
        }

        @media (max-width: 768px) {
            .transaction-card {
                padding: 12px;
            }

            .pagination a {
                padding: 6px 10px;
            }

            .mob{
                display: block;
            }
        }

        @media (max-width: 480px) {
            .transaction-card {
                padding: 10px;
            }

            .pagination a {
                padding: 5px 8px;
            }
        }
    </style>

    <script>
        function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("active");
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>