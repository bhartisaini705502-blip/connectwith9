<?php


require_once 'common.php';
require_once '../db.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Optional: Admin check (you must define this function elsewhere)
if (function_exists('isAdminLoggedIn') && !isAdminLoggedIn()) {
    if (function_exists('adminRedirect')) {
        adminRedirect('index.php');
    } else {
        header('Location: index.php');
        exit;
    }
}

if (!isset($_GET['user_id'])) {
    echo "User ID is missing!";
    exit;
}

$user_id = intval($_GET['user_id']);

$sql = "SELECT * FROM detailed_profile WHERE user_id = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => $user_id]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detailed Profile</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="index.css">
    <style>

    </style>
</head>

<body>
    <?php
    include("sidebar.php");
    ?>

    <!-- Toggle Button for Mobile -->
    <button class="toggle-btn" onclick="toggleSidebar()">☰</button>

    <div class="main-content">

        <?php
        if (!$profile) {
            echo "No detailed profile found for this user.";
            exit;
        }

        ?>

        <div class="container mt-5">
            <div class="card shadow p-4">
                <h2 class="mb-4 text-center text-success">User Detailed Profile</h2>

                <div class="mb-3">
                    <strong>Education:</strong>
                    <p><?= nl2br(htmlspecialchars($profile['education'])) ?></p>
                </div>

                <div class="mb-3">
                    <strong>Experience:</strong>
                    <p><?= nl2br(htmlspecialchars($profile['experience'])) ?></p>
                </div>

                <div class="mb-3">
                    <strong>Skills:</strong>
                    <p><?= nl2br(htmlspecialchars($profile['skills'])) ?></p>
                </div>

                <?php if (!empty($profile['certifications'])): ?>
                    <div class="mb-3">
                        <strong>Certifications:</strong>
                        <p><?= nl2br(htmlspecialchars($profile['certifications'])) ?></p>
                    </div>
                <?php endif; ?>

                <?php
                $resumeFile = basename($profile['resume_path']); // safely strip path
                $resumePath = '../uploads/resumes/' . rawurlencode($resumeFile);
                // $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/resumes/' . rawurldecode($resumeFile);
                $fullPath = realpath(__DIR__ . '/../uploads/resumes/' . $resumeFile);

                ?>

                <?php if (!empty($resumeFile) && file_exists($fullPath)): ?>
                    <div class="mb-3">
                        <strong>Resume:</strong>

                        <a href="<?= $resumePath ?>" class="btn btn-primary" target="_blank">View Resume</a>
                        <a href="<?= $resumePath ?>" class="btn btn-success ms-2" download>Download Resume</a>
                    </div>
                <?php else: ?>
                    <div class="mb-3 text-danger">
                        <strong>Resume file not found.</strong>
                    </div>
                <?php endif; ?>


                <p class="text-muted">Created at: <?= date('Y-m-d', strtotime($profile['created_at'])) ?></p>
<p class="text-muted">Last updated: <?= date('Y-m-d', strtotime($profile['updated_at'])) ?></p>


                <a href="javascript:history.back()" class="btn btn-secondary mt-3">Go Back</a>
            </div>
        </div>

    </div>

    <!-- FontAwesome for Icons -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>

    <!-- Sidebar Toggle Script -->
    <script>
        function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("active");
        }
    </script>

    <!-- FontAwesome for Icons -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>

</html>