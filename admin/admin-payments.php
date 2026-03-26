<?php
require_once 'common.php';
if (!isAdminLoggedIn()) {
    adminRedirect('index.php');
}
require_once '../db.php';

// Filters
$search = $_GET['search'] ?? '';
$transactionType = $_GET['type'] ?? '';
$dateFilter = $_GET['date'] ?? '';

$where = "WHERE 1";
$params = [];

if ($search) {
    $where .= " AND (u.username LIKE :search OR wt.description LIKE :search)";
    $params[':search'] = "%$search%";
}
if ($transactionType) {
    $where .= " AND wt.transaction_type = :type";
    $params[':type'] = $transactionType;
}
if ($dateFilter) {
    $where .= " AND DATE(wt.transaction_date) = :date";
    $params[':date'] = $dateFilter;
}

// Pagination
$limit = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$countStmt = $pdo->prepare("SELECT COUNT(*) 
    FROM wallet_transactions wt 
    JOIN users u ON wt.user_id = u.id 
    $where");
foreach ($params as $k => $v) $countStmt->bindValue($k, $v);
$countStmt->execute();
$total = $countStmt->fetchColumn();
$totalPages = ceil($total / $limit);

// Fetch transactions
$sql = "SELECT wt.*, u.username 
        FROM wallet_transactions wt 
        JOIN users u ON wt.user_id = u.id 
        $where 
        ORDER BY wt.transaction_date DESC 
        LIMIT :offset, :limit";
$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Payments</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="index.css">
    <style>
        body { background-color: #f4f7fa; }
        .card {
            border-radius: 12px;
            transition: 0.3s;
            border: none;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }
        .toggle-btn {
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1001;
        }
    </style>
</head>
<body>

<?php include("sidebar.php"); ?>
<button class="toggle-btn btn btn-light" onclick="toggleSidebar()">☰</button>

<div class="main-content p-4">
    <h2 class="text-center mb-4">User Payment Transactions</h2>

    <!-- Filters -->
    <form class="row g-2 mb-4" method="GET">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Search username or description" value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-3">
            <select name="type" class="form-select">
                <option value="">All Types</option>
                <option value="credit" <?= $transactionType === 'credit' ? 'selected' : '' ?>>Credit</option>
                <option value="debit" <?= $transactionType === 'debit' ? 'selected' : '' ?>>Debit</option>
            </select>
        </div>
        <div class="col-md-3">
            <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($dateFilter) ?>">
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary w-100">Filter</button>
        </div>
    </form>

    <?php if (!$transactions): ?>
        <div class="alert alert-warning text-center">No transactions found.</div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($transactions as $t): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="mb-0"><?= htmlspecialchars($t['username']) ?></h5>
                                <span class="badge <?= $t['transaction_type'] === 'credit' ? 'bg-success' : 'bg-danger' ?>">
                                    <?= ucfirst($t['transaction_type']) ?>
                                </span>
                            </div>
                            <h4 class="text-primary mb-2">₹<?= number_format($t['amount'], 2) ?></h4>
                            <p class="text-muted mb-1"><strong>Description:</strong> <?= htmlspecialchars($t['description']) ?></p>
                            <small class="text-secondary"><strong>Date:</strong> <?= date("d M Y, h:i A", strtotime($t['transaction_date'])) ?></small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $page == $i ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&type=<?= $transactionType ?>&date=<?= $dateFilter ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<script>
    function toggleSidebar() {
        document.getElementById("sidebar").classList.toggle("active");
    }
</script>

</body>
</html>
