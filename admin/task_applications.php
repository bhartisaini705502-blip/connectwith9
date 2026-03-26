<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="index.css">
    <style>
/* General Layout */
.applications-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.application-card {
    background-color: #ffffff;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    padding: 20px;
    transition: transform 0.3s ease;
}

.application-card:hover {
    transform: scale(1.05);
}

.application-details p {
    font-size: 14px;
    margin-bottom: 10px;
}

.application-title {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 10px;
}

.application-actions {
    margin-top: 10px;
    text-align: center;
}

.application-actions .btn-sm {
    font-size: 12px;
    width: 40%;
}

.badge {
    font-size: 12px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .applications-container {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 480px) {
    .applications-container {
        grid-template-columns: 1fr;
    }

    .application-card {
        padding: 15px;
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


    <?php
// admin/task_applications.php
// Admin panel to approve or reject task applications

require_once 'common.php';
require_once '../db.php'; // Database connection

if (!isAdminLoggedIn()) {
    adminRedirect('index.php');
}

// Fetch all pending task applications
$stmt = $pdo->query("
    SELECT * FROM task_applications 
    WHERE task_status = 'pending'
    ORDER BY applied_at DESC
");

$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle application approval/rejection
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $application_id = intval($_POST['application_id']);
    $action = $_POST['action'];

    if ($action === 'approve') {
        $status = 'approved';
    } elseif ($action === 'reject') {
        $status = 'rejected';
    }

    $stmt = $pdo->prepare("UPDATE task_applications SET task_status = ? WHERE id = ?");
    $stmt->execute([$status, $application_id]);

    // Redirect after processing
    header("Location: task_applications.php?success=Application+Updated");
    exit();
}
?> 
    <div class="main-content">
    <div class="container">
    <h2 class="text-center mb-4">Task Applications Approval</h2>

    <?php if (isset($_GET['success'])) { ?>
        <div class="alert alert-success text-center"><?php echo htmlspecialchars($_GET['success']); ?></div>
    <?php } ?>

    <div class="applications-container">
        <?php if (empty($applications)) { ?>
            <div class="alert alert-info text-center">No pending applications.</div>
        <?php } else { ?>
            <?php foreach ($applications as $app) { ?>
                <div class="application-card">
                    <div class="application-details">
                        <h5 class="application-title"><?php echo htmlspecialchars($app['task_title']); ?></h5>
                        <p><strong>Applicant:</strong> <?php echo htmlspecialchars($app['user_id']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($app['email']); ?></p>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($app['phone_no']); ?></p>
                        <p><strong>Applied At:</strong> <?php echo htmlspecialchars($app['applied_at']); ?></p>
                        <p><strong>Task Status:</strong> <span class="badge bg-warning">Pending</span></p>
                        <p><strong>Overall Status:</strong> 
                            <span class="badge bg-<?php echo ($app['status'] == 'active') ? 'success' : 'secondary'; ?>">
                                <?php echo ucfirst($app['status']); ?>
                            </span>
                        </p>
                    </div>
                    <div class="application-actions">
                        <form method="POST" action="task_applications.php" style="display:inline;">
                            <input type="hidden" name="application_id" value="<?php echo $app['id']; ?>">
                            <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
                            <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">Reject</button>
                        </form>
                    </div>
                </div>
            <?php } ?>
        <?php } ?>
    </div>
</div>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


<script>
        function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("active");
        }
    </script>