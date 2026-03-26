<?php
// verify_email.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Load PHPMailer

function sendMail($to, $subject, $message)
{
    $mail = new PHPMailer(true);
    try {
        // SMTP configuration
        $mail->isSMTP();
        $mail->SMTPAuth = true;
        $mail->Host = "smtp.hostinger.com";
        $mail->Username = "info@connectwith9.com"; 
        $mail->Password = "TechTrick@1234#"; // Replace with your actual SMTP password

        // Use TLS instead of SSL for better compatibility
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
        $mail->Port = 587; // Use 587 with STARTTLS

        // Email details
        $mail->setFrom('info@connectwith9.com', 'MICROJOBS');
        $mail->addAddress($to);
        $mail->Subject = $subject;
        $mail->isHTML(true);
        $mail->Body = $message;

        // Debugging (Remove this after testing)
        $mail->SMTPDebug = 0; // Change to 2 for debugging
        $mail->Debugoutput = 'html';

        // Send the email
        $mail->send();
        return true; // Success
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo); // Log errors
        return "Email could not be sent. Error: " . $mail->ErrorInfo;
    }
}

// Prevent execution when included in `register.php`
if (!isset($_GET['token'])) {
    return; // Stop execution if token is not set
}

// Email verification process
require_once 'db.php';

$token = $_GET['token'];

// Validate token
$stmt = $pdo->prepare("SELECT id, verification_token_expiry FROM users WHERE verification_token = ? AND is_verified = 0");
$stmt->execute([$token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $expiry_time = strtotime($user['verification_token_expiry']);
    if ($expiry_time > time()) {
        // Token is valid and not expired, update verification status
        $updateStmt = $pdo->prepare("UPDATE users 
                                     SET is_verified = 1, status = 'active', verification_token = NULL, verification_token_expiry = NULL 
                                     WHERE id = ?");
        $updateStmt->execute([$user['id']]);

        echo "<h3>Email successfully verified. Your account is now <strong>active</strong>. You can now <a href='login.php'>login</a>.</h3>";
    } else {
        echo "<h3>Verification link has expired. Please register again.</h3>";
    }
} else {
    echo "<h3>Invalid or expired token.</h3>";
}
?>
