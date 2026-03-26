<?php include 'includes/header.php'; ?>
<link rel="stylesheet" href="css/style.css">
<!-- Bootstrap Icons CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

<main>
   <br>
   <!-- Hero Section -->
   <!-- <section id="hero" style="padding:60px 0; background: #e8f0fe;"> -->
   <section id="hero" class="hero-section"
      style="background: url('images/MAIN_BG.jpg') center/cover no-repeat !important;">
      <div class="container text-center header">
         <h1 class="display-4 fw-bold" style=" ">Contact Us</h1>
         <p class="lead">Your gateway to earning rewards by performing simple social media tasks.</p>
         <a href="register.php" class="btn btn-primary ">Get Started</a>
      </div>
   </section>

   <!-- Contact Section -->
   <section class="py-5">
      <div class="container">
         <div class="row align-items-stretch">
            <!-- Contact Details on the Left -->
            <div class="col-md-6 d-flex">
               <div class="bg-light p-4 rounded shadow w-100 d-flex flex-column">
                  <div class="text-center">
                  <h2 class="text-primary mb-3"><i class="bi bi-telephone-fill"></i> Get in Touch</h2>
                  <p>Feel free to reach out to us through any of the following contact methods.</p>
                  </div>
                  <br> <br> 
                  <div style="padding:20 0 0 50px">
                     <div class="mb-3 d-flex align-items-center">
                        <i class="bi bi-telephone-fill text-primary fs-3"></i>
                        <div class="ms-3">
                           <span class="fw-bold">Call Us:</span>
                           <p class="mb-0">+1 234 567 890</p>
                        </div>
                     </div>
                     <div class="mb-3 d-flex align-items-center">
                        <i class="bi bi-envelope-fill text-primary fs-3"></i>
                        <div class="ms-3">
                           <span class="fw-bold">Email Us:</span>
                           <p class="mb-0">support@example.com</p>
                        </div>
                     </div>
                     <div class="mb-3 d-flex align-items-center">
                        <i class="bi bi-geo-alt-fill text-primary fs-3"></i>
                        <div class="ms-3">
                           <span class="fw-bold">Our Location:</span>
                           <p class="mb-0">123 Street, City, Country</p>
                        </div>
                     </div>
                  </div>
               </div>
            </div>

            <!-- Contact Form on the Right -->
            <div class="col-md-6 d-flex">
               <div class="bg-light p-4 rounded shadow w-100 d-flex flex-column">
                  <h2 class="text-center text-primary mb-3"><i class="bi bi-chat-left-text-fill"></i> Contact Us</h2>
                  <form action="process_contact.php" method="POST" class="mt-auto">
                     <div class="mb-3">
                        <label for="name" class="form-label"><i class="bi bi-person-fill"></i> Your Name:</label>
                        <input type="text" name="name" id="name" class="form-control" required>
                     </div>
                     <div class="mb-3">
                        <label for="email" class="form-label"><i class="bi bi-envelope-fill"></i> Your Email:</label>
                        <input type="email" name="email" id="email" class="form-control" required>
                     </div>
                     <div class="mb-3">
                        <label for="phone" class="form-label"><i class="bi bi-envelope-fill"></i> Your Phone:</label>
                        <input type="tel" name="phone" id="phone" class="form-control" required>
                     </div>
                     <div class="mb-3">
                        <label for="message" class="form-label"><i class="bi bi-chat-left-text-fill"></i> Your
                           Message:</label>
                        <textarea name="message" id="message" class="form-control" rows="4" required></textarea>
                     </div>
                     <button type="submit" class="btn btn-primary w-100">Send Message</button>
                  </form>
               </div>
            </div>
         </div>

         <!-- Google Map -->
         <div class="row mt-5">
            <div class="col">
               <iframe class="w-100 rounded shadow" height="350" frameborder="0" style="border:0;"
                  src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3151.8354345094316!2d144.9537363155043!3d-37.81627974202186!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x6ad65d5df1b1d23b%3A0x5045675218cee40!2z5LiW55WM5bed5bO25YyX5LqG!5e0!3m2!1sen!2sbd!4v1637577701744!5m2!1sen!2sbd"
                  allowfullscreen>
               </iframe>
            </div>
         </div>
      </div>
   </section>

   <!-- Social Media Section -->
   <section class="py-4 text-center">
      <h3 class="fw-bold">Follow Us</h3>
      <div class="d-flex justify-content-center">
         <a href="#" class="text-primary me-3 fs-3"><i class="bi bi-facebook"></i></a>
         <a href="#" class="text-info me-3 fs-3"><i class="bi bi-twitter"></i></a>
         <a href="#" class="text-danger me-3 fs-3"><i class="bi bi-instagram"></i></a>
         <a href="#" class="text-dark fs-3"><i class="bi bi-linkedin"></i></a>
      </div>
   </section>

</main>
<?php include 'includes/footer.php'; ?>