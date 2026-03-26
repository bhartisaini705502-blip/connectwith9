<?php
// admin/common.php
// Common functions for the admin panel

session_start();
require_once '../db.php';

// Check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin']);
}

// Redirect helper for admin pages
function adminRedirect($url) {
    header("Location: $url");
    exit();
}
?>
