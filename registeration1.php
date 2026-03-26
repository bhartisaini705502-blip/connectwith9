<?php
session_start();
require_once 'db.php';

// Include PHPMailer for SMTP Email
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// Function to sanitize input
function sanitize($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $bio = sanitize($_POST['bio']);
    $profession = sanitize($_POST['profession']);
    $interests = isset($_POST['interests']) ? $_POST['interests'] : [];

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        $error = "❌ Email already exists! Please use a different email.";
    }

    // Check if phone number already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE phone_no = ?");
    $stmt->execute([$phone]);
    if ($stmt->rowCount() > 0) {
        $error = "❌ Phone number already in use! Please use a different number.";
    }

    if (empty($error)) {
        // Generate verification token
        $verification_token = bin2hex(random_bytes(32));
        $token_expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));

        // Insert user into database
        $stmt = $pdo->prepare("INSERT INTO users (username, email, phone_no, password_hash, bio, profession, is_verified, verification_token, verification_token_expiry, status) 
                               VALUES (?, ?, ?, ?, ?, ?, 0, ?, ?, 'inactive')");
        if ($stmt->execute([$username, $email, $phone, $password, $bio, $profession, $verification_token, $token_expiry])) {

            // Get user ID
            $user_id = $pdo->lastInsertId();

            // Insert interests into `user_interests` table
            if (!empty($interests)) {
                $valid_interest_stmt = $pdo->query("SELECT id FROM interests");
                $valid_interest_ids = $valid_interest_stmt->fetchAll(PDO::FETCH_COLUMN);

                $interest_stmt = $pdo->prepare("INSERT INTO user_interests (user_id, interest_id) VALUES (?, ?)");
                foreach ($interests as $interest_id) {
                    if (in_array($interest_id, $valid_interest_ids)) {
                        $interest_stmt->execute([$user_id, $interest_id]);
                    }
                }
            }

            // ✅ Profile Picture Upload with Validation
            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $max_size = 2 * 1024 * 1024; // 2MB

                if (in_array($_FILES['profile_picture']['type'], $allowed_types) && $_FILES['profile_picture']['size'] <= $max_size) {
                    $file_name = time() . '_' . basename($_FILES['profile_picture']['name']);
                    $target_path = 'uploads/profile_pictures/' . $file_name;

                    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_path)) {
                        // ✅ Save correct file path to the database
                        $stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
                        $stmt->execute([$target_path, $user_id]);
                    } else {
                        $error = "❌ File upload failed.";
                    }
                } else {
                    $error = "❌ Invalid file format or size exceeded (Max: 2MB, Formats: JPEG, PNG, GIF).";
                }
            }

            if (empty($error)) {
                // Send verification email using PHPMailer
                $verification_link = "https://connectwith9.com/verify.php?token=" . $verification_token;

                $mail = new PHPMailer(true);
                try {
                    // SMTP Configuration
                    $mail->isSMTP();
                    $mail->Host = 'smtp.hostinger.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'info@connectwith9.com';
                    $mail->Password = 'Aman9908@1234#';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    // Email Content
                    $mail->setFrom('info@connectwith9.com', 'ConnectWith9');
                    $mail->addAddress($email, $username);
                    $mail->Subject = "Verify Your Account";
                    $mail->Body = "Hello $username,\n\nClick the link below to verify your account:\n$verification_link\n\nThis link will expire in 24 hours.\n\nBest Regards,\nConnectWith9 Team";

                    $mail->send();
                    header("Location: login.php?verify_email=true");
                    exit();
                } catch (Exception $e) {
                    $error = "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }
            }
        }
    }
}

// Fetch interests for display in the form
$stmt = $pdo->query("SELECT id, name FROM interests");
$allInterests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html>

<head>
    <title>Register</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #e3f2fd;
        }

        .register-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 768px) {
            .mob {
                width: 100%;
            }

            .register-container {
                max-width: 100%;
                /* max-height: 100%; */
            }
        }
    </style>
</head>

<body>
    <div class="mob">
        <div class="register-container mob" style="max-height: fit-content;">
        <p style="text-align:center;"> <span><a href="index.php">
                <img src="images/logonew.png" alt=""
                            style="max-width100px; max-height:50px"></a></span> </p>
            <h2 class="text-center">
                <strong>Registration Form</strong></h2>
            <?php if (isset($error)) {
                echo "<div class='alert ' style='color:red;'>$error</div>";
            } ?>
            <form method="POST" action="registeration.php" enctype="multipart/form-data">
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
                        if (!empty($allInterests)) {
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
                        } else {
                            echo "<p class='text-muted'>No interests found.</p>";
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
<!-- Add this inside your page body -->

<div id="captchaModal" style="
    display:none;
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.5);
    z-index: 9999;
    justify-content: center;
    align-items: center;
">
    <div style="
        background: white;
        padding: 20px;
        border-radius: 8px;
        max-width: 320px;
        width: 90%;
        text-align: center;
        box-shadow: 0 0 10px #00000050;
    ">
        <h4>Security Check</h4>
        <p id="question"></p>
        <select id="answerSelect" style="width: 100%; padding: 8px; font-size: 16px; margin-bottom: 15px;">
            <!-- Options will be populated by JS -->
        </select>
        <button id="submitCaptcha" style="padding: 10px 20px; font-size: 16px;">Submit</button>
        <p id="errorMsg" style="color: red; display: none; margin-top: 10px;"></p>
    </div>
</div>

<!-- Place this inside your page body -->

<div id="captchaModal" style="
    display:none;
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.5);
    z-index: 9999;
    display: flex;
    justify-content: center;
    align-items: center;
">
  <div style="
      background: white;
      padding: 20px;
      border-radius: 8px;
      max-width: 320px;
      width: 90%;
      text-align: center;
      box-shadow: 0 0 10px #00000050;
  ">
    <h4>Security Check</h4>
    <canvas id="captchaCanvas" width="280" height="50" style="margin-bottom: 15px; border:1px solid #ccc; display: block; margin-left:auto; margin-right:auto;"></canvas>
    <input type="text" id="answerInput" placeholder="Enter your answer" style="width: 100%; padding: 8px; font-size: 16px; margin-bottom: 15px;">
    <button id="submitCaptcha" style="padding: 10px 20px; font-size: 16px;">Submit</button>
    <p id="errorMsg" style="color: red; display: none; margin-top: 10px;"></p>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var form = document.querySelector('form');
  var modal = document.getElementById('captchaModal');
  var canvas = document.getElementById('captchaCanvas');
  var ctx = canvas.getContext('2d');
  var answerInput = document.getElementById('answerInput');
  var submitBtn = document.getElementById('submitCaptcha');
  var errorMsg = document.getElementById('errorMsg');

  var attempts = 0;
  var maxAttempts = 5;

  // Generate random math question
  var num1 = Math.floor(Math.random() * 9) + 1;
  var num2 = Math.floor(Math.random() * 9) + 1;
  var correctAnswer = num1 + num2;
  var questionText = `${num1} + ${num2} = ?`;

  function getRandomColor() {
    const letters = '0123456789ABCDEF';
    let color = '#';
    for (var i = 0; i < 6; i++) {
      color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
  }

  function drawCaptcha(text) {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.fillStyle = "#f0f0f0";
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    for (let i = 0; i < 5; i++) {
      ctx.strokeStyle = getRandomColor();
      ctx.beginPath();
      ctx.moveTo(Math.random() * canvas.width, Math.random() * canvas.height);
      ctx.lineTo(Math.random() * canvas.width, Math.random() * canvas.height);
      ctx.stroke();
    }

    ctx.font = "28px Arial";
    ctx.fillStyle = "#333";
    let x = 10;
    for (let i = 0; i < text.length; i++) {
      let char = text.charAt(i);
      let angle = (Math.random() - 0.5) * 0.6;
      let y = 30 + (Math.random() - 0.5) * 12;
      ctx.save();
      ctx.translate(x, y);
      ctx.rotate(angle);
      ctx.fillText(char, 0, 0);
      ctx.restore();
      x += 25 + Math.random() * 5;
    }
  }

  drawCaptcha(questionText);

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    errorMsg.style.display = 'none';
    answerInput.value = '';
    modal.style.display = 'flex';
    answerInput.focus();
  });

  submitBtn.addEventListener('click', function () {
    var userAnswer = answerInput.value.trim();

    if (!/^\d+$/.test(userAnswer)) {
      errorMsg.textContent = 'Please enter a valid numeric answer.';
      errorMsg.style.display = 'block';
      answerInput.focus();
      return;
    }

    if (parseInt(userAnswer, 10) === correctAnswer) {
      modal.style.display = 'none';
      errorMsg.style.display = 'none';
      attempts = 0;
      form.removeEventListener('submit', arguments.callee);
      form.submit();
    } else {
      attempts++;
      errorMsg.textContent = `Incorrect answer. Attempts left: ${maxAttempts - attempts}`;
      errorMsg.style.display = 'block';
      answerInput.focus();
      if (attempts >= maxAttempts) {
        alert("You have exceeded the maximum number of attempts.");
        modal.style.display = 'none';
      }
    }
  });
});
</script>


</body>

</html>