<?php
session_start();

require_once 'common.php';
require_once '../db.php'; // Database connection

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
            // Not a moderator
            session_abort();
            session_destroy();
            header("Location: login1.php?error=Only+partners+(moderators)+are+allowed+to+log+in+here.");
            exit();
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
    <title>Document</title>

<style>
        body {
            background-image: url(../images/MAIN_BG.jpg);
            background-size: cover;
            background-repeat: no-repeat;
            background-color: #00abe6;
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            /* min-height: 100vh; */
            padding: 20px 1px;
            transition: all 0.3s ease-in-out;
            margin-top: 20px;
        }

        .card {
            max-width: 450px;
            width: 100%;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            background-color: rgba(255, 255, 255, 0.85);
            padding: 30px 0px;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: transform 0.3s ease-in-out;
        }

        h2 {
            color: #333;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 20px;
            text-align: center;
            color: #4e73df;
        }

        .form-label {
            color: #555;
            font-size: 14px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            /* padding: 15px; */
            border-radius: 8px;
            font-size: 16px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 15px;
            font-size: 16px;
            border-radius: 8px;
            border: none;
            background-color: #4e73df;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease;
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

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 0 15px;
                margin-top: 50px;
            }

            .card {
                padding: 10px;
            }

            h2 {
                font-size: 22px;
                margin-bottom: 15px;
            }

            input[type="text"], input[type="password"] {
                font-size: 14px;
                /* padding: 12px; */
            }

            button {
                padding: 14px;
                font-size: 15px;
            }
        }

        @media (max-width: 480px) {
            .card {
                padding: 15px;
            }

            h2 {
                font-size: 18px;
                margin-bottom: 10px;
            }

            input[type="text"], input[type="password"] {
                font-size: 14px;
                padding: 10px;
            }

            button {
                padding: 12px;
                font-size: 14px;
            }

            .alert {
                font-size: 14px;
                padding: 8px;
            }
        }

        /* Animation */
        .card {
            animation: fadeIn 1s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card">
            <p class="text-center">
                <a href="../"><img src="../images/logo.png" alt="" style="max-width:100px; max-height:50px;"></a>
            </p>
            <h2 class="text-center">
                <a href="../">
                    <i class="fas fa-home">&nbsp; &nbsp;</i>
                </a>
                Admin Login
            </h2>

            <?php if (!empty($error)) { echo "<div class='alert alert-danger'>$error</div>"; } ?>

            <form method="POST" action="index.php">
                <div class="mb-3">
                    <label class="form-label">Username:</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password:</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
            <div>
                <a href="forgot_password.php">Forgot Password?</a>
            </div>
        </div>
    </div>
</body>
</html>

