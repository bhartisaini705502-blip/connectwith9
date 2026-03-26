<?php
session_start();
require_once 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user'];

// Fetch existing detailed profile if available
$stmt = $pdo->prepare("SELECT * FROM detailed_profile WHERE user_id = ?");
$stmt->execute([$user_id]);
$detailedProfile = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $education = $_POST['education'] ?? '';
    $experience = $_POST['experience'] ?? '';
    $skills = $_POST['skills'] ?? '';
    $certifications = $_POST['certifications'] ?? '';
    $resumePath = $detailedProfile['resume_path'] ?? null;

    // Handle Resume Upload
    if (!empty($_FILES['resume']['name'])) {
        $uploadDir = 'uploads/resumes/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $resumeFileName = time() . "_" . basename($_FILES['resume']['name']);
        $resumeTargetPath = $uploadDir . $resumeFileName;

        if (move_uploaded_file($_FILES['resume']['tmp_name'], $resumeTargetPath)) {
            $resumePath = $resumeTargetPath;
        }
    }

    if ($detailedProfile) {
        // Update existing record
        $stmt = $pdo->prepare("UPDATE detailed_profile SET education=?, experience=?, skills=?, certifications=?, resume_path=? WHERE user_id=?");
        $stmt->execute([$education, $experience, $skills, $certifications, $resumePath, $user_id]);
    } else {
        // Insert new record
        $stmt = $pdo->prepare("INSERT INTO detailed_profile (user_id, education, experience, skills, certifications, resume_path) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $education, $experience, $skills, $certifications, $resumePath]);
    }

    $message = "Profile updated successfully.";
    // Fetch updated details
    $stmt = $pdo->prepare("SELECT * FROM detailed_profile WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $detailedProfile = $stmt->fetch(PDO::FETCH_ASSOC);
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
       body {
            background-color: #f8f9fa;
        }
        .profile-container {
            max-width: 700px;
            margin: 50px auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>

<!-- Include Sidebar -->
<?php include("sidebar.php"); ?>

<!-- Toggle Button -->
<button class="toggle-btn" onclick="toggleSidebar()">☰</button>


    <div class="main-content">
    <div class="profile-container">
        <h2 class="text-center text-primary">Improve Your Profile</h2>

        <?php if (isset($message)) { echo "<div class='alert alert-success'>$message</div>"; } ?>

        <form method="POST" action="improve_profile.php" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Education:</label>
                <textarea class="form-control" name="education" required><?php echo htmlspecialchars($detailedProfile['education'] ?? ''); ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Experience:</label>
                <textarea class="form-control" name="experience" required><?php echo htmlspecialchars($detailedProfile['experience'] ?? ''); ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Skills:</label>
                <textarea class="form-control" name="skills" required><?php echo htmlspecialchars($detailedProfile['skills'] ?? ''); ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Certifications:</label>
                <textarea class="form-control" name="certifications"><?php echo htmlspecialchars($detailedProfile['certifications'] ?? ''); ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Upload Resume (PDF only):</label>
                <input type="file" name="resume" class="form-control" accept=".pdf">
            </div>

            <?php if (!empty($detailedProfile['resume_path'])): ?>
                <p>Current Resume: <a href="<?php echo htmlspecialchars($detailedProfile['resume_path']); ?>" target="_blank">View Resume</a></p>
            <?php endif; ?>

            <button type="submit" class="btn btn-success w-100">Update Profile</button>
        </form>

        <p class="mt-3 text-center"><a href="profile.php" class="btn btn-primary">Back to Profile</a></p>
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