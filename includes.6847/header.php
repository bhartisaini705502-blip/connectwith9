<header class="bg-light pt-4">
    <!-- Desktop Navbar (Rounded) -->
    <!-- <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm  mx-auto px-3 d-none d-lg-flex" style="width: 90%; z-index: 999 ; border-radius:8px;"> -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm rounded-pill mx-auto px-3 d-none d-lg-flex" style="width: 90%; z-index: 999;">
        <div class="container-fluid">
            <!-- Logo -->
            <a href="index.php" class="navbar-brand fw-bold text-primary">LOGO</a>

            <!-- Navbar Items -->
            <div class="collapse navbar-collapse justify-content-center">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link fw-bold text-dark" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark fw-bold " href="about_us.php">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark fw-bold " href="contact.php">Contact Us</a>
                    </li>
                </ul>
            </div>

            <!-- Right-Aligned Buttons -->
            <div class="d-flex gap-2">
                <a href="login.php" class="btn btn-primary rounded-pill px-4">Log In</a>
                <a href="register.php" class="btn btn-outline-primary rounded-pill px-4">Register</a>
            </div>
        </div>
    </nav>

    <!-- Mobile & Tablet Navbar (Full Width, No Rounded Corners) -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm d-lg-none w-100" style="z-index: 999; margin-top: -20px;">
        <div class="container-fluid">
            <!-- Logo -->
            <a href="index.php" class="navbar-brand fw-bold text-primary">LOGO</a>

            <!-- Mobile Toggle Button -->
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mobileNav" aria-controls="mobileNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Mobile Navigation Links -->
            <div class="collapse navbar-collapse" id="mobileNav">
                <ul class="navbar-nav mx-auto text-center">
                    <li class="nav-item">
                        <a class="nav-link active text-primary fw-bold" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark" href="about_us.php">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark" href="contact.php">Contact Us</a>
                    </li>
                </ul>

                <!-- Mobile Buttons (Stacked) -->
                <div class="text-center mt-3">
                    <a href="login.php" class="btn btn-primary rounded  mb-2">Log In</a>
                    <a href="register.php" class="btn btn-outline-primary rounded ">Register</a>
                </div>
            </div>
        </div>
    </nav>
</header>

<!-- Bootstrap 5 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

<!-- Bootstrap 5 JS (For Navbar Toggle on Mobile) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
