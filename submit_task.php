<?php
// submit_task.php

session_start();
require_once 'db.php';
require_once 'functions.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['task_id'])) {
    die("Task ID not provided.");
}

$task_id = intval($_GET['task_id']);
$user_id = $_SESSION['user'];
$message = $error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $submission_urls = isset($_POST['submission_urls']) ? array_filter($_POST['submission_urls']) : [];
    $uploadedFiles = [];

    try {
        $pdo->beginTransaction();

        // Insert submission record into the `submissions` table
        $details = isset($_POST['details']) ? $_POST['details'] : '';
        $stmt = $pdo->prepare("INSERT INTO submissions (user_id, task_id, submitted_at, details, status) VALUES (?, ?, NOW(), ?, 'pending')");
        $stmt->execute([$user_id, $task_id, $details]);

        $submission_id = $pdo->lastInsertId(); // Get the inserted submission ID

        if (!empty($submission_urls)) {
            $stmt = $pdo->prepare("INSERT INTO submission_urls (submission_id, url) VALUES (?, ?)");
            $first_url = null;

            foreach ($submission_urls as $url) {
                $clean_url = filter_var($url, FILTER_VALIDATE_URL);
                if ($clean_url) {
                    $stmt->execute([$submission_id, $clean_url]);
                    if (!$first_url) {
                        $first_url = $clean_url; // Store the first valid URL
                    }
                }
            }

            // Update the `submissions` table with the first URL
            if ($first_url) {
                $stmt = $pdo->prepare("UPDATE submissions SET submission_url = ? WHERE id = ?");
                $stmt->execute([$first_url, $submission_id]);
            }
        }

        // Process multiple file uploads
        if (!empty($_FILES['screenshots']['name'][0])) {
            $uploadDir = 'uploads/screenshots/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            foreach ($_FILES['screenshots']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['screenshots']['error'][$key] == 0) {
                    $filename = time() . "_" . basename($_FILES['screenshots']['name'][$key]);
                    $targetFile = $uploadDir . $filename;

                    if (move_uploaded_file($tmp_name, $targetFile)) {
                        $uploadedFiles[] = $targetFile;

                        // Insert file path into `submission_files` table
                        $stmt = $pdo->prepare("INSERT INTO submission_files (submission_id, file_path) VALUES (?, ?)");
                        $stmt->execute([$submission_id, $targetFile]);

                        // Update submission table with the first screenshot path
                        if (count($uploadedFiles) == 1) {
                            $stmt = $pdo->prepare("UPDATE submissions SET screenshot_path = ? WHERE id = ?");
                            $stmt->execute([$targetFile, $submission_id]);
                        }
                    }
                }
            }
        }

        // ✅ Increment current_submissions in the tasks table
        $stmt = $pdo->prepare("UPDATE tasks SET current_submissions = current_submissions + 1 WHERE id = ?");
        $stmt->execute([$task_id]);

        $pdo->commit();
        $message = "Task submitted successfully. Await admin verification.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error submitting task: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Details</title>
    <link rel="stylesheet" href="css/style.css"> <!-- Include global styles -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <style>
        .submit-container {
            padding: 25px;
            font-size: 16px;
            /* Default font size for normal screens */
            max-width: 800px;
            margin: 0 auto;
            background-color: #ffffff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            transition: 0.3s ease-in-out;
            margin-top: 30px;
        }

        .submit-container:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        h2 {
            font-size: 1.5rem;
            /* Adjust heading size */
            color: #333;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .alert {
            margin-bottom: 20px;
            font-size: 14px;
        }

        .form-label {
            font-weight: bold;
            font-size: 14px;
            color: #333;
        }

        .url-input-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }

        .url-input-group input {
            width: 100%;
            max-width: 100%;
        }

        .btn {
            padding: 12px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            /* width: 50%; */
            margin-top: 10px;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .btn-success {
            background-color: #28a745;
            color: white;
        }

        .btn-success:hover {
            background-color: #218838;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .mt-3 {
            margin-top: 1rem;
        }

        .text-center {
            text-align: center;
        }

        .w-100 {
            width: 100%;
        }

        /* Responsive Design for Mobile View */
        @media (max-width: 768px) {
            .submit-container {

                padding: 15px;
                font-size: 14px;
                /* Adjust font size for better readability on mobile */
            }

            .new1 {
                margin-top: 200px !important;
            }


            h2 {
                font-size: 1.3rem;
            }

            .url-input-group input {
                font-size: 14px;
            }

            .btn {
                padding: 12px;
                font-size: 14px;
                width: 50%;
            }

            .alert {
                font-size: 12px;
            }

            .url-input-group {
                gap: 8px;
            }
        }

        @media (max-width: 480px) {
            .submit-container {
                padding: 40px;
                margin-top: 200px !important;
                font-size: 12px;
                /* Smaller font size for very small screens */
            }

            h2 {
                font-size: 1.2rem;
            }

            .url-input-group input {
                font-size: 12px;
                padding: 8px;
            }

            .btn {
                padding: 12px;
                font-size: 14px;
            }

            .alert {
                font-size: 12px;
            }

            .url-input-group {
                flex-direction: column;
                gap: 10px;
            }
        }

        .mob {
            width: 95%;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);


        }



        .mob:hover {
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            transition: box-shadow 0.3s ease-in-out;
        }

        @media (max-width: 768px) {
            .mob {
                width: 100%;
                font-size: x-large;
            }
        }
    </style>
</head>

<body>

    <!-- Include Sidebar -->
    <?php include("sidebar.php"); ?>

    <!-- Toggle Button -->
    <button class="toggle-btn" onclick="toggleSidebar()">☰</button>


    <div class="main-content">
        <div style="padding:30px ; font-size: xx-large; background-color:#ffffff8c;
    border-radius: 20px; " class="mob">

            <h1 class="text-center mb-4"><b>Submit Task</b></h1>

            <?php if (!empty($message)) {
                echo "<div class='alert alert-success'>$message</div>";
            } ?>
            <?php if (!empty($error)) {
                echo "<div class='alert alert-danger'>$error</div>";
            } ?>

            <form method="POST" action="submit_task.php?task_id=<?php echo $task_id; ?>" enctype="multipart/form-data">
                <!-- URLs Submission Section -->
                <div class="mb-3">
                    <label class="form-label" style="font-size: x-large;">Submission URLs:</label>
                    <div id="url-fields">
                        <div class="url-input-group mb-2">
                            <input type="url" name="submission_urls[]" class="form-control" placeholder="Enter URL">
                        </div>
                    </div>
                    <button type="button" class="btn btn-success btn-sm mt-2" onclick="addURLField()">Add More
                        URLs</button>
                </div>

                <div class="mb-3">
                    <label for="details">Task Details:</label>
                    <textarea name="details" class="form-control" placeholder="Enter additional details..."
                        rows="4"></textarea>
                </div>

                <!-- Screenshots Upload Section -->
                <div class="mb-3">
                    <label class="form-label" style="font-size: x-large;">Upload Screenshots (Multiple Allowed):</label>
                    <input type="file" name="screenshots[]" class="form-control" multiple>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-success w-100">Submit Task</button>
            </form>

            <!-- Back Button -->
            <p class="mt-3 text-center">
                <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
            </p>
        </div>

    </div>

    <script>
        function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("active");
        }

        function addURLField() {
            let div = document.createElement("div");
            div.classList.add("mb-3", "url-input-group");
            div.innerHTML = `
                <input type="url" name="submission_urls[]" class="form-control mb-2">
                <button type="button" class="btn btn-danger btn-sm" onclick="removeURLField(this)">Remove</button>
            `;
            document.getElementById("url-fields").appendChild(div);
        }

        function removeURLField(button) {
            button.parentElement.remove();
        }

    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>