<?php
require_once 'common.php';
if (!isAdminLoggedIn()) {
    adminRedirect('index.php');
}

require_once '../db.php';

$errors = [];
$success = "";

// Add Interest
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_interest"])) {
    $interest_name = trim($_POST["interest_name"]);
    if (empty($interest_name)) {
        $errors[] = "Interest name is required.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO interests (name, created_at) VALUES (:name, NOW())");
        $stmt->execute([':name' => $interest_name]);
        $success = "Interest added successfully!";
    }
}

// Edit Interest
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["edit_interest"])) {
    $interest_id = $_POST["interest_id"];
    $interest_name = trim($_POST["interest_name"]);
    if (empty($interest_name)) {
        $errors[] = "Interest name is required.";
    } else {
        $stmt = $pdo->prepare("UPDATE interests SET name = :name WHERE id = :id");
        $stmt->execute([':name' => $interest_name, ':id' => $interest_id]);
        $success = "Interest updated successfully!";
    }
}

// Delete Interest
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_interest"])) {
    $stmt = $pdo->prepare("DELETE FROM interests WHERE id = :id");
    $stmt->execute([':id' => $_POST["interest_id"]]);
    $success = "Interest deleted successfully!";
}

// Pagination Settings
$limit = 9;
$page = isset($_GET["page"]) && is_numeric($_GET["page"]) ? (int) $_GET["page"] : 1;
$offset = ($page - 1) * $limit;

// Search Interests
$search_query = "";
$search_condition = "";
$params = [];

if (isset($_GET["search"])) {
    $search_query = trim($_GET["search"]);
    if (!empty($search_query)) {
        $search_condition = " WHERE name LIKE :search";
        $params[':search'] = "%$search_query%";
    }
}

// Get total number of interests
$total_sql = "SELECT COUNT(*) FROM interests" . $search_condition;
$total_stmt = $pdo->prepare($total_sql);
$total_stmt->execute($params);
$total_rows = $total_stmt->fetchColumn();
$total_pages = ceil($total_rows / $limit);

// Fetch interests with pagination
$sql = "SELECT id, name, created_at FROM interests" . $search_condition . " LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);

// Bind parameters correctly
if (!empty($search_query)) {
    $stmt->bindValue(':search', "%$search_query%", PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$interests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Interests</title>
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>

<body>
    <?php include("sidebar.php"); ?>
    <button class="toggle-btn" onclick="toggleSidebar()">☰</button>

    <div class="main-content">
        <h2 class="text-center"><b>Manage Interests</b></h2>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error) echo "<p>$error</p>"; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Add Interest Form -->
        <form method="post" class="mb-4">
            <div class="input-group">
                <input type="text" name="interest_name" class="form-control" placeholder="Add new interest" required>
                <button type="submit" name="add_interest" class="btn btn-primary">Add</button>
            </div>
        </form>

        <!-- Search -->
        <form method="get" class="mb-3">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search interests..."
                    value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
            </div>
        </form>

        <!-- Interests List -->
        <div class="row">
            <?php if (count($interests) > 0): ?>
                <?php foreach ($interests as $interest): ?>
                    <div class="col-md-4">
                        <div class="card mb-3">
                            <div class="card-body" style="text-align: center;">
                                <h5 class="card-title"><?php echo htmlspecialchars($interest['name']); ?></h5>
                                <p class="card-text"><small class="text-muted">Added on:
                                        <?php echo $interest['created_at']; ?></small></p>
                                <a href="edit_interest.php?id=<?php echo $interest['id']; ?>" class="btn btn-success btn-sm" style="width: 40%;">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="delete_interest.php?id=<?php echo $interest['id']; ?>" class="btn btn-danger btn-sm" style="width: 40%;">
                                    <i class="fas fa-edit"></i> Delete
                                </a>
                               
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center">No interests found.</p>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <nav>
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item"><a class="page-link"
                            href="?page=1&search=<?php echo urlencode($search_query); ?>">First</a></li>
                    <li class="page-item"><a class="page-link"
                            href="?page=<?php echo ($page - 1); ?>&search=<?php echo urlencode($search_query); ?>">Previous</a>
                    </li>
                <?php endif; ?>

                <li class="page-item active"><a class="page-link"><?php echo $page; ?></a></li>

                <?php if ($page < $total_pages): ?>
                    <li class="page-item"><a class="page-link"
                            href="?page=<?php echo ($page + 1); ?>&search=<?php echo urlencode($search_query); ?>">Next</a>
                    </li>
                    <li class="page-item"><a class="page-link"
                            href="?page=<?php echo $total_pages; ?>&search=<?php echo urlencode($search_query); ?>">Last</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("active");
        }
    </script>
</body>

</html>
