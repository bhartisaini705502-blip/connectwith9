<?php
// process_contact.php - Processes the contact form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input data
    $name = htmlspecialchars($_POST["name"]);
    $email = htmlspecialchars($_POST["email"]);
    $message = htmlspecialchars($_POST["message"]);
    
    // Here you can add code to store the data in your database or send an email notification.

    // Bootstrap-styled response page
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
       <meta charset='UTF-8'>
       <meta name='viewport' content='width=device-width, initial-scale=1.0'>
       <title>Contact Us - Thank You</title>
       <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css'>
    </head>
    <body class='bg-light'>
       <div class='container mt-5 text-center'>
          <div class='card shadow-sm p-4'>
             <h1 class='text-primary'>Thank You, $name!</h1>
             <p class='mt-3'>We have received your message and will get back to you shortly.</p>
             <a href='home.php' class='btn btn-primary mt-3'>Back to Home</a>
          </div>
       </div>
       <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
    </body>
    </html>";
}
?>
