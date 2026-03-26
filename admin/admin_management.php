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
/* Admin Form */
.admin-form-container {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.admin-form {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: space-between;
}

.admin-form .form-control, .admin-form .form-select {
    flex: 1;
    min-width: 200px;
}

/* Admin Cards */
.admin-card-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 15px;
}

.admin-card {
    background: white;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    /* text-align: center; */
}

.admin-card h5 {
    font-size: 18px;
    font-weight: bold;
    text-align: center;
}

.admin-card p {
    font-size: 14px;
    margin: 5px 0;
}

/* Buttons */
.admin-card .btn {
    margin-top: 10px;
   width: 100%;
}

/* Responsive */
@media (max-width: 768px) {
    .admin-form {
        flex-direction: column;
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

    <div class="main-content">
    <div class="container mt-4">
    <h2 class="text-center mb-4">Admin Management</h2>

    <?php if (isset($_GET['success'])) { ?>
        <div class="alert alert-success text-center"><?php echo htmlspecialchars($_GET['success']); ?></div>
    <?php } ?>
    <?php if (isset($error)) { ?>
        <div class="alert alert-danger text-center"><?php echo htmlspecialchars($error); ?></div>
    <?php } ?>

    <!-- Add New Admin Form -->
    <div class="admin-form-container">
        <h4>Add New Admin</h4>
        <form method="POST" action="admin_management.php" class="admin-form">
            <input type="text" name="username" class="form-control" placeholder="Username" required>
            <input type="email" name="email" class="form-control" placeholder="Email" required>
            <select name="role" class="form-select" required>
                <option value="editor">Editor</option>
                <option value="moderator">Moderator</option>
                <option value="super_admin">Super Admin</option>
            </select>
            <input type="password" name="password" class="form-control" placeholder="Password" required>
            <button type="submit" name="add_admin" class="btn btn-primary">Add Admin</button>
        </form>
    </div>

    <!-- Display Admins in a Card-Based Layout -->
    <div class="admins-container mt-4">
        <h4>All Admins</h4>
        <?php if (empty($admins)) { ?>
            <div class="alert alert-info text-center">No admins found.</div>
        <?php } else { ?>
            <div class="admin-card-container">
                <?php foreach ($admins as $admin) { ?>
                    <div class="admin-card">
                        <h5><?php echo htmlspecialchars($admin['username']); ?></h5>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($admin['email']); ?></p>
                        <p>
                            <strong>Role:</strong>
                            <span class="badge bg-<?php echo ($admin['role'] == 'super_admin') ? 'danger' : 'primary'; ?>">
                                <?php echo ucfirst($admin['role']); ?>
                            </span>
                        </p>
                        <?php if ($admin['role'] !== 'super_admin') { ?>
                            <form method="POST" action="admin_management.php">
                                <input type="hidden" name="admin_id" value="<?php echo $admin['id']; ?>">
                                <button type="submit" name="delete_admin" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Are you sure?');">Delete</button>
                            </form>
                        <?php } else { ?>
                            <button class="btn btn-secondary btn-sm" disabled>Super Admin</button>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
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