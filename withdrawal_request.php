<?php
session_start();
require_once 'db.php';
require_once 'functions.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user'];
$balance = getWalletBalance($user_id);
$message = $error = '';

// Fetch user's last submitted withdrawal details
$stmt = $pdo->prepare("SELECT upi_id, bank_account, ifsc_code FROM withdrawal_requests WHERE user_id = ? ORDER BY request_date DESC LIMIT 1");
$stmt->execute([$user_id]);
$withdrawalDetails = $stmt->fetch(PDO::FETCH_ASSOC);

$upi_id = $withdrawalDetails['upi_id'] ?? '';
$bank_account = $withdrawalDetails['bank_account'] ?? '';
$ifsc_code = $withdrawalDetails['ifsc_code'] ?? '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = floatval($_POST['amount']);

    // Validate withdrawal amount
    if ($amount < 100) {
        $error = "Minimum withdrawal amount is Rs.100.";
    } elseif ($amount > $balance) {
        $error = "Insufficient balance.";
    } else {
        // Use existing details if available
        $new_upi_id = (!empty($upi_id)) ? $upi_id : sanitize($_POST['upi_id']);
        $new_bank_account = (!empty($bank_account)) ? $bank_account : sanitize($_POST['bank_account']);
        $new_ifsc_code = (!empty($ifsc_code)) ? $ifsc_code : sanitize($_POST['ifsc_code']);

        if (!$error) {
            $stmt = $pdo->prepare("INSERT INTO withdrawal_requests (user_id, amount, upi_id, bank_account, ifsc_code, status, request_date) VALUES (?, ?, ?, ?, ?, 'pending', NOW())");
            $stmt->execute([$user_id, $amount, $new_upi_id, $new_bank_account, $new_ifsc_code]);
            $message = "Withdrawal request submitted successfully.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Withdrawal Request</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>

    <style>
        
    </style>
</head>
<body>

<!-- Include Sidebar -->
<?php include("sidebar.php"); ?>

<!-- Toggle Button for Sidebar -->
<button class="toggle-btn" onclick="toggleSidebar()">☰</button>

<!-- Main Content -->
<div class="main-content">
    
    <div class="container">
   
        <div class="withdrawal-container">
            <h2 class="text-center">Request Withdrawal</h2>
            <p class="text-center">Your current wallet balance: <strong>Rs. <?php echo $balance; ?></strong></p>
            <?php if (!empty($message)) { echo "<div class='alert alert-success'>$message</div>"; } ?>
            <?php if (!empty($error)) { echo "<div class='alert alert-danger'>$error</div>"; } ?>

           

            <form method="POST" action="withdrawal_request.php">
                <div class="mb-3">
                    <label class="form-label">Enter Amount to Withdraw:</label>
                    <input type="number" name="amount" class="form-control" min="100" max="<?php echo $balance; ?>" required>
                </div>

                <?php if (empty($upi_id)) { ?>
                    <div class="mb-3">
                        <label class="form-label">Enter your UPI ID:</label>
                        <input type="text" name="upi_id" class="form-control" required>
                    </div>
                <?php } else { ?>
                    <p><strong>UPI ID:</strong> <?php echo htmlspecialchars($upi_id); ?></p>
                <?php } ?>

                <?php if (empty($bank_account)) { ?>
                    <div class="mb-3">
                        <label class="form-label">Bank Account Number:</label>
                        <input type="text" name="bank_account" class="form-control" required>
                    </div>
                <?php } else { ?>
                    <p><strong>Bank Account:</strong> <?php echo htmlspecialchars($bank_account); ?></p>
                <?php } ?>

                <?php if (empty($ifsc_code)) { ?>
                    <div class="mb-3">
                        <label class="form-label">IFSC Code:</label>
                        <input type="text" name="ifsc_code" class="form-control" required>
                    </div>
                <?php } else { ?>
                    <p><strong>IFSC Code:</strong> <?php echo htmlspecialchars($ifsc_code); ?></p>
                <?php } ?>

                <?php if (!empty($upi_id) || !empty($bank_account) || !empty($ifsc_code)) { ?>
                    <!-- <div style="float:left; margin-top: 5px; " > -->
                        <a href="update_bank_details.php" >Update Bank Details</a>
                    <!-- </div> -->
                     <br>
                <?php } ?>
                <br>

                <button type="submit" class="btn btn-success ">Request Withdrawal</button>
            </form>
            
            
                
        </div>
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
