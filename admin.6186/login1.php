<?php
session_start();
require_once 'db.php';
require_once 'functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];

    // Fetch user data based on email
    $stmt = $pdo->prepare("SELECT id, email, password, role, status FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if (!password_verify($password, $user['password'])) {
            $error = "Incorrect password.";
        } 
        } elseif ($user['role'] === 'moderator') {
            $_SESSION['user'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            header("Location: dashboard1.php");
            exit();
        } else {
            $error = "Only partners (moderators) are allowed to log in here.";
        }
    } else {
        $error = "No account found with this email.";
    }

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            /* background-color:#548ff0; */
            background-image: url(images/MAIN_BG.jpg);
            background-size: cover;
            background-repeat: no-repeat;
            background-color:#07b9f680;
            font-family: 'Arial', sans-serif;
            background-repeat: none;
        }

        @media (max-width: 768px) {
            background-color:#07b9f680;
        }


        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100%;
            margin-top: 5%;
        }

        .card {
            max-width: 400px;
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background-color:#ffffffb0;
            padding: 20px 50px 20px 30px;
        }

        h3 {
            color: #333;
            font-weight: bold;
            text-align: center;
        }

        .form-label {
            color: #555;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            font-size: 16px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
        }

        button {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border-radius: 8px;
            border: none;
            background-color: #4e73df;
            color: white;
            cursor: pointer;
        }

        button:hover {
            background-color: #2e59d9;
        }

        .text-center a {
            text-decoration: none;
            color: #4e73df;
        }

        .text-center a:hover {
            text-decoration: underline;
        }

        .alert {
            color: red;
            background-color: #f8d7da;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        a {
            text-decoration: none;
        }

    </style>
</head>

<body>
    <div class="container">
        <div class="card">

            <p style="text-align:center;"> <span><a href="index.php"><img src="images/logo.png" alt=""
                            style="max-width100px; max-height:50px"></a></span> </p>
            <h3>Login</h3>
            <!-- Display error if any -->
            <?php if (isset($error)) {
                echo "<div class='alert'>$error</div>";
            } ?>

            <form method="POST" action="login.php" >
                <div>
                    <!-- <label for="email" class="form-label">Email address</label> -->
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>
                <div>
                    <!-- <label for="password" class="form-label">Password</label> -->
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>
                <div>
                    <button type="submit">Login</button>
                </div>
                <div class="text-center">
                    <p style="text-align:center;">Don't have an account? <a href="register.php">Sign up</a> </p>
                  
                </div>
            </form>
            <p style="text-align:center;"><a href="forgot_password.php" style="text-decoration:none; color:rgba(7, 186, 246, 0.77);">Forgot Password</a></p>
        </div>
    </div>
</body>


</html>