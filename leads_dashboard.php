<?php
session_start();
require_once 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leads Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">

    <style>
        body {
            background-color: #f8f9fa;
        }

        a {
            text-decoration: none;
        }

        .card-hover {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>

<body>
    <?php include("sidebar2.php"); ?>

    <!-- Toggle Button for Mobile -->
    <button class="toggle-btn" onclick="toggleSidebar()">☰</button>

    <div class="main-content">
        <div class="container-fluid">
            <h1 class="mb-4 fw-bold text-dark text-center">Welcome to Your Leads Dashboard !</h1>

            <div class="row g-4 ">
                <a href="all_leads.php" class="text-white">
                    <div class="col-md-6 col-xl-4 ">
                        <div class="card bg-success text-white card-hover">
                            <div class="card-body text-center">
                                <i class="fas fa-bullhorn fa-3x mb-3"></i>
                                <h5 class="card-title"><a href="all_leads.php" class="text-white">Available Leads</a>
                                </h5>
                            </div>
                        </div>
                    </div>
                </a>

                <div class="col-md-6 col-xl-4">
                    <div class="card bg-danger text-white card-hover">
                        <div class="card-body text-center">
                            <i class="fas fa-file-alt fa-3x mb-3"></i>
                            <h5 class="card-title"><a href="user_leads.php" class="text-white">My Lead Submissions</a>
                            </h5>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-4">
                    <div class="card bg-primary text-white card-hover">
                        <div class="card-body text-center">
                            <i class="fas fa-users fa-3x mb-3"></i>
                            <h5 class="card-title"><a href="my_clients.php" class="text-white">My Clients</a></h5>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-4">
                    <div class="card bg-warning text-white card-hover">
                        <div class="card-body text-center">
                            <i class="fas fa-cogs fa-3x mb-3"></i>
                            <h5 class="card-title"><a href="#" class="text-white">Option 1</a></h5>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-4">
                    <div class="card bg-secondary text-white card-hover">
                        <div class="card-body text-center">
                            <i class="fas fa-lightbulb fa-3x mb-3"></i>
                            <h5 class="card-title"><a href="#" class="text-white">Option 2</a></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toggle Sidebar Script -->
    <script>
        function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("active");
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq"
        crossorigin="anonymous"></script>
</body>

</html>