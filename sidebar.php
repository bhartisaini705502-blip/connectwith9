<div class="sidebar" id="sidebar">
    <button class="toggle-btn" onclick="toggleSidebar()">☰</button>
    <div class="none"><br><br></div>
    <a href="dashboard.php"><h4 style="">Dashboard</h4></a>
    <a href="profile.php"><i class="fas fa-user"></i>&nbsp; &nbsp; Profile</a>
    <a href="dashboard.php"><i class="fas fa-home"></i>&nbsp; &nbsp;Dashboard</a>
    <a href="applied_task.php"><i class="fas fa-tasks"></i>&nbsp; &nbsp;Applied Tasks</a>
    <a href="leads_dashboard.php"><i class="fas fa-tasks"></i>&nbsp; &nbsp;Leads Tasks</a>
    <a href="withdrawal_request.php"><i class="fas fa-exchange-alt"></i>&nbsp; &nbsp;Request Withdrawal</a>
    <a href="transitions.php"><i class="fas fa-exchange-alt"></i>&nbsp; &nbsp;Transaction</a>
    <a href="help.php"><i class="fas fa-info-circle"></i>&nbsp; &nbsp;Help</a>
    <a href="logout.php"><i class="fas fa-sign-out-alt"></i>&nbsp; &nbsp;Logout</a>
</div>

<style>
    /* Sidebar Styles */
    .none{
        display: none;
    }

    .sidebar {
        width: 22%;
        height: 100%;
        background:#0D3B6A;
        color: white;
        position: fixed;
        padding: 20px 20px 20px 35px;
        transition: transform 0.3s ease-in-out;
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
    }

    .sidebar a i {
        margin-right: 10px;
    }

    .sidebar a:hover,
    .sidebar .active {
        background:#0D3B6A;
    }

    /* Mobile View */
    @media (max-width: 768px) {
        .sidebar {
            width: 250px;
            transform: translateX(-100%);
            position: fixed;
            z-index: 1000;
        }

        .sidebar.active {
            transform: translateX(0);
        }

        .toggle-btn {
            display: block;
        }

        .none{
        display:block;
    }
    }
</style>

<script>
    function toggleSidebar() {
        document.getElementById("sidebar").classList.toggle("active");
    }
</script>
