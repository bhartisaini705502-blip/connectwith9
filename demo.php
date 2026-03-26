<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="index.css">

    <style>
        /* Sidebar Custom Styling */
        .sidebar {
            width: 250px;
            height: 100vh;
            background: #0D3B6A;
            color: white;
            position: fixed;
            padding: 20px;
            transition: transform 0.3s ease-in-out;
        }

        /* Custom Class for Sidebar Toggle */
        .sidebar.inactive1 {
            transform: translateX(-100%);
        }

        .toggle-btn {
            position: fixed;
            top: 15px;
            left: 15px;
            background: #0D3B6A;
            color: white;
            font-size: 24px;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            z-index: 1001;
        }

        /* Responsive View */
        @media (max-width: 768px) {
            .sidebar {
                width: 250px;
                transform: translateX(-100%);
            }

            .sidebar.active1 {
                transform: translateX(0);
            }

            .toggle-btn {
                display: block;
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
// admin/manage_users.php
// Manage Users: Search, View, Activate/Deactivate, and View Transactions

require_once 'common.php';
require_once '../db.php'; // Database connection

if (!isAdminLoggedIn()) {
    adminRedirect('index.php'); // Redirect to login page if not logged in
}

$search = "";
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['search'])) {
    $search = trim($_GET['search']);
}

// Fetch users based on search input
if (!empty($search)) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? OR username LIKE ?");
    $stmt->execute([$search, "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY id DESC");
}

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Include Sidebar -->
<?php include("sidebar.php"); ?>

<!-- Toggle Button for Sidebar -->
<button class="toggle-btn" onclick="toggleSidebar()">☰</button>

<div class="main-content">
    <div class="container">
        <h2 class="text-center mb-4">Manage Users</h2>

        <form method="GET" action="manage_users.php" class="search-box text-center">
            <input type="text" name="search" class="form-control d-inline w-auto" placeholder="Search by User ID or Name" value="<?php echo htmlspecialchars($search); ?>" required>
            <button type="submit" class="btn btn-primary">Search</button>
            <a href="manage_users.php" class="btn btn-secondary">Reset</a>
        </form>

        <div class="table-container mt-4">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Wallet Balance</th>
                        <th>Status</th>
                        <th>Registered On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)) { ?>
                        <tr><td colspan="7" class="text-center">No users found.</td></tr>
                    <?php } else { ?>
                        <?php foreach ($users as $user) { ?>
                            <tr id="user_<?php echo $user['id']; ?>">
                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <?php 
                                        $stmt = $pdo->prepare("
                                            SELECT SUM(CASE WHEN transaction_type = 'credit' THEN amount ELSE -amount END) AS balance 
                                            FROM wallet_transactions 
                                            WHERE user_id = ?
                                        ");
                                        $stmt->execute([$user['id']]);
                                        $wallet_balance = $stmt->fetchColumn();
                                        echo "Rs. " . number_format($wallet_balance ?? 0, 2);
                                    ?>
                                </td>
                                <td>
                                    <button class="btn toggle-btn btn-<?php echo ($user['status'] == 'active') ? 'success' : 'secondary'; ?>" 
                                            onclick="toggleStatus(<?php echo $user['id']; ?>, '<?php echo $user['status']; ?>')">
                                        <?php echo ucfirst($user['status']); ?>
                                    </button>
                                </td>
                                <td><?php echo date("d M Y, h:i A", strtotime($user['created_at'])); ?></td>
                                <td>
                                    <a href="view_transactions.php?user_id=<?php echo $user['id']; ?>" class="btn btn-info btn-sm">View Transactions</a>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Sidebar Toggle Button using active1/inactive1 -->
<script>
function toggleSidebar() {
    let sidebar = document.querySelector(".sidebar");
    sidebar.classList.toggle("active1");
}

function toggleStatus(userId, currentStatus) {
            let newStatus = (currentStatus === 'active') ? 'inactive' : 'active';

            $.ajax({
                url: 'toggle_user_status.php',
                type: 'POST',
                data: { user_id: userId, status: newStatus },
                success: function(response) {
                    if (response === 'success') {
                        let button = $('#user_' + userId).find('.toggle-btn');
                        button.text(newStatus.charAt(0).toUpperCase() + newStatus.slice(1));
                        button.removeClass('btn-success btn-secondary');
                        button.addClass((newStatus === 'active') ? 'btn-success' : 'btn-secondary');
                        button.attr('onclick', "toggleStatus(" + userId + ", '" + newStatus + "')");
                    } else {
                        alert("Error updating user status.");
                    }
                }
            });
        }

</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
