<?php
require_once 'common.php';
require_once '../db.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    // Check if email exists
    $query = "SELECT id FROM admins WHERE email = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Store token in DB
        $update_query = "UPDATE admins SET reset_token = ?, reset_token_expiry = ? WHERE email = ?";
        $stmt = $pdo->prepare($update_query);
        $stmt->execute([$token, $expiry, $email]);

        if ($stmt->rowCount() > 0) {
            $reset_link = "https://connectwith9.com/admin/password_recovery.php?token=$token";
            $subject = "Password Reset Request";
            $message = "
                <html>
                <head><title>Password Reset</title></head>
                <body>
                    <p>Hi,</p>
                    <p>Click the link below to reset your password:</p>
                    <a href='$reset_link'>$reset_link</a>
                    <p>This link expires in 1 hour.</p>
                </body>
                </html>
            ";

            // Send email using PHPMailer
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.hostinger.com';  
                $mail->SMTPAuth = true;
                $mail->Username = 'info@connectwith9.com';  
                $mail->Password = 'Aman9908@1234#'; 
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('info@connectwith9.com', 'ConnectWith9');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = $message;

                $mail->send();
                echo "<script>alert('Password reset link sent to your email.'); window.location.href = 'index.php';</script>";
            } catch (Exception $e) {
                echo "<script>alert('Failed to send email. Error: " . addslashes($mail->ErrorInfo) . "');</script>";
            }
        } else {
            echo "<script>alert('Error generating reset link. Try again later.');</script>";
        }
    } else {
        echo "<script>alert('No account found with this email.');</script>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
</head>
<body>
    <h2>Forgot Password</h2>
    <form method="POST">
        <input type="email" name="email" placeholder="Enter your email" required>
        <button type="submit">Send Reset Link</button>
    </form>
</body>
</html>
