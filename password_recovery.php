<?php
require_once 'config.php';
require_once 'db.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "<script>alert('Invalid or expired token.'); window.location.href = 'forgot_password.php';</script>";
        exit;
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if ($password !== $confirm_password) {
            echo "<script>alert('Passwords do not match.');</script>";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $update = $pdo->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
            $update->execute([$hashed, $user['id']]);

            echo "<script>alert('Password updated successfully.'); window.location.href = 'login.php';</script>";
            exit;
        }
    }
} else {
    echo "<script>alert('No token provided.'); window.location.href = 'forgot_password.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #667eea, #764ba2);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            border-radius: 20px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
            padding: 30px;
        }
        .btn-primary {
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card bg-white">
                    <h3 class="text-center mb-4">🔐 Reset Your Password</h3>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Enter new password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control" placeholder="Confirm password" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Update Password</button>
                        </div>
                    </form>
                    <p class="text-center mt-3 mb-0"><a href="login.php" class="text-decoration-none">Back to Login</a></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
