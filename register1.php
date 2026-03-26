<?php
// register.php
// User registration with profile picture, bio, profession, and email verification

session_start();
require_once 'db.php';
require 'verify_email.php'; // Use verify_email.php for sending email

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and retrieve form inputs
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash password
    $bio = sanitize($_POST['bio']);
    $profession = sanitize($_POST['profession']);
    $interests = isset($_POST['interests']) ? $_POST['interests'] : [];

    // Generate Email Verification Token
    $verification_token = bin2hex(random_bytes(32)); // Secure token
    $expiry_time = date("Y-m-d H:i:s", strtotime("+1 hour")); // 1-hour expiry

    // Handle profile picture upload
    $profile_picture = '';
    if (!empty($_FILES['profile_picture']['name'])) {
        $uploadDir = 'uploads/profile_pictures/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = time() . "_" . basename($_FILES['profile_picture']['name']);
        $targetFile = $uploadDir . $fileName;
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // Allow only JPG, PNG, JPEG
        $allowedTypes = ['jpg', 'jpeg', 'png'];
        if (in_array($fileType, $allowedTypes)) {
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFile)) {
                $profile_picture = $targetFile;
            } else {
                $error = "Profile picture upload failed.";
            }
        } else {
            $error = "Only JPG, JPEG, and PNG files are allowed.";
        }
    }

    if (!isset($error)) {
        // Corrected SQL Query (added is_verified placeholder)
        $stmt = $pdo->prepare("INSERT INTO users (username, email, phone_no, password_hash, bio, profession, profile_picture, is_verified, verification_token, verification_token_expiry) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->execute([$username, $email, $phone, $password, $bio, $profession, $profile_picture, 0, $verification_token, $expiry_time]);

        // Send Verification Email using verify_email.php
        $verify_link = "https://connectwith9.com/verify_email.php?token=" . urlencode($verification_token);
        $subject = "Verify Your Email";
        $message = "Click this link to verify your email: <a href='$verify_link'>$verify_link</a><br>This link expires in 1 hour.";
        
        $email_status = sendMail($email, $subject, $message);
        if ($email_status !== true) {
            $error = "Verification email could not be sent. Please try again.";
        } else {
            // Redirect to verification pending page
            header("Location: verify_email.php?pending=true");
            exit();
        }
    }
}

// Fetch interests directly
$stmt = $pdo->query("SELECT id, name FROM interests");
$allInterests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>

<head>
    <title>Register</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .register-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="register-container">
            <h2 class="text-center">Register</h2>
            <?php if (isset($error)) {
                echo "<div class='alert alert-danger'>$error</div>";
            } ?>
            <form method="POST" action="register1.php" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Username:</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email:</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone No:</label>
                    <input type="tel" name="phone" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password:</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Profile Picture:</label>
                    <input type="file" name="profile_picture" class="form-control" accept="image/*">
                </div>
                <div class="mb-3">
                    <label class="form-label">About/Bio:</label>
                    <textarea name="bio" class="form-control" rows="3"
                        placeholder="Write something about yourself..."></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Profession:</label>
                    <input type="text" name="profession" class="form-control" placeholder="Enter your profession">
                </div>
                <div class="mb-3">
                    <label class="form-label">Select Your Interests:</label>
                    <div class="row">
                        <?php
                        $columns = 2;
                        $counter = 0;
                        foreach ($allInterests as $interest) {
                            if ($counter % $columns == 0) {
                                echo '<div class="col-md-6">';
                            }
                            ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="interests[]"
                                    value="<?php echo $interest['id']; ?>">
                                <label class="form-check-label"> <?php echo $interest['name']; ?> </label>
                            </div>
                            <?php
                            $counter++;
                            if ($counter % $columns == 0) {
                                echo '</div>';
                            }
                        }
                        if ($counter % $columns != 0) {
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">Register</button>
            </form>
            <p class="mt-3 text-center">Already registered? <a href="login.php">Login here</a></p>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
