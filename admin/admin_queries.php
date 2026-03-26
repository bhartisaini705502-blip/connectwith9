<?php
// session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$username = "u647904474_microjobs";
$password = "TechTrick@1234#";
$database = "u647904474_microjobs";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['query_id'], $_POST['new_status'])) {
    $stmt = $pdo->prepare("UPDATE user_queries SET status = ? WHERE id = ?");
    $stmt->execute([$_POST['new_status'], $_POST['query_id']]);
    header("Location: admin_queries.php");
    exit;
}

$stmt = $pdo->query("
    SELECT uq.*, u.username, u.email 
    FROM user_queries uq
    JOIN users u ON uq.user_id = u.id
");
$queries = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - User Queries</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="index.css">
    <style>
        body {
            background-color: #f5f8fa;
        }

        .card {
            border: none;
            border-left: 5px solid #0d6efd;
            transition: 0.3s;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .badge-status {
            font-size: 0.9rem;
            padding: 0.4em 0.75em;
        }

        .scroll-area {
            max-height: 80vh;
            overflow-y: auto;
            scrollbar-width: none;
        }

        .scroll-area::-webkit-scrollbar {
            display: none;
        }

        .query-buttons button {
            min-width: 80px;
        }

        .card-footer {
            background-color: #f8f9fa;
        }

        @media (max-width: 576px) {
            .query-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

<?php include("sidebar.php"); ?>
<button class="toggle-btn btn btn-light m-3" onclick="toggleSidebar()">☰</button>

<div class="main-content px-4">
    <h3 class="my-4">User Queries</h3>

    <?php if (count($queries)): ?>
        <div class="row g-4 scroll-area">
            <?php foreach ($queries as $q): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($q['subject']) ?></h5>
                            <h6 class="card-subtitle mb-2 text-muted"><?= htmlspecialchars($q['username']) ?> (<?= htmlspecialchars($q['email']) ?>)</h6>
                            <p class="card-text mt-3"><?= nl2br(htmlspecialchars($q['message'])) ?></p>
                            <span class="badge bg-<?= 
                                $q['status'] === 'Solved' ? 'success' : ($q['status'] === 'Pending' ? 'warning text-dark' : 'danger')
                            ?> badge-status"><?= $q['status'] ?></span>
                        </div>
                        <div class="card-footer d-flex gap-2 justify-content-between query-buttons">
                            <form method="POST" class="d-flex gap-2 flex-wrap w-100">
                                <input type="hidden" name="query_id" value="<?= $q['id'] ?>">
                                <button name="new_status" value="Pending" class="btn btn-sm btn-outline-warning">Pending</button>
                                <button name="new_status" value="Solved" class="btn btn-sm btn-outline-success">Solved</button>
                                <button name="new_status" value="Rejected" class="btn btn-sm btn-outline-danger">Rejected</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center">No queries found.</div>
    <?php endif; ?>
</div>

<script>
    function toggleSidebar() {
        document.getElementById("sidebar").classList.toggle("active");
    }
</script>

</body>
</html>
