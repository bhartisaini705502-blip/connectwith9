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

    </style>
</head>

<body>
    <?php
    include("sidebar1.php");
    ?>

    <!-- Toggle Button for Mobile -->
    <button class="toggle-btn" onclick="toggleSidebar()">☰</button>


    <?php
    // admin/all_tasks.php
// Admin panel to manage all tasks
    
    require_once 'common.php';
    require_once '../db.php'; // Database connection
    
    if (!isAdminLoggedIn()) {
        if (role=="moderator");
        adminRedirect('index.php');
    }
    ?>

    <style>



    </style>
    <div class="main-content">
             <>asdjnoljnas
        <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Vero ipsa dignissimos esse est perspiciatis. Facilis, fugiat. Provident unde nam blanditiis dolorum, aspernatur obcaecati animi eos sint asperiores quaerat in officia!</p>
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

    <!-- Sidebar Toggle Script -->
    <script>
        function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("active");
        }
    </script>
</body>

</html>