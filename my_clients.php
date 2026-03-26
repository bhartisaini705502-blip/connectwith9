<?php
session_start();
require_once 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user'];

// Fetch unique clients submitted by the user
$query = "
    SELECT ls.*
    FROM leads_submissions ls
    INNER JOIN (
        SELECT client_name, client_contact, MAX(id) AS max_id
        FROM leads_submissions
        WHERE user_id = ?
        GROUP BY client_name, client_contact
    ) latest ON ls.id = latest.max_id
    WHERE ls.user_id = ?
    ORDER BY client_name ASC
";
$stmt = $pdo->prepare($query);
$stmt->execute([$user_id, $user_id]);
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Function to show only first 10 words
function getPreview($text, $wordLimit = 10) {
    $words = preg_split('/\s+/', strip_tags($text));
    return count($words) > $wordLimit
        ? htmlspecialchars(implode(' ', array_slice($words, 0, $wordLimit)) . '...')
        : htmlspecialchars(implode(' ', $words));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Clients</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .client-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-left: 4px solid #0d6efd;
        }

        .client-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>
    <?php include("sidebar2.php"); ?>

    <!-- Toggle Button for Mobile -->
    <button class="toggle-btn" onclick="toggleSidebar()">☰</button>

    <div class="main-content p-4">
        <h2 class="mb-4 fw-bold text-dark">My Clients</h2>

        <div class="row g-4">
            <?php if (!empty($clients)): ?>
                <?php foreach ($clients as $client): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card client-card">
                            <div class="card-body">
                                <h5 class="card-title text-primary">
                                    <i class="fa fa-user me-2"></i><?= htmlspecialchars($client['client_name']) ?>
                                </h5>
                                <p class="mb-2"><strong>Contact:</strong> <?= htmlspecialchars($client['client_contact']) ?></p>
                                <p class="mb-2"><strong>Status:</strong> <?= htmlspecialchars($client['status']) ?></p>
                                <p class="mb-2"><strong>Admin Remark:</strong>
                                    <?= !empty($client['admin_remarks']) ? nl2br(htmlspecialchars($client['admin_remarks'])) : 'N/A' ?>
                                </p>
                                <p class="mb-2"><strong>Work Status:</strong> <?= htmlspecialchars($client['work_status']) ?></p>

                                <?php if (!empty($client['client_details'])): ?>
                                    <p class="mb-2"><strong>Details:</strong>
                                        <?= getPreview($client['client_details']) ?>
                                        <a href="#" class="text-primary ms-2" onclick="showDetailsModal(`<?= htmlspecialchars(addslashes($client['client_details'])) ?>`)">...view more</a>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">No clients found. Submit a lead to add clients.</div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal for Full Client Details -->
    <div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailsModalLabel">Client Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="detailsModalBody"></div>
            </div>
        </div>
    </div>

    <!-- Toggle Sidebar Script -->
    <script>
        function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("active");
        }

        function showDetailsModal(details) {
            document.getElementById('detailsModalBody').innerHTML = details.replace(/\n/g, "<br>");
            const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
            modal.show();
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
