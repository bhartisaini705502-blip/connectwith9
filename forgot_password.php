<?php
require_once 'config.php';
require_once 'db.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Load PHPMailer

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    $query = "SELECT id FROM users WHERE email = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $update = "UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE email = ?";
        $stmt = $pdo->prepare($update);
        $stmt->execute([$token, $expiry, $email]);

        if ($stmt->rowCount() > 0) {
            $reset_link = "https://connectwith9.com/password_recovery.php?token=$token";
            $subject = "Password Reset Request";
            $message = "
                <html>
                <head><title>Password Reset</title></head>
                <body>
                    <p>Hello,</p>
                    <p>You requested to reset your password. Click the link below:</p>
                    <p><a href='$reset_link'>Reset Password</a></p>
                    <p>This link is valid for 1 hour.</p>
                </body>
                </html>
            ";

            $mail = new PHPMailer(true);
            try {
                $mail->SMTPDebug = 0;
                $mail->isSMTP();
                $mail->Host = 'smtp.hostinger.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'info@connectwith9.com';
                $mail->Password = 'Aman9908@1234#'; // Use an app password if needed
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('info@connectwith9.com', 'ConnectWith9');
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = $message;

                $mail->send();
                echo "<script>alert('Reset link sent. Check your email.'); window.location.href = 'login.php';</script>";
            } catch (Exception $e) {
                echo "<script>alert('Email error: " . addslashes($mail->ErrorInfo) . "');</script>";
            }
        } else {
            echo "<script>alert('Failed to generate reset link.');</script>";
        }
    } else {
        echo "<script>alert('No account with that email.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #00c6ff, #0072ff);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            background-color: #fff;
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
                <div class="card">
                    <h3 class="text-center mb-4">🔐 Forgot Your Password?</h3>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Enter Your Email</label>
                            <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Send Reset Link</button>
                        </div>
                    </form>
                    <p class="text-center mt-3 mb-0"><a href="login.php" class="text-decoration-none">Back to Login</a></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
