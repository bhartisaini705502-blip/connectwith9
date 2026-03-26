<?php
session_start();
require_once 'db.php';
require_once 'functions.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user'];
$message = $error = '';

// Fetch latest details
$stmt = $pdo->prepare("SELECT upi_id, bank_account, ifsc_code FROM withdrawal_requests WHERE user_id = ? ORDER BY request_date DESC LIMIT 1");
$stmt->execute([$user_id]);
$details = $stmt->fetch(PDO::FETCH_ASSOC);

$upi_id = $details['upi_id'] ?? '';
$bank_account = $details['bank_account'] ?? '';
$ifsc_code = $details['ifsc_code'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_upi = sanitize($_POST['upi_id']);
    $new_bank = sanitize($_POST['bank_account']);
    $new_ifsc = sanitize($_POST['ifsc_code']);

    if (empty($new_upi) || empty($new_bank) || empty($new_ifsc)) {
        $error = "All fields are required.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO withdrawal_requests (user_id, amount, upi_id, bank_account, ifsc_code, status, request_date) VALUES (?, 0, ?, ?, ?, 'updated', NOW())");
        $stmt->execute([$user_id, $new_upi, $new_bank, $new_ifsc]);
        $message = "Bank details updated successfully.";
        $upi_id = $new_upi;
        $bank_account = $new_bank;
        $ifsc_code = $new_ifsc;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Bank Details</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>

    <style>
        .form-container {
            max-width: 600px;
            margin: auto;
        }

        .card {
            border-radius: 15px;
        }

        .btn {
            min-width: 120px;
        }

        @media (max-width: 576px) {
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<?php include("sidebar.php"); ?>
<button class="toggle-btn" onclick="toggleSidebar()">☰</button>

<div class="main-content">
    <div class>
        <div class="">
            <div class="card shadow-sm p-4">
                <h3 class="text-center mb-4">Update Bank Details</h3>

                <?php if ($message): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php elseif ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">UPI ID</label>
                        <input type="text" name="upi_id" class="form-control" value="<?= htmlspecialchars($upi_id) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Bank Account Number</label>
                        <input type="text" name="bank_account" class="form-control" value="<?= htmlspecialchars($bank_account) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">IFSC Code</label>
                        <input type="text" name="ifsc_code" class="form-control" value="<?= htmlspecialchars($ifsc_code) ?>" required>
                    </div>

                    <div class="d-flex flex-column flex-md-row justify-content-between gap-2">
                        <button type="submit" class="btn btn-primary">Update Details</button>
                        <a href="withdrawal_request.php" class="btn btn-secondary">Back</a>
                        
                    </div>
                    <br>
                </form>
            </div>
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
