<?php
// functions.php
// Common functions for user registration, login, OTP generation, etc.

// session_start();
require_once 'db.php';

// Redirect to a new URL
function redirect($url) {
    header("Location: $url");
    exit();
}

// Sanitize user input
function sanitize($data) {
    return htmlspecialchars(trim($data));
}

// Generate a random 6-digit OTP
function generateOTP() {
    return rand(100000, 999999);
}

// Register a new user (stores user and selected interests)
function registerUser($username, $email, $password, $interests) {
    global $pdo;
    // Check if the user already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        return "User already exists.";
    }
    // Hash the password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    // Insert the new user (set status to 'inactive' until OTP verification)
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, status, created_at) VALUES (?, ?, ?, 'inactive', NOW())");
    $stmt->execute([$username, $email, $password_hash]);
    $user_id = $pdo->lastInsertId();
    
    // Insert user interests (if any were selected)
    if (!empty($interests)) {
        $stmt = $pdo->prepare("INSERT INTO user_interests (user_id, interest_id, selected_at) VALUES (?, ?, NOW())");
        foreach ($interests as $interest_id) {
            $stmt->execute([$user_id, $interest_id]);
        }
    }
    
    // Generate and store an OTP (valid for 10 minutes)
    $otp = generateOTP();
    $stmt = $pdo->prepare("INSERT INTO otps (user_id, otp_code, created_at, expires_at, used) VALUES (?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 10 MINUTE), 0)");
    $stmt->execute([$user_id, $otp]);
    
    // For demonstration, store OTP and user_id in session (normally, you would email the OTP)
    $_SESSION['otp'] = $otp;
    $_SESSION['user_id'] = $user_id;
    
    return true;
}

// Verify the OTP entered by the user
function verifyOTP($user_id, $entered_otp) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM otps WHERE user_id = ? AND used = 0 ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $otp_record = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($otp_record) {
        // Check if OTP has expired
        if (strtotime($otp_record['expires_at']) < time()) {
            return "OTP expired.";
        }
        if ($otp_record['otp_code'] == $entered_otp) {
            // Mark OTP as used
            $stmt = $pdo->prepare("UPDATE otps SET used = 1 WHERE id = ?");
            $stmt->execute([$otp_record['id']]);
            // Activate the user account
            $stmt = $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?");
            $stmt->execute([$user_id]);
            $_SESSION['user'] = $user_id;
            return true;
        } else {
            return "Invalid OTP.";
        }
    }
    return "OTP not found.";
}

// Log in a user (checks status and password)
function loginUser($email, $password) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        if ($user['status'] != 'active') {
            return "Please verify your email first.";
        }
        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['user'] = $user['id'];
            return true;
        } else {
            return "Incorrect password.";
        }
    }
    return "User not found.";
}

// Check if the user is logged in
function isLoggedIn() {
    return isset($_SESSION['user']);
}

// Retrieve user details
function getUser($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get list of all available interests
function getInterests() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM interests");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get the interests selected by the user
function getUserInterests($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT interest_id FROM user_interests WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Get tasks filtered by the user's selected interests
function getTasksByInterests($user_id) {
    global $pdo;
    $user_interests = getUserInterests($user_id);
    if (empty($user_interests)) {
        // If no interests are selected, return all active tasks
        $stmt = $pdo->query("SELECT * FROM tasks WHERE status = 'active'");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    $in  = str_repeat('?,', count($user_interests) - 1) . '?';
    // Assume each task has an 'interest_id' field to match the user’s interests
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE status = 'active' AND interest_id IN ($in)");
    $stmt->execute($user_interests);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Calculate the user's wallet balance from wallet transactions
function getWalletBalance($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT SUM(CASE WHEN transaction_type = 'credit' THEN amount ELSE -amount END) AS balance FROM wallet_transactions WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row['balance'] ? $row['balance'] : 0;
}
?>
