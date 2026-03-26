<?php
// logout.php
// Logs out the user

session_start();
session_destroy();
header("Location: login.php");
exit();
?>
