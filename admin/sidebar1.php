<?php
session_start();

// Assuming user role is stored in session after login
$user_role = $_SESSION['user_role'] ?? '';

// Sidebar HTML
?>

<style>
    .scrollable-no-scrollbar {
    max-height: 590px;
    overflow-y: scroll;

    /* Hide scrollbar for Chrome, Safari, Opera */
    scrollbar-width: none; /* Firefox */
    -ms-overflow-style: none; /* IE and Edge */
}

.scrollable-no-scrollbar::-webkit-scrollbar {
    display: none; /* Chrome, Safari, Opera */
}

</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<div class="sidebar" id="sidebar">
    <button class="toggle-btn" onclick="toggleSidebar()">☰</button>
    <div class="sidebar-content scrollable-no-scrollbar">
        <a href="dashboard1.php"><h4 class="text-white">&nbsp; &nbsp; Dashboard</h4></a>
        <a href="logout.php" class="text-danger">&nbsp; Logout</a>
    </div>
</div>
