<?php

session_start();
?>
<div class="sidebar" id="sidebar">
    <!-- Sidebar content -->
    <a href="dashboard1.php"><h4>Dashboard</h4></a>
    <a href="help.php"><i class="fas fa-info-circle"></i>&nbsp; Help</a>
    <a href="logout.php"><i class="fas fa-sign-out-alt"></i>&nbsp; Logout</a>
</div>

<!-- Toggle Button (Only for Mobile View) -->
<button class="toggle-btn" onclick="toggleSidebar()">☰</button>

<style>
    /* Sidebar Styles */
    .sidebar {
        width: 22%;
        height: 100%;
        background: #0D3B6A;
        color: white;
        position: fixed;
        padding: 20px;
        padding-left: 35px;
        transition: transform 0.3s ease-in-out;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        top: 0;
    }

    .toggle-btn {
        background: #0D3B6A;
        border: none;
        color: white;
        font-size: 24px;
        cursor: pointer;
        position: fixed;
        top: 15px;
        left: 15px;
        padding: 5px 8px;
        border-radius: 5px;
        z-index: 1001;
        display: none;
    }

    .sidebar a {
        color: white;
        text-decoration: none;
        display: flex;
        align-items: center;
        padding: 10px;
        margin: 5px 0;
        border-radius: 5px;
        transition: background 0.3s ease-in-out;
        font-size: 16px;
    }

    .sidebar a i {
        margin-right: 10px;
    }

    .sidebar a:hover {
        background: #0D3B6A;
    }

    /* Mobile View */
    @media (max-width: 768px) {
        .sidebar {
            width: 250px;
            transform: translateX(-100%);
            position: fixed;
            z-index: 1000;
            transition: transform 0.3s ease-in-out;
            padding-left: 20px; /* Adjust padding for mobile */
        }

        .sidebar.active {
            transform: translateX(0);
        }

        .toggle-btn {
            display: block;
        }
    }
</style>

<script>
    function toggleSidebar() {
        document.getElementById("sidebar").classList.toggle("active");
    }
</script>
