<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include "includes/connect.php";

    $token = mysqli_real_escape_string($conn, $_POST['token']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);

    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match.'); window.history.back();</script>";
        exit();
    }

    // Check if the token is valid
    $query = "SELECT * FROM users WHERE reset_token = '$token' AND reset_token_expiry > NOW()";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Update password and clear reset token
        $update_query = "UPDATE users SET password = '$hashed_password', reset_token = NULL, reset_token_expiry = NULL WHERE reset_token = '$token'";
        if (mysqli_query($conn, $update_query)) {
            echo "<script>alert('Password reset successful! You can now log in.'); window.location.href = 'login.php';</script>";
        } else {
            echo "<script>alert('Failed to reset password. Please try again later.');</script>";
        }
    } else {
        echo "<script>alert('Invalid or expired token.');</script>";
    }

    mysqli_close($conn);
}
?>

<?php
if (!isset($_GET['token'])) {
    echo "<script>alert('Invalid or missing token.'); window.location.href = 'login.php';</script>";
    exit();
}

$token = $_GET['token'];
include "connect.php";

// Check if the token is valid and not expired
$query = "SELECT * FROM users WHERE reset_token = '$token' AND reset_token_expiry > NOW()";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    echo "<script>alert('Invalid or expired token.'); window.location.href = 'login.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Reset Password</title>
    <!-- Add your CSS links -->
</head>
<body>
    <form method="POST">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        <label>New Password:</label>
        <input type="password" name="password" required>
        <label>Confirm Password:</label>
        <input type="password" name="confirm_password" required>
        <button type="submit">Reset Password</button>
    </form>
</body>
</html>
