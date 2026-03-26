<?php

require_once 'common.php';
require_once '../functions.php';
if (!isAdminLoggedIn()) {
    adminRedirect('index.php');
}

require_once '../db.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Interest</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="index.css">
    <style>
a{
    text-decoration: none;
    color:white;
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



    <div class="main-content">
        <div class="container text-center ">
            <h1><b>Leads Management</b></h1>
            <div class="row text-white">
            <div class="col p-4 pt-5 pb-5 bg-danger mx-2 mt-2 rounded ">
                <h4>
                   <a href="leads_interest.php"> Leads Interests</a>
                   </h4>
                </div>
                <div class="col p-4 pt-5 pb-5 bg-primary mx-2 mt-2 ">
                <h4>
                  <a href="add_leads.php">Add Leads</a>  
                  </h4>
                </div>
                <div class="col p-4 pt-5 pb-5 bg-success mx-2 mt-2 rounded ">
                <h4>
                 <a href="all_leads.php">  All Leads</a> 
                    </h4>
                </div>
                
                
            </div>
            <div class="row text-white">
            <div class="col p-4 pt-5 pb-5 bg-warning mx-2 mt-2 rounded ">
                <h4>
                 <a href="leads_review.php">  Review Leads Submissions</a> 
                    </h4>
                </div>
               
                <div class="col p-4 pt-5 pb-5 bg-secondary mx-2 mt-2 rounded ">
               
                    <h4>
                        <a href="leads_clients.php"> Leads Client</a>
                    </h4>
                </div>
                
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