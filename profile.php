<?php
// profile.php
session_start();


define('DB_HOST', 'localhost');
define('DB_NAME', 'u647904474_microjobs');  // Replace with your database name
define('DB_USER', 'u647904474_microjobs');  // Replace with your DB username
define('DB_PASS', 'TechTrick@1234#');  // Replace with your DB password


try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    // Set PDO to throw exceptions for debugging
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Fetch user details
$stmt = $pdo->prepare("SELECT id, username, email, phone_no, profile_picture, bio, profession, status, is_verified, created_at FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$user) {
    echo "User not found.";
    exit();
}

// Fetch all interests
$stmt = $pdo->query("SELECT id, name FROM interests");
$allInterests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch user's selected interests
$stmt = $pdo->prepare("SELECT interest_id FROM user_interests WHERE user_id = ?");
$stmt->execute([$_SESSION['user']]);
$userInterests = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Handle form submission for interest update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newInterests = isset($_POST['interests']) ? $_POST['interests'] : [];

    // Remove old interests
    $stmt = $pdo->prepare("DELETE FROM user_interests WHERE user_id = ?");
    $stmt->execute([$_SESSION['user']]);

    // Insert new interests
    $stmt = $pdo->prepare("INSERT INTO user_interests (user_id, interest_id, selected_at) VALUES (?, ?, NOW())");
    foreach ($newInterests as $interest_id) {
        $stmt->execute([$_SESSION['user'], $interest_id]);
    }

    $message = "Interests updated successfully.";
    $userInterests = $newInterests; // Update local variable for checked interests
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>

    <style>
.profile-container {
    max-width: 100%;
    margin: 20px auto;
    background: #ffffff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
}

h2 {
    font-size: 22px;
    font-weight: bold;
    margin-bottom: 15px;
}

.text-primary {
    color: #007bff;
}

.text-center {
    text-align: center;
}

.text-end {
    text-align: right;
}

.profile-pic {
    display: flex;
    justify-content: center;
    margin: 15px 0;
}

.profile-pic img {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    border: 3px solid #007bff;
    object-fit: cover;
}

.profile-details {
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 8px;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    padding: 10px;
    border-bottom: 1px solid #ddd;
}

.detail-title {
    font-weight: bold;
    color: #555;
}

.detail-value {
    font-weight: normal;
    color: #333;
}

.text-success {
    color: #28a745;
}

.text-danger {
    color: #dc3545;
}

/* Interests */
.interest-form {
    margin-top: 20px;
}

.interest-container {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.interest-item {
    background: #f1f1f1;
    padding: 8px 12px;
    border-radius: 6px;
    cursor: pointer;
}

.interest-item input {
    margin-right: 5px;
}

.btn {
    padding: 10px;
    font-size: 14px;
    border-radius: 5px;
    text-align: center;
    display: block;
    /* width: 100%; */
    max-width: 200px;
}

.btn-success {
    background: #28a745;
    color: white;
}

.btn-success:hover {
    background: #218838;
}

.btn-primary {
    background: #007bff;
    color: white;
}

.btn-primary:hover {
    background: #0056b3;
}

/* Responsive */
@media (max-width: 768px) {
    .profile-container {
        width: 90%;
    }

    .text-end {
        text-align: center;
        margin-top: 10px;
        width: 100% !important;
    }

    .btn {
        width: 100%;
        max-width: 100%;
    }

    .detail-item {
        flex-direction: column;
        align-items: start;
    }
    .interest-item {
        width: 100%;
    }
}

@media (max-width: 480px) {
    .profile-container {
        width: 95%;
        padding: 15px;
    }

    .profile-pic img {
        width: 150px;
        height: 150px;
    }

}


    </style>

</head>

<body>

    <!-- Include Sidebar -->
    <?php include("sidebar.php"); ?>

    <!-- Toggle Button -->
    <button class="toggle-btn" onclick="toggleSidebar()">☰</button>

    <!-- Main Content -->
    <div class="main-content">

    <div class="profile-container">
    <h2 class="text-center text-primary"><?php echo htmlspecialchars($user['username']); ?>'s Profile</h2>

    <!-- Improve Profile Button -->
    <div class="text-end mt-3" style="text-align: center !important;">
        <a href="improve_profile.php" class="btn btn-success">Improve Profile</a>
        <br>
    </div>

    
    <!-- Profile Picture -->
<div class="profile-pic">
    <?php if (!empty($user['profile_picture'])) { ?>
        <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture">
    <?php } else { ?>
        <img src="images/default-profile.png" alt="Default Profile Picture">
    <?php } ?>
</div>


    <!-- User Details -->
    <div class="profile-details">
        <div class="detail-item">
            <span class="detail-title">Email:</span>
            <span class="detail-value"><?php echo htmlspecialchars($user['email']); ?></span>
        </div>
        <div class="detail-item">
            <span class="detail-title">Phone No:</span>
            <span class="detail-value"><?php echo htmlspecialchars($user['phone_no']); ?></span>
        </div>
        <div class="detail-item">
            <span class="detail-title">Bio:</span>
            <span class="detail-value"><?php echo htmlspecialchars($user['bio']); ?></span>
        </div>
        <div class="detail-item">
            <span class="detail-title">Profession:</span>
            <span class="detail-value"><?php echo htmlspecialchars($user['profession']); ?></span>
        </div>
        <div class="detail-item">
            <span class="detail-title">Status:</span>
            <span class="detail-value <?php echo ($user['status'] == 'active') ? 'text-success' : 'text-danger'; ?>">
                <?php echo ($user['status'] == 'active') ? "Active" : "Inactive"; ?>
            </span>
        </div>
        <div class="detail-item">
            <span class="detail-title">Verified:</span>
            <span class="detail-value <?php echo ($user['is_verified']) ? 'text-success' : 'text-danger'; ?>">
                <?php echo ($user['is_verified']) ? "Verified" : "Not Verified"; ?>
            </span>
        </div>
        <div class="detail-item">
            <span class="detail-title">Member Since:</span>
            <span class="detail-value"><?php echo date("d M Y, h:i A", strtotime($user['created_at'])); ?></span>
        </div>
    </div>

    <!-- Interest Selection Form -->
    <form method="POST" action="profile.php" class="interest-form">
        <h4 class="mt-4">Select Your Interests:</h4>
        <div class="interest-container">
            <?php foreach ($allInterests as $interest) { ?>
                <label class="interest-item">
                    <input type="checkbox" name="interests[]" value="<?php echo $interest['id']; ?>" 
                    <?php echo in_array($interest['id'], $userInterests) ? 'checked' : ''; ?>>
                    <?php echo htmlspecialchars($interest['name']); ?>
                </label>
            <?php } ?>
        </div>
        <button type="submit" class="btn btn-primary w-100 mt-3">Update Interests</button>
    </form>

    <?php if (isset($message)) {
        echo "<div class='alert alert-success mt-3'>$message</div>";
    } ?>
</div>

    </div>

    <script>
        function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("active");
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>