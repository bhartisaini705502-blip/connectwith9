<?php
require_once 'common.php';
if (!isAdminLoggedIn()) {
    adminRedirect('index.php');
}

require_once '../db.php'; // Make sure $pdo is defined here

$message = '';

try {
    if (isset($_POST['add_interest'])) {
        $name = trim($_POST['name']);
        if (!empty($name)) {
            $stmt = $pdo->prepare("INSERT INTO leads_interests (name) VALUES (?)");
            $stmt->execute([$name]);
            $message = "Interest added successfully.";
        }
    }

    if (isset($_POST['update_interest'])) {
        $interest_id = (int) $_POST['interest_id'];
        $name = trim($_POST['name']);
        if (!empty($name)) {
            $stmt = $pdo->prepare("UPDATE leads_interests SET name = ? WHERE interest_id = ?");
            $stmt->execute([$name, $interest_id]);
            $message = "Interest updated successfully.";
        }
    }

    if (isset($_GET['delete_interest'])) {
        $interest_id = (int) $_GET['delete_interest'];

        $stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM leads WHERE interest_id = ?");
        $stmt->execute([$interest_id]);
        $row = $stmt->fetch();

        if ($row['count'] == 0) {
            $stmt = $pdo->prepare("DELETE FROM leads_interests WHERE interest_id = ?");
            $stmt->execute([$interest_id]);
            $message = "Interest deleted successfully.";
        } else {
            $message = "Cannot delete interest, it is associated with a lead.";
        }
    }

    $stmt = $pdo->query("SELECT * FROM leads_interests ORDER BY created_at DESC");
    $interests = $stmt->fetchAll();
} catch (PDOException $e) {
    $message = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lead Interests</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="index.css">
    <style>
        a {
            text-decoration: none;
            color: white;
            /* font-weight: 600; */
        }
    </style>
</head>

<body>
    <?php
    include("sidebar.php");
    ?>

    <!-- Toggle Button for Mobile -->
    <button class="toggle-btn" onclick="toggleSidebar()">☰</button>



    <div class="main-content p-5">
        <h1 class="mb-4 text-center">
            <b>Manage Lead Interests</b> </h1>
        

        <?php if (!empty($message)): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header">
            <h3> <b> Add New Interest</b>  </h3> 
        </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="name" class="form-label">Interest Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <button type="submit" name="add_interest" class="btn btn-primary">Add Interest</button>
                </form>
            </div>
        </div>


        <div class="mb-3 text-center">
    <a href="leads_management.php" class="btn btn-secondary">
        <i class="fa fa-arrow-left me-1"></i> Back to Leads Management
    </a>
</div>

        <div class="card">
            <div class="card-header text-center">
               <h3> <b> Existing Interests </b></h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40%;">Interest Name</th>
                            <th style="width: 60%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($interests) > 0): ?>
                            <?php foreach ($interests as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td>
                                        <form method="POST" class="d-inline-flex gap-2 align-items-center">
                                            <input type="hidden" name="interest_id" value="<?php echo $row['interest_id']; ?>">
                                            <input type="text" name="name" value="<?php echo htmlspecialchars($row['name']); ?>"
                                                class="form-control form-control-sm" required style="max-width: 200px;">
                                            <button type="submit" name="update_interest"
                                                class="btn btn-sm btn-success">Update</button>
                                        </form>
                                        <a href="?delete_interest=<?php echo $row['interest_id']; ?>"
                                            onclick="return confirm('Are you sure you want to delete this interest?')"
                                            class="btn btn-sm btn-danger ms-2">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="2">No interests found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Sidebar Toggle Script -->
    <script>
        function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("active");
        }
    </script>

    <!-- FontAwesome for Icons -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq"
        crossorigin="anonymous"></script>
</body>

</html>