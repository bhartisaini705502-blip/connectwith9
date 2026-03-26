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

// Category filter setup
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : '';

// Fetch all categories for dropdown
$categoriesStmt = $pdo->query("SELECT * FROM leads_interests ORDER BY name ASC");
$categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);

// Pagination setup
$limit = 6;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$page = max($page, 1);
$start = ($page - 1) * $limit;

// Query with optional category filter
$query = "SELECT leads.*, leads_interests.name AS interest_name 
          FROM leads 
          LEFT JOIN leads_interests ON leads.interest_id = leads_interests.interest_id";

if (!empty($selectedCategory)) {
    $query .= " WHERE leads.interest_id = :category";
}

$query .= " ORDER BY leads.createdat DESC LIMIT :start, :limit";
$stmt = $pdo->prepare($query);

if (!empty($selectedCategory)) {
    $stmt->bindValue(':category', $selectedCategory, PDO::PARAM_INT);
}
$stmt->bindValue(':start', $start, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$leads = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total count for pagination
if (!empty($selectedCategory)) {
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM leads WHERE interest_id = :category");
    $countStmt->bindValue(':category', $selectedCategory, PDO::PARAM_INT);
    $countStmt->execute();
    $totalLeads = $countStmt->fetchColumn();
} else {
    $totalStmt = $pdo->query("SELECT COUNT(*) FROM leads");
    $totalLeads = $totalStmt->fetchColumn();
}
$totalPages = ceil($totalLeads / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Leads</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <link rel="stylesheet" href="css/style.css">

    <style>
        strong {
            color: rgb(0, 78, 235);
        }
    </style>
</head>
<body>

<?php include("sidebar2.php"); ?>
<button class="toggle-btn" onclick="toggleSidebar()">☰</button>

<div class="main-content p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-center"><b>All Leads</b></h2>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>

    <!-- Category Dropdown -->
    <form method="GET" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <select name="category" class="form-select" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['interest_id'] ?>" <?= $selectedCategory == $cat['interest_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </form>

    <div class="row g-4">
        <?php if (count($leads) === 0): ?>
            <div class="col-12 text-center">
                <div class="alert alert-warning">No leads found.</div>
            </div>
        <?php endif; ?>

        <?php foreach ($leads as $lead): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm" style="background-color:rgba(248, 249, 250, 0.8); color: #212529;">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><strong><?= htmlspecialchars($lead["title"]) ?></strong></h5>
                        <p class="card-text"><strong>Reward:</strong> ₹<?= number_format($lead["reward"], 2) ?></p>
                        <p class="card-text"><strong>Category:</strong> <?= htmlspecialchars($lead["interest_name"] ?? "N/A") ?></p>
                        <p class="card-text"><strong>Max Submissions:</strong> <?= htmlspecialchars($lead["maxsubmissions"] ?? "N/A") ?></p>
                        <p class="card-text"><strong>Expiry:</strong> <?= date("d-m-Y", strtotime($lead["expirydate"])) ?></p>
                        <p class="btn btn-primary">
                            <a href="submit_lead.php?lead_id=<?= $lead['id'] ?>" style="color:white;">Submit/View Lead</a>
                        </p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page - 1 ?>&category=<?= urlencode($selectedCategory) ?>">Previous</a>
                </li>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&category=<?= urlencode($selectedCategory) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page + 1 ?>&category=<?= urlencode($selectedCategory) ?>">Next</a>
                </li>
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
