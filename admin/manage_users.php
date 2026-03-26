<?php 
   
    require_once 'common.php';
    require_once '../db.php';

    if (!isAdminLoggedIn()) {
        adminRedirect('index.php');
    }

// Pagination Setup
$limit = 12; // Users per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Search Functionality
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchSql = $searchQuery ? "WHERE id = :search OR username LIKE :search" : "";

// Get Total Users Count
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM users $searchSql");
if ($searchQuery) {
    $countStmt->bindValue(':search', "%$searchQuery%", PDO::PARAM_STR);
}
$countStmt->execute();
$totalUsers = $countStmt->fetchColumn();
$totalPages = max(1, ceil($totalUsers / $limit));

// Fetch Users with Search & Pagination
$stmt = $pdo->prepare("SELECT * FROM users $searchSql ORDER BY created_at DESC LIMIT :offset, :limit");
if ($searchQuery) {
    $stmt->bindValue(':search', "%$searchQuery%", PDO::PARAM_STR);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="index.css">
    <style>
        .card{
            border-left: 4px solid green;
        }
    </style>
</head>

<body>

<?php
 include("sidebar.php");
?>
 <!-- Toggle Button for Mobile -->
 <button class="toggle-btn" onclick="toggleSidebar()">☰</button>


    <div class="main-content">
        <h2 class="text-center">Manage Users</h2>
        <form method="GET" action="manage_users.php" class="text-center mb-3">
        <input type="text" name="search" class="form-control d-inline w-auto" placeholder="Search by Name" value="<?php echo htmlspecialchars($searchQuery); ?>">

            <button type="submit" class="btn btn-primary">Search</button>
            <a href="manage_users.php" class="btn btn-secondary">Reset</a>
        </form>

        <div class="row">
            <?php foreach ($users as $user) { ?>
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">ID: <?php echo htmlspecialchars($user['id']); ?></h5>
                            <p class="card-text"><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                            <p class="card-text"><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                            <p class="card-text"><strong>Contact:</strong> <?php echo htmlspecialchars($user['phone_no']); ?></p>
                            <p class="card-text"><strong>Wallet Balance:</strong> 
                                <?php
                                $stmt = $pdo->prepare("SELECT SUM(CASE WHEN transaction_type = 'credit' THEN amount ELSE -amount END) AS balance FROM wallet_transactions WHERE user_id = ?");
                                $stmt->execute([$user['id']]);
                                $wallet_balance = $stmt->fetchColumn();
                                echo "Rs. " . number_format($wallet_balance ?? 0, 2);
                                ?>
                            </p>
                            <p class="card-text">
                                <strong>Status:</strong> 
                                <span class="badge bg-<?php echo ($user['status'] == 'active') ? 'success' : 'secondary'; ?>">
                                    <?php echo ucfirst($user['status']); ?>
                                </span>
                            </p>
                            <p class="card-text"><strong>Registered On:</strong> <?php echo date("d M Y, h:i A", strtotime($user['created_at'])); ?></p>
                            <a href="delete_user.php?user_id=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                            <a href="view_transactions.php?user_id=<?php echo $user['id']; ?>" class="btn btn-primary btn-sm">Transactions</a>
                            <a href="detailed_profile.php?user_id=<?php echo $user['id']; ?>" class="btn btn-success btn-sm">Profile</a>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
         <!-- Pagination Controls -->
         <!-- Pagination -->
    <nav>
        <ul class="pagination justify-content-center mt-4">
            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                <a class="page-link" href="?page=<?php echo max(1, $page - 1); ?>&search=<?php echo urlencode($searchQuery); ?>">&laquo;</a>
            </li>
            <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($searchQuery); ?>"> <?php echo $i; ?> </a>
                </li>
            <?php } ?>
            <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                <a class="page-link" href="?page=<?php echo min($totalPages, $page + 1); ?>&search=<?php echo urlencode($searchQuery); ?>">&raquo;</a>
            </li>
        </ul>
    </nav>
    </div>
</body>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>

<!-- Sidebar Toggle Script -->
<script>
    function toggleSidebar() {
        document.getElementById("sidebar").classList.toggle("active");
    }
</script>

</html>
