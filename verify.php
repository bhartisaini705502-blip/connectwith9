<?php
require_once 'db.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    try {
        // Check if token is valid and not expired
        $stmt = $pdo->prepare("SELECT id FROM users WHERE verification_token = ? AND is_verified = 0 AND verification_token_expiry > NOW()");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Update user as verified
            $updateStmt = $pdo->prepare("UPDATE users SET is_verified = 1, status = 'active', verification_token = NULL, verification_token_expiry = NULL WHERE id = ?");
            $updateStmt->execute([$user['id']]);

            echo "<div style='text-align: center; padding: 50px;'>
                    <h2 style='color: green;'>✅ Your account has been verified!</h2>
                    <p>You can now log in to your account.</p>
                    <a href='login.php' style='padding: 10px 20px; background: blue; color: white; text-decoration: none; border-radius: 5px;'>Login Here</a>
                  </div>";
        } else {
            echo "<div style='text-align: center; padding: 50px;'>
                    <h2 style='color: red;'>❌ Invalid or expired token.</h2>
                    <p>Please request a new verification link.</p>
                    <a href='register.php' style='padding: 10px 20px; background: orange; color: white; text-decoration: none; border-radius: 5px;'>Register Again</a>
                  </div>";
        }
    } catch (PDOException $e) {
        echo "<div style='text-align: center; padding: 50px; color: red;'>
                <h2>Error!</h2>
                <p>Something went wrong. Please try again later.</p>
              </div>";
    }
} else {
    echo "<div style='text-align: center; padding: 50px;'>
            <h2 style='color: red;'>⚠ No token provided.</h2>
            <p>Invalid request.</p>
          </div>";
}
?>
