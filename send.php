<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST["contact_names"]);
    $subject = htmlspecialchars($_POST["contact_subject"]);
    $email = htmlspecialchars($_POST["contact_email"]);
    $phone = htmlspecialchars($_POST["contact_phone"]);
    $message = htmlspecialchars($_POST["contact_message"]);

    $mail = new PHPMailer(true);

    try {
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // Use your SMTP host
        $mail->SMTPAuth   = true;
        $mail->Username   = 'info@connectwith9.com'; // Your email
        $mail->Password   = 'Aman9908@1234#'; // Your email password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Sender and recipient
        $mail->setFrom('info@connectwith9.com', 'ConnectWith9');
        $mail->addAddress('amanbisht9908@gmail.com'); // Change recipient email if needed

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'New Contact Form Submission: ' . $subject;
        $mail->Body    = "<h3>Contact Form Details:</h3>
                          <p><strong>Name:</strong> $name</p>
                          <p><strong>Email:</strong> $email</p>
                          <p><strong>Phone:</strong> $phone</p>
                          <p><strong>Message:</strong> $message</p>";

        // Send email
        if ($mail->send()) {
            echo 'Message sent successfully!';
        } else {
            echo 'Error sending message: ' . $mail->ErrorInfo;
        }
    } catch (Exception $e) {
        echo 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo;
    }
} else {
    echo 'Invalid request method!';
}
?>
