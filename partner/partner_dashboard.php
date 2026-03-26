<?php
session_start();
require_once '../db.php'; // Database connection
require_once '../functions.php'; // Include functions

if (!isset($_SESSION['partner_id'])) {
    header("Location: index.php");
    exit();
}

$partner_id = $_SESSION['partner_id'];
$stmt = $pdo->prepare("SELECT username FROM partners WHERE id = ?");
$stmt->execute([$partner_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$username = $user ? $user['username'] : 'Partner';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partner Dashboard</title>

    <!-- CSS -->
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>

    <style>
        body {
            margin: 0;
        }

        #sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            height: 100%;
            background-color: #0d3b6a;
            padding-top: 60px;
            transition: transform 0.3s ease;
        }

        #sidebar.active {
            transform: translateX(-100%);
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.3s ease;
        }

        #sidebar.active ~ .main-content {
            margin-left: 0;
        }

        .toggle-btn {
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1050;
            background-color: #0d3b6a;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
        }

        /* Responsive */
        @media (max-width: 768px) {
            #sidebar {
                transform: translateX(-100%);
            }

            #sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>

    <!-- Toggle Button -->
    <button class="toggle-btn" onclick="toggleSidebar()">☰</button>

    <!-- Sidebar -->
    <div id="sidebar">
        <?php include("sidebar1.php"); ?>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
            <p>This is your partner dashboard.</p>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById("sidebar");
            if (window.innerWidth <= 768) {
                sidebar.classList.toggle("show");
            } else {
                sidebar.classList.toggle("active");
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
