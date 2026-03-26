<?php
session_start();
require_once 'db.php'; // your PDO connection
require_once 'functions.php'; // if you use helper functions

// Redirect to login if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user'];

// Fetch user data
$stmt = $pdo->prepare("SELECT username AS name, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle form submission
$success = $error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO user_queries (user_id, name, email, subject, message) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$user_id, $name, $email, $subject, $message])) {
            $success = "Your query has been submitted successfully!";
        } else {
            $error = "Something went wrong. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Help & Support</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/style.css"> <!-- Your custom styles -->
    <link rel="stylesheet" href="index.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
    background-color: #f8f9fa;
    font-family: 'Segoe UI', sans-serif;
}

.toggle-btn {
    position: fixed;
    top: 15px;
    left: 15px;
    font-size: 1.5rem;
    z-index: 1001;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 8px;
    padding: 6px 14px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
}

.toggle-btn:hover {
    background-color: #0056b3;
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
}

@media (min-width: 768px) {
    .toggle-btn {
        display: none;
    }
}

.main-content {
    padding: 30px 20px;
}

.form-wrapper {
    max-width: 700px;
    margin: 40px auto;
    background: #fff;
    padding: 30px;
    border-radius: 16px;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
}

.form-wrapper h2 {
    font-weight: 600;
    margin-bottom: 25px;
}

.form-control {
    border-radius: 10px;
    box-shadow: none;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.1);
}

.btn-primary {
    border-radius: 10px;
    padding: 10px;
    font-size: 1.1rem;
    font-weight: 500;
    background-color: #007bff;
    border: none;
    transition: background-color 0.3s ease, box-shadow 0.3s ease;
}

.btn-primary:hover {
    background-color: #0056b3;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.alert {
    border-radius: 10px;
    padding: 12px 16px;
    font-size: 0.95rem;
}

    </style>
</head>
<body>

<?php include("sidebar.php"); ?>
<button class="toggle-btn" onclick="toggleSidebar()">☰</button>

<div class="main-content " >
    <div class="form-wrapper bg-white p-4 rounded shadow-sm">
        <h2 class="text-center mb-4">Need Help? Contact Us</h2>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Subject</label>
                <input type="text" name="subject" class="form-control" placeholder="Enter subject" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Message</label>
                <textarea name="message" rows="5" class="form-control" placeholder="Describe your issue..." required></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-100">Submit Query</button>
        </form>
    </div>
</div>

<script>
    function toggleSidebar() {
        document.getElementById("sidebar").classList.toggle("active");
    }
</script>

</body>
</html>
