<?php
session_start();
require_once 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user'];

// Fetch leads submitted by the user using PDO
$query = "SELECT ls.*, l.title AS lead_title 
          FROM leads_submissions ls 
          JOIN leads l ON ls.lead_id = l.id 
          WHERE ls.user_id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$user_id]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Submitted Leads</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .lead-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-left: 4px solid green;
        }

        .lead-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .badge-status {
            font-size: 0.85rem;
        }
    </style>
</head>

<body>
    <?php include("sidebar2.php"); ?>

    <!-- Toggle Button for Mobile -->
    <button class="toggle-btn" onclick="toggleSidebar()">☰</button>

    <div class="main-content p-4">
        <h2 class="mb-4 fw-bold text-dark">My Lead Submissions</h2>

        <div class="row g-4">
            <?php if (!empty($results)): ?>
                <?php foreach ($results as $row): ?>
                    <?php
                    $status = ucfirst($row['status']);
                    $badgeClass = match ($status) {
                        'Approved' => 'bg-success',
                        'Rejected' => 'bg-danger',
                        'Pending' => 'bg-warning text-dark',
                        default => 'bg-secondary',
                    };
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card lead-card">
                            <div class="card-body">
                                <h5 class="card-title text-primary">
                                    <a href="lead_submission_details.php?id=<?= $row['id'] ?>" class="text-decoration-none">
                                        <?= htmlspecialchars($row['lead_title']) ?>
                                    </a>
                                </h5>

                                <p class="mb-2"><strong>Client:</strong> <?= htmlspecialchars($row['client_name']) ?></p>
                                <p class="mb-2"><strong>Contact:</strong> <?= htmlspecialchars($row['client_contact']) ?></p>
                                <p class="mb-2"><strong>Submitted:</strong>
                                    <?= date("M d, Y", strtotime($row['submitted_at'])) ?></p>
                                <?php if (!empty($row['url'])): ?>
                                    <p class="mb-2"><strong>URL:</strong>
                                        <a href="<?= htmlspecialchars($row['url']) ?>" target="_blank">
                                            <?= htmlspecialchars($row['url']) ?>
                                        </a>
                                    </p>
                                <?php endif; ?>

                                <p class="mb-2"><strong>Status:</strong>
                                    <span class="badge <?= $badgeClass ?> badge-status"><?= $status ?></span>
                                </p>

                                <?php if (strtolower($status) === 'approved' && !empty($row['work_status'])): ?>
                                    <p class="mb-2"><strong>Work Status:</strong> <?= htmlspecialchars($row['work_status']) ?></p>
                                <?php endif; ?>

                                <a href="lead_submission_details.php?id=<?= $row['id'] ?>"
                                    class="text-decoration-none btn btn-success">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">You have not submitted any leads yet.</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <!-- Toggle Sidebar Script -->
    <script>
        function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("active");
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>