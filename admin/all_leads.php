<?php
require_once "../db.php";

// Handle delete request
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_id"])) {
    $deleteId = intval($_POST["delete_id"]);
    $stmt = $pdo->prepare("DELETE FROM leads WHERE id = ?");
    $stmt->execute([$deleteId]);
    $message = "Lead deleted successfully.";
}

// Fetch all leads
$stmt = $pdo->query("SELECT leads.*, leads_interests.name AS interest_name FROM leads 
                     LEFT JOIN leads_interests ON leads.interest_id = leads_interests.interest_id 
                     ORDER BY leads.createdat DESC");
$leads = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <link rel="stylesheet" href="index.css">
    <style>
        strong{
            color:rgb(0, 78, 235);
        }
    </style>
</head>

<body>

    <?php include("sidebar.php"); ?>
    <button class="toggle-btn" onclick="toggleSidebar()">☰</button>

    <div class="main-content p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-center"><b>All Leads</b></h2>
            <a href="add_leads.php" class="btn btn-success">+ Add New Lead</a>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success"><?= $message ?></div>
        <?php endif; ?>

        <div class="row g-4">
            <?php if (count($leads) === 0): ?>
                <div class="col-12 text-center">
                    <div class="alert alert-warning">No leads found.</div>
                </div>
            <?php endif; ?>

            <?php foreach ($leads as $lead): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body d-flex flex-column">
                            
                            <h5 class="card-title"><strong><?= htmlspecialchars($lead["title"]) ?></strong></h5>
                            <p class="card-text"><strong>Reward:</strong> ₹<?= number_format($lead["reward"], 2) ?></p>
                            <p class="card-text"><strong>Interest:</strong>
                                <?= htmlspecialchars($lead["interest_name"] ?? "N/A") ?></p>
                            <p class="card-text"><strong>Expiry:</strong> <?= $lead["expirydate"] ?></p>
                            <p class="card-text"><strong>Created:</strong>
                                <?= date("Y-m-d", strtotime($lead["createdat"])) ?></p>
                            <?php if (!empty($lead["url"])): ?>
                                <p>
                                    <strong>URL:</strong>
                                    <a href="<?= htmlspecialchars($lead["url"]) ?>" target="_blank">
                                        <?= htmlspecialchars($lead["url"]) ?>
                                    </a>
                                </p>
                            <?php endif; ?>

                            <div class="d-flex justify-content-between mt-3">
                                <a href="edit_lead.php?id=<?= $lead["id"] ?>" class="btn btn-sm btn-warning">Edit</a>
                                <form method="POST" onsubmit="return confirm('Are you sure to delete this lead?');">
                                    <input type="hidden" name="delete_id" value="<?= $lead["id"] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("active");
        }
    </script>

</body>

</html>