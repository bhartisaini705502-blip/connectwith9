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
function getWalletBalance($user_id) {
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

<!DOCTYPE html>
<html>
<head>
    <title>Withdrawal Requests</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h2 class="text-center">Withdrawal Requests</h2>
        <p class="text-center">
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a> 
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </p>

        <?php if (empty($requests)) { ?>
            <div class="alert alert-info text-center">No pending withdrawal requests.</div>
        <?php } else { ?>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Request ID</th>
                        <th>User</th>
                        <th>Amount</th>
                        <th>UPI ID</th>
                        <th>Bank Account</th>
                        <th>IFSC Code</th>
                        <th>Request Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $req) { ?>
                    <tr>
                        <td><?php echo $req['id']; ?></td>
                        <td><?php echo htmlspecialchars($req['username']); ?></td>
                        <td>Rs. <?php echo htmlspecialchars($req['amount']); ?></td>
                        <td><?php echo htmlspecialchars($req['upi_id']); ?></td>
                        <td><?php echo htmlspecialchars($req['bank_account']); ?></td>
                        <td><?php echo htmlspecialchars($req['ifsc_code']); ?></td>
                        <td><?php echo $req['request_date']; ?></td>
                        <td>
                            <form method="POST" action="withdrawal_requests.php">
                                <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                                <textarea name="comment" class="form-control mb-2" placeholder="Add comment"></textarea>
                                <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
                                <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">Reject</button>
                            </form>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
