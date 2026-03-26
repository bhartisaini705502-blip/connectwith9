<?php
// admin/logout.php
// Admin logout page

session_start();
unset($_SESSION['admin']);
header("Location: index.php");
exit();
?>
