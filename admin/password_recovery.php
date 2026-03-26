<?php
require_once 'common.php';
require_once '../db.php';

if (!isset($_GET['token'])) {
    die("Invalid request.");
}

$token = $_GET['token'];

// Check if token is valid and not expired
$query = "SELECT id, email FROM admins WHERE reset_token = ? AND reset_token_expiry > NOW()";
$stmt = $pdo->prepare($query);
$stmt->execute([$token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Invalid or expired token.");
}

$email = $user['email'];

// Handle password reset
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['password']) || empty($_POST['password'])) {
        echo "<script>alert('Password cannot be empty.');</script>";
    } else {
        $new_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // Update password in DB
        $update_query = "UPDATE admins SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE email = ?";
        $stmt = $pdo->prepare($update_query);
        $stmt->execute([$new_password, $email]);

        if ($stmt->rowCount() > 0) {
            echo "<script>alert('Password reset successful. You can now log in.'); window.location.href = 'index.php';</script>";
        } else {
            echo "<script>alert('Error resetting password. Try again.');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
</head>
<body>
    <h2>Reset Password</h2>
    <form method="POST">
        <input type="password" name="password" placeholder="Enter new password" required>
        <button type="submit">Reset Password</button>
    </form>
</body>
</html>
