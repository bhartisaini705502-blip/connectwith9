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
/* Grid Layout for Requests */
.requests-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
}

/* Request Card Styling */
.request-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    padding: 20px;
    transition: transform 0.3s ease;
}

.request-card:hover {
    transform: translateY(-5px);
}

/* Request Details */
.request-details p {
    font-size: 14px;
    margin-bottom: 8px;
}

.request-title {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 10px;
}

/* Request Actions */
.request-actions {
    margin-top: 15px;
}

.request-actions .btn-sm {
    font-size: 12px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .requests-container {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 480px) {
    .requests-container {
        grid-template-columns: 1fr;
    }

    .request-card {
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
    // admin/withdrawal_requests.php
// Process user withdrawal requests
    
    require_once 'common.php';
    require_once '../db.php'; // Database connection
    
    if (!isAdminLoggedIn()) {
        adminRedirect('index.php'); // Redirect to login page if not logged in
    }

    // Ensure only 'super_admin' can process requests
    if ($_SESSION['admin_role'] !== 'super_admin') {
        die("Access Denied: Only Super Admins can process withdrawals.");
    }

    $admin_id = $_SESSION['admin']; // Ensure admin_id is set correctly
    
    // Function to get wallet balance dynamically from wallet_transactions
    function getWalletBalance($user_id)
    {
        global $pdo;
        $stmt = $pdo->prepare("
        SELECT SUM(CASE WHEN transaction_type = 'credit' THEN amount ELSE -amount END) AS balance 
        FROM wallet_transactions 
        WHERE user_id = ?
    ");
        $stmt->execute([$user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['balance'] ? $row['balance'] : 0;
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $request_id = intval($_POST['request_id']);
        $action = $_POST['action'];
        $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

        // Fetch withdrawal request details
        $stmt = $pdo->prepare("SELECT * FROM withdrawal_requests WHERE id = ? AND status = 'pending'");
        $stmt->execute([$request_id]);
        $request = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($request) {
            $user_id = $request['user_id'];
            $amount = $request['amount'];

            if ($action == 'approve') {
                $status = 'approved';

                // Get the user's wallet balance dynamically
                $current_balance = getWalletBalance($user_id);

                if ($current_balance >= $amount) {
                    // Start transaction to ensure consistency
                    $pdo->beginTransaction();
                    try {
                        // Insert a debit transaction in wallet_transactions
                        $stmt = $pdo->prepare("INSERT INTO wallet_transactions (user_id, transaction_type, amount, description, transaction_date) 
                                           VALUES (?, 'debit', ?, 'Withdrawal Approved', NOW())");
                        $stmt->execute([$user_id, $amount]);

                        // Update withdrawal request status
                        $stmt = $pdo->prepare("UPDATE withdrawal_requests SET status = ?, processed_by = ?, processed_date = NOW(), admin_comment = ? WHERE id = ?");
                        $stmt->execute([$status, $admin_id, $comment, $request_id]);
                        $pdo->commit(); // Commit transaction
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        die("Transaction failed: " . $e->getMessage());
                    }
                } else {
                    die("Error: Insufficient wallet balance.");
                }
            } elseif ($action == 'reject') {
                $status = 'rejected';

                // Update withdrawal request status
                $stmt = $pdo->prepare("UPDATE withdrawal_requests SET status = ?, processed_by = ?, processed_date = NOW(), admin_comment = ? WHERE id = ?");
                $stmt->execute([$status, $admin_id, $comment, $request_id]);
            }
        }
    }

    // Fetch all pending withdrawal requests
    $stmt = $pdo->query("SELECT wr.*, u.username FROM withdrawal_requests wr 
                     JOIN users u ON wr.user_id = u.id 
                     WHERE wr.status = 'pending' 
                     ORDER BY wr.request_date DESC");
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <div class="main-content">
    <div class="container mt-4">
    <h2 class="text-center">Withdrawal Requests</h2>

    <?php if (empty($requests)) { ?>
        <div class="alert alert-info text-center">No pending withdrawal requests.</div>
    <?php } else { ?>
        <div class="requests-container">
            <?php foreach ($requests as $req) { ?>
                <div class="request-card">
                    <div class="request-details">
                        <h5 class="request-title">Withdrawal Request #<?php echo $req['id']; ?></h5>
                        <p><strong>User:</strong> <?php echo htmlspecialchars($req['username']); ?></p>
                        <p><strong>Amount:</strong> Rs. <?php echo htmlspecialchars($req['amount']); ?></p>
                        <p><strong>UPI ID:</strong> <?php echo htmlspecialchars($req['upi_id']); ?></p>
                        <p><strong>Bank Account:</strong> <?php echo htmlspecialchars($req['bank_account']); ?></p>
                        <p><strong>IFSC Code:</strong> <?php echo htmlspecialchars($req['ifsc_code']); ?></p>
                        <p><strong>Request Date:</strong> <?php echo $req['request_date']; ?></p>
                    </div>
                    <div class="request-actions">
                        <form method="POST" action="withdrawal_requests.php">
                            <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                            <textarea name="comment" class="form-control mb-2" placeholder="Add comment"></textarea>
                            <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
                            <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">Reject</button>
                        </form>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
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