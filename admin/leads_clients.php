<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

$perPage = 12;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $perPage;

$totalStmt = $pdo->query("SELECT COUNT(*) FROM leads_submissions WHERE status = 'approved'");
$total = $totalStmt->fetchColumn();
$totalPages = ceil($total / $perPage);

$stmt = $pdo->prepare("SELECT l.title AS lead_title, u.username AS submitted_by, ls.client_name, ls.client_contact, ls.client_details 
                       FROM leads_submissions ls
                       LEFT JOIN leads l ON ls.lead_id = l.id
                       LEFT JOIN users u ON ls.user_id = u.id
                       WHERE ls.status = 'approved'
                       ORDER BY ls.submitted_at DESC
                       LIMIT :limit OFFSET :offset");

$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$leads = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leads Client</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="index.css">
    <style>
        body {
            background: #f8f9fa;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.05);
            transition: transform 0.2s ease-in-out;
            border-left: 4px solid green;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-title {
            font-size: 1.2rem;
            color: #0d6efd;
        }

        .card p {
            margin-bottom: 0.5rem;
        }

        .lead-title-badge {
            background-color:rgb(28, 97, 17);
            color: white;
            display: inline-block;
            padding: 5px 10px;
            border-radius: 6px;
            margin-bottom: 10px;
            font-size: 0.9rem;
        }

        @media (max-width: 576px) {
            .card {
                margin-bottom: 1rem;
            }
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
    <h2 class="text-center mb-4"><b>Lead Clients</b></h2>
    <div class="container py-4">
   

    <?php if (count($leads) === 0): ?>
        <div class="alert alert-info text-center">No lead client data available.</div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($leads as $lead): ?>
                <div class="col-md-4 col-sm-6">
                <div class="card p-3 h-100">
    <div class="card-body d-flex flex-column">
        <div class="lead-title-badge"><?= htmlspecialchars($lead['lead_title']) ?></div>
        <p><strong>Client Name:</strong> <?= htmlspecialchars($lead['client_name']) ?></p>
        <p><strong>Contact:</strong> <?= htmlspecialchars($lead['client_contact']) ?></p>
        <p><strong>Details:</strong><br><?= nl2br(htmlspecialchars($lead['client_details'])) ?></p>
        <p class="mt-2"><strong>Client By:</strong> <?= htmlspecialchars($lead['submitted_by']) ?></p>
    </div>
</div>

                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>


    </div>

    <!-- FontAwesome for Icons -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>

    <!-- Sidebar Toggle Script -->
    <script>
        function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("active");
        }
    </script>

    <!-- FontAwesome for Icons -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>    
</body>

</html>