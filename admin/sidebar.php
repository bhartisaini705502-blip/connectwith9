<?php
// session_start();

// Assuming user role is stored in session after login
// $user_role = $_SESSION['user_role'] ?? '';

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
        <a href="dashboard.php"><h4 class="text-white">&nbsp; &nbsp; Dashboard</h4></a>
        <a href="add_task.php">&nbsp; Add Task</a>
        <a href="add_interest.php">&nbsp; Add Interest</a>
        <a href="all_tasks.php">&nbsp; All Tasks</a>
        <a href="tasks.php">&nbsp; Review Submission</a>
        <a href="leads_management.php">&nbsp; Leads Management</a>
        <a href="task_applications.php">&nbsp; Task Approval</a>
        <a href="withdrawal_requests.php">&nbsp; Withdrawal Request</a>
        <a href="admin_management.php">&nbsp; Admin Management</a>
        <a href="admin-payments.php">&nbsp; Payments</a>
        <a href="admin_queries.php">&nbsp; User Queries</a>
        <a href="manage_users.php">&nbsp; Manage Users</a>
        <a href="logout.php" class="text-danger">&nbsp; Logout</a>
    </div>
</div>
