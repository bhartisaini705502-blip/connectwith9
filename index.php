<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ConnectWith9.com</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="stylesheet" href="css/new.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS CSS -->
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">

</head>

<body>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm sticky-top animate__animated animate__fadeInDown">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <img src="images/logo3.png" alt="Company logo" height="50px">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php">About Us</a></li>
                    <li class="nav-item"><a class="nav-link" href="#how">How It Works</a></li>
                    <li class="nav-item"><a class="nav-link" href="#categories">Categories</a></li>
                    <li class="nav-item"><a class="nav-link" href="#join">Join as Freelancer</a></li>
                    <li class="nav-item"><a class="nav-link" href="#faqs">FAQs</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact Us</a></li>
                    <li class="nav-item"><a class="btn btn-success ms-2" href="login.php">Login / Register</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="hero-section text-white position-relative overflow-hidden">
        <div class="hero-bg position-absolute top-0 start-0 w-100 h-100"
            style="background-image: url('images/337109.jpg'); background-size: cover; background-position: center; z-index: -2;">
        </div>
        <div class="hero-overlay position-absolute top-0 start-0 w-100 h-100"
            style="background: rgba(0, 0, 0, 0.6); z-index: -1;">
        </div>
        <div class="container d-flex flex-column justify-content-center align-items-center text-center"
            style="min-height: 100vh;">
            <h1 class="display-3 fw-bold animate__animated animate__fadeInDown">Work from Home with Freedom</h1>
            <p class="lead mt-3 animate__animated animate__fadeInUp animate__delay-1s">
                Join India’s Fastest Growing Task-Based Freelancing Platform.<br />
                Choose tasks you love. Work at your pace. Get paid for what you complete.
            </p>
            <div
                class="mt-4 d-flex flex-column flex-sm-row justify-content-center gap-3 animate__animated animate__zoomIn animate__delay-2s">
                <a href="registeration.php"
                    class="btn btn-success btn-lg shadow-lg animate__animated animate__pulse animate__infinite">Join
                    Now</a>
                <a href="login.php" class="btn btn-outline-light btn-lg shadow">Login</a>
            </div>
        </div>
    </header>


    <!-- How It Works Section -->
    <section id="how" class="bg-light text-center py-5">
        <div class="container">
            <h2 class="section-title">How ConnectWith9 Works</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card p-4 text-center shadow-lg border-0 animate__animated animate__fadeInUp"
                        style="transition: transform 0.3s;">
                        <img src="images\sign-up.png" alt="Sign Up" class="mb-3 mx-auto"
                            style="width: 160px; height: auto;">
                        <h4 class="fw-bold mb-2">1. Sign Up for Free</h4>
                        <p class="text-muted mb-0">Create your profile and choose your interests.</p>
                    </div>

                </div>
                <div class="col-md-4">


                    <div class="card p-4 text-center shadow-lg border-0 animate__animated animate__fadeInUp"
                        style="transition: transform 0.3s;">
                        <img src="images\pick.webp" alt="Sign Up" class="mb-3 mx-auto"
                            style="width: 160px; height: auto;">
                        <h4 class="fw-bold mb-2">2. Pick Your Tasks</h4>
                        <p class="text-muted mb-0"></p>Choose daily tasks from various categories.</p>
                    </div>
                </div>
                <div class="col-md-4">

                    <div class="card p-4 text-center shadow-lg border-0 animate__animated animate__fadeInUp"
                        style="transition: transform 0.3s;">
                        <img src="images\submit.png" alt="Sign Up" class="mb-3 mx-auto"
                            style="width: 160px; height: auto;">
                        <h4 class="fw-bold mb-2">3. Submit & Get Paid</h4>
                        <p class="text-muted mb-0"></p>Submit quality work, get approved, and receive payments.</p>
                    </div>
                </div>
            </div>
            <a href="registeration.php" class="btn btn-success mt-4">Get Started Today</a>
        </div>
    </section>

    <!-- Popular Task Categories Section -->
    <section id="task-categories" class="py-5 bg-white">
        <div class="container">
            <h2 class="section-title text-center mb-5 animate__animated animate__fadeInUp animate__delay-2s">
                Explore Opportunities You Can Work On
            </h2>

            <div class="row text-center g-4">
                <!-- Category 1 -->
                <div class="col-6 col-md-3 animate__animated animate__fadeInUp animate__delay-2s">
                    <div class="card border-0 shadow h-100 p-3">
                        <div class="mb-2 fs-1">📢</div>
                        <h5 class="fw-bold">Digital Marketing</h5>
                        <p class="small text-muted">SEO, ads, and campaigns</p>
                    </div>
                </div>

                <!-- Category 2 -->
                <div class="col-6 col-md-3 animate__animated animate__fadeInUp animate__delay-2s">
                    <div class="card border-0 shadow h-100 p-3">
                        <div class="mb-2 fs-1">📞</div>
                        <h5 class="fw-bold">Tele Calling & Sales</h5>
                        <p class="small text-muted">Leads, calls, conversions</p>
                    </div>
                </div>

                <!-- Category 3 -->
                <div class="col-6 col-md-3 animate__animated animate__fadeInUp animate__delay-2s">
                    <div class="card border-0 shadow h-100 p-3">
                        <div class="mb-2 fs-1">🎓</div>
                        <h5 class="fw-bold">Education & Admissions</h5>
                        <p class="small text-muted">Counseling and support tasks</p>
                    </div>
                </div>

                <!-- Category 4 -->
                <div class="col-6 col-md-3 animate__animated animate__fadeInUp animate__delay-2s">
                    <div class="card border-0 shadow h-100 p-3">
                        <div class="mb-2 fs-1">💻</div>
                        <h5 class="fw-bold">App & Web Development</h5>
                        <p class="small text-muted">Frontend, backend, testing</p>
                    </div>
                </div>

                <!-- Category 5 -->
                <div class="col-6 col-md-3 animate__animated animate__fadeInUp animate__delay-3s">
                    <div class="card border-0 shadow h-100 p-3">
                        <div class="mb-2 fs-1">📱</div>
                        <h5 class="fw-bold">Social Media Tasks</h5>
                        <p class="small text-muted">Engagement & posting</p>
                    </div>
                </div>

                <!-- Category 6 -->
                <div class="col-6 col-md-3 animate__animated animate__fadeInUp animate__delay-3s">
                    <div class="card border-0 shadow h-100 p-3">
                        <div class="mb-2 fs-1">✍️</div>
                        <h5 class="fw-bold">Content & Reviews</h5>
                        <p class="small text-muted">Writing blogs, reviews</p>
                    </div>
                </div>

                <!-- Category 7 -->
                <div class="col-6 col-md-3 animate__animated animate__fadeInUp animate__delay-3s">
                    <div class="card border-0 shadow h-100 p-3">
                        <div class="mb-2 fs-1">🎨</div>
                        <h5 class="fw-bold">Graphic Designing</h5>
                        <p class="small text-muted">Posters, banners & more</p>
                    </div>
                </div>

                <!-- Category 8 -->
                <div class="col-6 col-md-3 animate__animated animate__fadeInUp animate__delay-3s">
                    <div class="card border-0 shadow h-100 p-3">
                        <div class="mb-2 fs-1">🧑‍💼</div>
                        <h5 class="fw-bold">Consultancy</h5>
                        <p class="small text-muted">Advisory and expert input</p>
                    </div>
                </div>

                <!-- Category 9 -->
                <div class="col-md-12 mt-4 animate__animated animate__fadeInUp animate__delay-4s">
                    <a href="login.php" class="btn btn-success btn-lg"> View All Categories</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Join Section -->
<section id="join" class="bg-light text-center py-5">
    <div class="container">
        <h2 class="section-title mb-5 fw-bold">Why Thousands Trust <span class="text-primary">ConnectWith9</span></h2>
        <div class="row g-4">
            <!-- Feature Item -->
            <div class="col-sm-6 col-lg-4">
                <div class="feature-box h-100 p-4 shadow-sm rounded bg-white">
                    <div class="icon-circle mb-3">
                        <i class="fas fa-laptop-house"></i>
                    </div>
                    <h6 class="fw-semibold">Work From Anywhere, Anytime</h6>
                </div>
            </div>
            <!-- Feature Item -->
            <div class="col-sm-6 col-lg-4">
                <div class="feature-box h-100 p-4 shadow-sm rounded bg-white">
                    <div class="icon-circle mb-3">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <h6 class="fw-semibold">Wide Range of Tasks</h6>
                </div>
            </div>
            <!-- Feature Item -->
            <div class="col-sm-6 col-lg-4">
                <div class="feature-box h-100 p-4 shadow-sm rounded bg-white">
                    <div class="icon-circle mb-3">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h6 class="fw-semibold">Earn as per Performance</h6>
                </div>
            </div>
            <!-- Feature Item -->
            <div class="col-sm-6 col-lg-4">
                <div class="feature-box h-100 p-4 shadow-sm rounded bg-white">
                    <div class="icon-circle mb-3">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <h6 class="fw-semibold">Transparent Payment System</h6>
                </div>
            </div>
            <!-- Feature Item -->
            <div class="col-sm-6 col-lg-4">
                <div class="feature-box h-100 p-4 shadow-sm rounded bg-white">
                    <div class="icon-circle mb-3">
                        <i class="fas fa-ban"></i>
                    </div>
                    <h6 class="fw-semibold">No Hidden Charges</h6>
                </div>
            </div>
            <!-- Feature Item -->
            <div class="col-sm-6 col-lg-4">
                <div class="feature-box h-100 p-4 shadow-sm rounded bg-white">
                    <div class="icon-circle mb-3">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <h6 class="fw-semibold">Privacy & Data Protection</h6>
                </div>
            </div>
        </div>
    </div>
</section>

    <!-- Featured Tasks Section -->
    <section id="featured-tasks" class="py-5 " style="background-color:#ffffff;">
        <div class="container">
            <h2 class="section-title text-center mb-4 animate__animated animate__fadeInDown"> Tasks You Can Do Today
            </h2>

            <div class="row g-4 justify-content-center">
                <!-- Task Card 1 -->
                <div class="col-md-4 animate__animated animate__fadeInUp">
                    <div class="card shadow h-100">
                        <div class="card-body">
                            <div style="text-align: center;">
                                <img src="images\refer.png" alt="Sign Up" class="mb-3 mx-auto"
                                    style="width: 160px; height: 160px;">
                                <h5 class="card-title text-primary"> <b>Refer Clients</b></h5>
                            </div>
                            <div class="px-5 py-4">

                                <p><strong>💰 Payout:</strong> ₹1000</p>
                                <p><strong>📂 Category:</strong> Referral</p>
                                <p><strong>⏱️ Time Required:</strong> 2 mins</p>
                                <div class="text-center">
                                    <a href="login.php" class="btn btn-success w-100">Apply Now</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Task Card 2 -->
                <div class="col-md-4 animate__animated animate__fadeInUp animate__delay-1s">
                    <div class="card shadow h-100">
                        <div class="card-body">
                            <div style="text-align: center;">
                                <img src="images\testing.jpg" alt="Sign Up" class="mb-3 mx-auto"
                                    style="width: 160px; height: 160px;">
                                <h5 class="card-title text-primary"> <b>App Testing</b></h5>
                            </div>
                            <div class="px-5 py-4">
                                <p><strong>💰 Payout:</strong> ₹100</p>
                                <p><strong>📂 Category:</strong> Testing</p>
                                <p><strong>⏱️ Time Required:</strong> 20 mins</p>
                                <div class="text-center">
                                    <a href="login.php" class="btn btn-success w-100">Apply Now</a>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Task Card 3 -->
                <div class="col-md-4 animate__animated animate__fadeInUp animate__delay-2s">
                    <div class="card shadow h-100">
                        <div class="card-body">
                            <div style="text-align: center;">
                                <img src="images\review.png" alt="Sign Up" class="mb-3 mx-auto"
                                    style="width: 160px; height: 160px;">
                                <h5 class="card-title text-primary"> <b>Write a Product Review</b></h5>
                            </div>
                            <div class="px-5 py-4">
                                <p><strong>💰 Payout:</strong> ₹75</p>
                                <p><strong>📂 Category:</strong> Writing</p>
                                <p><strong>⏱️ Time Required:</strong> 15 mins</p>
                                <div class="text-center">
                                    <a href="login.php" class="btn btn-success w-100">Apply Now</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonial Section -->
    <section id="testimonials" class="py-5 bg-light">
        <div class="container text-center">
            <h2 class="section-title animate__animated animate__fadeInUp">💬 What Our Users Say</h2>

            <!-- Bootstrap Carousel -->
            <div id="testimonialCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
                <div class="carousel-inner">

                    <!-- Testimonial 1 -->
                    <div class="carousel-item active">
                        <div class="testimonial">
                            <img src="images/women.jpg" class="rounded-circle mb-3" alt="User 1"
                                style="width: 100px; height: 100px; object-fit: cover;">
                            <p class="testimonial-text">“I’ve been earning from home while managing my family life. It’s
                                the best platform for flexible work!”</p>
                            <h5 class="user-name">Anita S.</h5>
                            <p class="user-location">Delhi, India</p>
                        </div>
                    </div>

                    <!-- Testimonial 2 -->
                    <div class="carousel-item">
                        <div class="testimonial">
                            <img src="images/male.jpeg" class="rounded-circle mb-3" alt="User 2"
                                style="width: 100px; height: 100px; object-fit: cover;">
                            <p class="testimonial-text">“ConnectWith9 has transformed the way I work. I love the variety
                                of tasks available and the freedom it offers.”</p>
                            <h5 class="user-name">Rajesh P.</h5>
                            <p class="user-location">Bangalore, India</p>
                        </div>
                    </div>

                    <!-- Testimonial 3 -->
                    <div class="carousel-item">
                        <div class="testimonial">
                            <img src="images/women.jpg" class="rounded-circle mb-3" alt="User 3"
                                style="width: 100px; height: 100px; object-fit: cover;">
                            <p class="testimonial-text">“I’ve completed several tasks, and the payouts are timely. This
                                platform is a game-changer for freelancing.”</p>
                            <h5 class="user-name">Priya M.</h5>
                            <p class="user-location">Mumbai, India</p>
                        </div>
                    </div>

                </div>

                <!-- Carousel Controls -->
                <!-- Previous -->
                <button class="carousel-control-prev" type="button" data-bs-target="#testimonialCarousel"
                    data-bs-slide="prev">
                    <i class="fas fa-chevron-left fa-2x text-white"
                        style="background-color: rgba(22, 142, 255, 0.75); padding: 12px; border-radius: 50%;"></i>
                    <span class="visually-hidden">Previous</span>
                </button>

                <!-- Next -->
                <button class="carousel-control-next" type="button" data-bs-target="#testimonialCarousel"
                    data-bs-slide="next">
                    <i class="fas fa-chevron-right fa-2x text-white"
                        style="background-color: rgba(22, 142, 255, 0.75); padding: 12px; border-radius: 50%;"></i>
                    <span class="visually-hidden">Next</span>
                </button>

            </div>
        </div>
    </section>

    <!-- Download Our App Section -->
    <section id="download-app" class="py-5 text-center " style="background-color:#ffffff;">
        <div class="container">
            <h2 class="section-title mb-3 animate__animated animate__fadeInDown"> Work Smarter with the ConnectWith9
                App</h2>
            <p class="mb-4 animate__animated animate__fadeInUp">Boost your productivity and earn on-the-go with our
                upcoming mobile app.</p>

            <div class="row align-items-center">
                <!-- Sneak Peek Image -->
                <div class="col-md-6 mb-4 mb-md-0 animate__animated animate__zoomIn">
                    <img src="images/app.jpg" alt="App Preview" class="img-fluid rounded shadow" height="300px">
                </div>

                <!-- Benefits and Info -->
                <div class="col-md-6 text-md-start animate__animated animate__fadeInRight p-5">
                    <ul class="list-unstyled fs-5 mb-4 " style="line-height: 3;">
                        <li>✅ Manage tasks from anywhere</li>
                        <li>✅ Instant notifications & updates</li>
                        <li>✅ Seamless submissions and tracking</li>
                        <li>✅ Coming soon on Android & iOS</li>
                    </ul>

                    <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center justify-content-md-start">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg"
                            alt="Google Play" style="height: 50px;">

                    </div>

                    <p class="text-muted mt-3 text-success">App launching soon — stay tuned!</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQs Section -->
    <section id="faqs" class="py-5 bg-light">
        <div class="container">
            <h2 class="section-title text-center mb-4"> Frequently Asked Questions</h2>
            <div class="row align-items-center">
                <!-- Left Column:  -->

                <div class="col-md-6">
                    <div class="accordion" id="faqAccordion">
                        <!-- FAQ Item 1 -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faqHeading1">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#faq1" aria-expanded="false" aria-controls="faq1">
                                    How do I receive payment?
                                    <i class="fa fa-caret-down ms-2"></i> <!-- Down arrow -->
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse" aria-labelledby="faqHeading1"
                                data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Payments are made after task approval via bank transfer or UPI.
                                </div>
                            </div>
                        </div>

                        <!-- FAQ Item 2 -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faqHeading2">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#faq2" aria-expanded="false" aria-controls="faq2">
                                    Are there any joining fees?
                                    <i class="fa fa-caret-down ms-2"></i> <!-- Down arrow -->
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" aria-labelledby="faqHeading2"
                                data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    No, it's completely free to join ConnectWith9.com.
                                </div>
                            </div>
                        </div>

                        <!-- FAQ Item 3 -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faqHeading3">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#faq3" aria-expanded="false" aria-controls="faq3">
                                    How many tasks can I do in a day?
                                    <i class="fa fa-caret-down ms-2"></i> <!-- Down arrow -->
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" aria-labelledby="faqHeading3"
                                data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    You can complete as many tasks as you are eligible for.
                                </div>
                            </div>
                        </div>

                        <!-- FAQ Item 4 -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faqHeading4">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#faq4" aria-expanded="false" aria-controls="faq4">
                                    What if my task is rejected?
                                    <i class="fa fa-caret-down ms-2"></i> <!-- Down arrow -->
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" aria-labelledby="faqHeading4"
                                data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    You will receive feedback and can correct/resubmit where allowed.
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- <a href="#" class="btn btn-primary mt-3">View All FAQs</a> -->
                </div>
                <!-- Right Column:  -->
                <div class="col-md-6 mb-4 mb-md-0 p-5">
                    <img src="images\faq.jpg" alt="FAQs Image" class="img-fluid rounded shadow-lg" height="250px">
                </div>


            </div>
        </div>

       
    </section>

    <div class="px-5 py-4 text-primary bg-light rounded shadow-sm">
    <h3 class="text-center fw-bold ">
        Join India’s Fastest Growing Task-Based Freelancing Platform.
        Choose tasks you love. Work at your pace. Get paid for what you complete.
</h3>
    <div class="text-center mt-3">
        <a href="registeration.php" class="btn btn-success btn-lg px-4"><strong> JOIN NOW !</strong></a>
    </div>
    <br>
</div>


    <!-- Add the following CSS to rotate the arrow when the accordion is expanded -->
    <style>
        /* Rotate the caret when the accordion is expanded (i.e., rotated 180 degrees) */
        .accordion-button:not(.collapsed) .fa-caret-down {
            transform: rotate(180deg);
        }
    </style>

    <!-- Add the following JavaScript to toggle the arrows dynamically -->
    <script>
        // Toggle the caret arrow direction when accordion is toggled
        var accordionButtons = document.querySelectorAll('.accordion-button');

        accordionButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                var caret = this.querySelector('.fa');
                // Toggle the caret direction
                if (this.classList.contains('collapsed')) {
                    caret.classList.remove('fa-caret-up');
                    caret.classList.add('fa-caret-down');
                } else {
                    caret.classList.remove('fa-caret-down');
                    caret.classList.add('fa-caret-up');
                }
            });
        });
    </script>


    <!-- Newsletter Signup -->
    <!-- <section id="newsletter" class="py-5 bg-light">
        <div class="container">
            <div class="p-5 rounded-4 shadow-lg bg-white text-center mx-auto" style="max-width: 600px;">
                <h2 class="section-title mb-3">Stay Updated!</h2>
                <p class="mb-4 text-muted">Get task updates, platform tips, and exclusive opportunities straight to your
                    inbox.</p>
                <form class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                    <input type="email" class="form-control form-control-lg" placeholder="Enter your email" required />
                    <button type="submit" class="btn btn-primary btn-lg px-4">
                        Subscribe
                    </button>
                </form>
            </div>
        </div>
    </section> -->


    <!-- Footer -->
    <?php
    include("footer.php");
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>