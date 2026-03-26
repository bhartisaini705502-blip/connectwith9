<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>About Us | ConnectWith9.com</title>

    <!-- Fonts & CSS -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="new.css" />
    <style>
        /* General improvements */
        body {
            font-family: 'Inter', sans-serif;
        }

        /* Card hover effect */
        .task-card:hover {
            transform: translateY(-5px);
            transition: transform 0.3s ease;
        }

        /* Make hero section image responsive */
        .hero-about {
            position: relative;
            overflow: hidden;
        }

        .hero-about .hero-bg,
        .hero-about .hero-overlay {
            background-size: cover;
            background-position: center;
        }

        /* Responsive image adjustments */
        @media (max-width: 768px) {
            #how-it-works img {
                width: 100%;
                height: auto;
            }
        }

        /* CTA button adjustments */
        #cta .btn {
            padding: 0.75rem 2rem;
            font-size: 1.1rem;
        }

        /* Reduce padding for smaller devices */
        @media (max-width: 576px) {
            .hero-about h1 {
                font-size: 1.75rem;
            }

            .hero-about p.lead {
                font-size: 1rem;
            }

            .card-body i {
                font-size: 1.5rem;
            }

            .card-body h6 {
                font-size: 0.95rem;
            }
        }

        /* Why Choose Us padding */
        #why-choose .p-4 {
            padding: 1.25rem !important;
        }
    </style>
</head>

<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm sticky-top animate__animated animate__fadeInDown">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <img src="images\logo3.png" alt="Company logo" height="50px">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link " href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link active" href="about.php">About Us</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php">How It Works</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php">Categories</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php">Join as Freelancer</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php">FAQs</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php">Contact Us</a></li>
                    <li class="nav-item"><a class="btn btn-success ms-2" href="#">Login / Register</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- ✅ Hero Section -->
    <section class="hero-about text-white py-5" style="background-color: rgba(6, 6, 6, 0.6); ">
        <div class="container">
            <div class="hero-bg position-absolute top-0 start-0 w-100 h-100"
                style="background-image: url('images/337109.jpg'); background-size: cover; background-position: center; z-index: -2;">
            </div>
            <div class="hero-overlay position-absolute top-0 start-0 w-100 h-100"
                style="background: rgba(0, 0, 0, 0.6); z-index: -1;">
            </div>
            <div class="row align-items-center">
                <div class="col-md-6 animate__animated animate__fadeInLeft">
                    <h1 class="display-5 fw-bold"> Welcome to ConnectWith9.com </h1>
                    <p class="lead mt-3">Where flexibility meets opportunity. Learn how we're helping thousands work
                        with freedom.</p>
                    <!-- <a href="#about-intro" class="btn btn-light btn-lg mt-3">Learn More</a> -->
                </div>
                <div class="col-md-6 text-center animate__animated animate__fadeInRight">

                </div>
            </div>
        </div>
    </section>

    <!-- ✅ About Introduction -->
    <section id="about-intro" class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-4 animate__animated animate__fadeInDown"><strong>About ConnectWith9.com</strong>
            </h2>
            <p class="lead text-center animate__animated animate__fadeInUp">
                ConnectWith9.com is India’s fastest-growing task-based freelancing platform. We’re on a mission to
                empower individuals to earn flexibly—anytime, anywhere—with meaningful micro jobs and freelance tasks
                tailored to modern lifestyles.
            </p>
        </div>
    </section>

    <!-- ✅ How It Works -->
    <section id="how-it-works" class="py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 animate__animated animate__fadeInLeft">
                    <h2 class="fw-bold mb-3">🎯 How It Works</h2>
                    <p>
                        Sign up, explore a curated list of tasks that match your skills and interests, and complete them
                        at your convenience. Each task is reviewed by our backend team, and you get paid for every
                        approved submission—simple, secure, and sustainable.
                    </p>
                    <ul>
                        <li>🔍 Browse tasks based on category and interest</li>
                        <li>⏱️ Work at your pace—no pressure, no deadlines</li>
                        <li>💸 Get paid for approved tasks directly to your wallet</li>
                    </ul>
                </div>
                <div class="col-md-6 animate__animated animate__fadeInRight text-center" style="overflow:hidden;">
                    <img src="images/about.png" alt="Work Flow" height="500px" width="600px">
                </div>
            </div>
        </div>
    </section>
    <!-- ✅ Task Types - Modern Design -->
    <section id="task-types" class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center fw-bold mb-4 animate__animated animate__fadeInUp">🛠️ Explore a World of Tasks</h2>
            <p class="text-center mb-5 animate__animated animate__fadeInUp">
                <strong>From tech to teaching, marketing to mobile apps—there’s something for everyone.</strong>
            </p>
            <div class="row g-4 row-cols-2 row-cols-md-3 row-cols-lg-4 animate__animated animate__fadeInUp">
                <!-- Task Item -->
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm text-center task-card hover-shadow">
                        <div class="card-body">
                            <i class="fas fa-bullhorn fa-2x mb-3 text-primary"></i>
                            <h6 class="fw-bold">Digital Marketing</h6>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="card h-100 border-0 shadow-sm text-center task-card hover-shadow">
                        <div class="card-body">
                            <i class="fas fa-phone-volume fa-2x mb-3 text-success"></i>
                            <h6 class="fw-bold">Tele Sales</h6>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="card h-100 border-0 shadow-sm text-center task-card hover-shadow">
                        <div class="card-body">
                            <i class="fas fa-shield-alt fa-2x mb-3 text-danger"></i>
                            <h6 class="fw-bold">Insurance Services</h6>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="card h-100 border-0 shadow-sm text-center task-card hover-shadow">
                        <div class="card-body">
                            <i class="fas fa-box-open fa-2x mb-3 text-warning"></i>
                            <h6 class="fw-bold">Product Sales</h6>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="card h-100 border-0 shadow-sm text-center task-card hover-shadow">
                        <div class="card-body">
                            <i class="fas fa-user-friends fa-2x mb-3 text-info"></i>
                            <h6 class="fw-bold">Client Referrals</h6>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="card h-100 border-0 shadow-sm text-center task-card hover-shadow">
                        <div class="card-body">
                            <i class="fas fa-graduation-cap fa-2x mb-3 text-secondary"></i>
                            <h6 class="fw-bold">Education Admissions</h6>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="card h-100 border-0 shadow-sm text-center task-card hover-shadow">
                        <div class="card-body">
                            <i class="fas fa-comments-dollar fa-2x mb-3 text-primary"></i>
                            <h6 class="fw-bold">Consultancy</h6>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="card h-100 border-0 shadow-sm text-center task-card hover-shadow">
                        <div class="card-body">
                            <i class="fas fa-chalkboard-teacher fa-2x mb-3 text-success"></i>
                            <h6 class="fw-bold">Teaching & Tutoring</h6>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="card h-100 border-0 shadow-sm text-center task-card hover-shadow">
                        <div class="card-body">
                            <i class="fas fa-heart fa-2x mb-3 text-danger"></i>
                            <h6 class="fw-bold">Social Media Engagement</h6>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="card h-100 border-0 shadow-sm text-center task-card hover-shadow">
                        <div class="card-body">
                            <i class="fas fa-bullseye fa-2x mb-3 text-warning"></i>
                            <h6 class="fw-bold">Company Promotions</h6>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="card h-100 border-0 shadow-sm text-center task-card hover-shadow">
                        <div class="card-body">
                            <i class="fas fa-star fa-2x mb-3 text-info"></i>
                            <h6 class="fw-bold">Online Reviews</h6>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="card h-100 border-0 shadow-sm text-center task-card hover-shadow">
                        <div class="card-body">
                            <i class="fas fa-paint-brush fa-2x mb-3 text-secondary"></i>
                            <h6 class="fw-bold">Graphic Designing</h6>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="card h-100 border-0 shadow-sm text-center task-card hover-shadow">
                        <div class="card-body">
                            <i class="fas fa-code fa-2x mb-3 text-primary"></i>
                            <h6 class="fw-bold">Website Development</h6>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="card h-100 border-0 shadow-sm text-center task-card hover-shadow">
                        <div class="card-body">
                            <i class="fas fa-mobile-alt fa-2x mb-3 text-success"></i>
                            <h6 class="fw-bold">Mobile App Development</h6>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="card h-100 border-0 shadow-sm text-center task-card hover-shadow">
                        <div class="card-body">
                            <i class="fas fa-ellipsis-h fa-2x mb-3 text-dark"></i>
                            <h6 class="fw-bold">And many more…</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ✅ Why Choose Us -->
    <section id="why-choose" class="py-5">
        <div class="container py-5">
            <h2 class="text-center fw-bold mb-4 animate__animated animate__fadeInDown">🚀 Why Choose ConnectWith9.com?
            </h2>
            <div class="row g-4 animate__animated animate__fadeInUp">
                <div class="col-md-6 col-lg-4">
                    <div class="p-4 shadow rounded bg-white h-100">
                        <h5>📌 Flexible Work Hours</h5>
                        <p>Work when you want. We value your time and autonomy.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="p-4 shadow rounded bg-white h-100">
                        <h5>💼 Diverse Opportunities</h5>
                        <p>Choose from a variety of task categories to match your skills.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="p-4 shadow rounded bg-white h-100">
                        <h5>💰 Transparent Earnings</h5>
                        <p>Only approved tasks are paid. No gimmicks, no hidden fees.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="p-4 shadow rounded bg-white h-100">
                        <h5>🌐 Open to Everyone</h5>
                        <p>Students, homemakers, professionals—anyone can start earning today.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="p-4 shadow rounded bg-white h-100">
                        <h5>🎯 Empowering the Masses</h5>
                        <p>Our mission is to create one million micro-entrepreneurs by 2027.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ✅ Final Call to Action -->
    <section id="cta" class="py-5 bg-primary text-white text-center">
        <div class="container animate__animated animate__fadeInUp py-5">
            <p class="lead mb-3 ">
                ConnectWith9.com is your platform to explore flexible income through micro tasks—start your journey
                today.
            </p>
            <a href="register.php" class="btn btn-light btn-lg animate__animated animate__pulse animate__infinite">Start
                Exploring Tasks</a>
        </div>
    </section>

    <!-- Footer -->
    <?php
    include("footer.php");
    ?>
    <!-- JS + AOS Init -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script>
        AOS.init();
    </script>

</body>

</html>