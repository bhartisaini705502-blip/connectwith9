<?php
// admin/admin_management.php
// Manage Admins (Only for Super Admins)

require_once 'common.php';
require_once '../db.php'; // Database connection

if (!isAdminLoggedIn()) {
    adminRedirect('index.php'); // Redirect if not logged in
}

// Restrict access to Super Admin only
if ($_SESSION['admin_role'] !== 'super_admin') {
    die("Access Denied: Only Super Admins can manage admins.");
}

// Handle adding a new admin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_admin'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    if (!empty($username) && !empty($email) && !empty($role) && !empty($_POST['password'])) {
        $stmt = $pdo->prepare("INSERT INTO admins (username, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $email, $password, $role]);
        header("Location: admin_management.php?success=Admin+Added+Successfully");
        exit();
    } else {
        $error = "All fields are required.";
    }
}

// Fetch all admins
$stmt = $pdo->query("SELECT * FROM admins ORDER BY role DESC, id ASC");
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle deleting an admin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_admin'])) {
    $admin_id = intval($_POST['admin_id']);

    // Ensure the Super Admin is not deleted
    $stmt = $pdo->prepare("SELECT role FROM admins WHERE id = ?");
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin['role'] === 'super_admin') {
        $error = "Super Admin cannot be deleted.";
    } else {
        $stmt = $pdo->prepare("DELETE FROM admins WHERE id = ?");
        $stmt->execute([$admin_id]);
        header("Location: admin_management.php?success=Admin+Deleted+Successfully");
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 30px;
        }
        .table-container {
            margin-top: 20px;
            background: #fff;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center mb-4">Admin Management</h2>

        <?php if (isset($_GET['success'])) { ?>
            <div class="alert alert-success text-center"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php } ?>
        <?php if (isset($error)) { ?>
            <div class="alert alert-danger text-center"><?php echo htmlspecialchars($error); ?></div>
        <?php } ?>

        <div class="table-container">
            <h4>Add New Admin</h4>
            <form method="POST" action="admin_management.php">
                <div class="row">
                    <div class="col-md-3">
                        <input type="text" name="username" class="form-control" placeholder="Username" required>
                    </div>
                    <div class="col-md-3">
                        <input type="email" name="email" class="form-control" placeholder="Email" required>
                    </div>
                    <div class="col-md-2">
                        <select name="role" class="form-select" required>
                            <option value="editor">Editor</option>
                            <option value="moderator">Moderator</option>
                            <option value="super_admin">Super Admin</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" name="add_admin" class="btn btn-primary">Add</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-container mt-4">
            <h4>All Admins</h4>
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($admins)) { ?>
                        <tr><td colspan="5" class="text-center">No admins found.</td></tr>
                    <?php } else { ?>
                        <?php foreach ($admins as $admin) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($admin['id']); ?></td>
                                <td><?php echo htmlspecialchars($admin['username']); ?></td>
                                <td><?php echo htmlspecialchars($admin['email']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo ($admin['role'] == 'super_admin') ? 'danger' : 'primary'; ?>">
                                        <?php echo ucfirst($admin['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($admin['role'] !== 'super_admin') { ?>
                                        <form method="POST" action="admin_management.php" style="display:inline;">
                                            <input type="hidden" name="admin_id" value="<?php echo $admin['id']; ?>">
                                            <button type="submit" name="delete_admin" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');">Delete</button>
                                        </form>
                                    <?php } else { ?>
                                        <button class="btn btn-secondary btn-sm" disabled>Super Admin</button>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <p class="text-center mt-3"><a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a></p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
