<?php
session_start();
include('/var/www/html/db_connect.php'); // Ensure this file connects to your database correctly
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set the timezone to IST
date_default_timezone_set('Asia/Kolkata'); // Set the timezone to IST

// Get the update type from the URL parameter, default to 'events'
$updateType = isset($_GET['update_type']) ? $_GET['update_type'] : 'events';

// Initialize variables
$club_name = 'All Clubs'; // Displaying for all clubs
$student_id = isset($_SESSION['student_id']) ? $_SESSION['student_id'] : 0; // Ensure student_id is available

$currentDateTime = date('Y-m-d H:i:s'); // Get the current date and time

// Fetch all upcoming events (those that have not started) that the student has not registered for
$stmt_fetch_events = $conn->prepare("
    SELECT e.*, c.club_name, e.latitude, e.longitude 
    FROM events e
    INNER JOIN clubs c ON e.club_id = c.id
    LEFT JOIN event_registrations r ON e.id = r.event_id AND r.student_id = ?
    WHERE r.event_id IS NULL AND e.event_start_time >= ?
");
$stmt_fetch_events->bind_param("is", $student_id, $currentDateTime);

if ($stmt_fetch_events) {
    $stmt_fetch_events->execute();
    $eventsResult = $stmt_fetch_events->get_result();
    $stmt_fetch_events->close();
} else {
    error_log("Prepare failed: " . $conn->error);
    $_SESSION['message'] = "Error fetching events.";
}

// Fetch all recruitments from all clubs
$stmt_fetch_recruitments = $conn->prepare("
    SELECT r.*, c.club_name 
    FROM recruitments r
    INNER JOIN clubs c ON r.club_id = c.id
");
if ($stmt_fetch_recruitments) {
    $stmt_fetch_recruitments->execute();
    $recruitmentsResult = $stmt_fetch_recruitments->get_result();
    $stmt_fetch_recruitments->close();
} else {
    error_log("Prepare failed: " . $conn->error);
    $_SESSION['message'] = "Error fetching recruitments.";
}

// Fetch registered events for the current student (those that have not ended)
$stmt_fetch_registered_events = $conn->prepare("
    SELECT e.id AS event_id, e.title, e.description, c.club_name, r.registration_date, e.latitude, e.longitude, 
           e.event_start_time, e.event_end_time, r.student_id, s.student_name, s.college_email 
    FROM events e
    INNER JOIN clubs c ON e.club_id = c.id
    INNER JOIN event_registrations r ON e.id = r.event_id
    INNER JOIN student_login_details s ON r.student_id = s.id
    WHERE r.student_id = ? AND e.event_end_time > NOW()
");
$stmt_fetch_registered_events->bind_param("i", $student_id);



if ($stmt_fetch_registered_events) {
    $stmt_fetch_registered_events->execute();
    $registeredEventsResult = $stmt_fetch_registered_events->get_result();
    $stmt_fetch_registered_events->close();
} else {
    error_log("Prepare failed: " . $conn->error);
    $_SESSION['message'] = "Error fetching registered events.";
}

// Fetch the student's name
if (isset($_SESSION['student_id'])) {
    $student_id = $_SESSION['student_id'];

    $stmt_fetch_student_name = $conn->prepare("SELECT student_name FROM student_login_details WHERE id = ?");
    $stmt_fetch_student_name->bind_param("i", $student_id);
    if ($stmt_fetch_student_name) {
        $stmt_fetch_student_name->execute();
        $result = $stmt_fetch_student_name->get_result();
        if ($row = $result->fetch_assoc()) {
            $student_name = $row['student_name'];
        }
        $stmt_fetch_student_name->close();
    } else {
        error_log("Prepare failed: " . $conn->error);
        $_SESSION['message'] = "Error fetching student details.";
    }
}

// Close the database connection
$conn->close();
?>





<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Index - Squadfree Bootstrap Template</title>
  <meta name="description" content="">
  <meta name="keywords" content="">

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Fonts -->
 <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Raleway:300,300i,400,400i,500,500i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">

  <!-- =======================================================
  * Template Name: Squadfree
  * Template URL: https://bootstrapmade.com/squadfree-free-bootstrap-template-creative/
  * Updated: Aug 07 2024 with Bootstrap v5.3.3
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->


</head>
<body class="index-page">

<header id="header" class="header d-flex align-items-center fixed-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center justify-content-between">
        <a href="index.html" class="logo d-flex align-items-center">
            <!-- Uncomment the line below if you also wish to use an image logo -->
            <!-- <img src="assets/img/logo.png" alt=""> -->
            <h1 class="sitename">CLUBHUB</h1>
        </a>
        <nav id="navmenu" class="navmenu">
            <ul>
                <li><a href="?update_type=events#hero" class="scroll-link" data-scroll="hero">Home</a></li>
                <li><a href="?update_type=events#events" class="scroll-link" data-scroll="events">Events</a></li>
                <li><a href="?update_type=recruitments#recruitments" class="scroll-link" data-scroll="recruitments">Recruitments</a></li>
                <li><a href="?update_type=applications#applications" class="scroll-link" data-scroll="applications">Applications</a></li>
                <li><a href="?update_type=onboarding#onboarding" class="scroll-link" data-scroll="onboarding">Onboarding</a></li>                
                <li><a href="#contact" class="scroll-link" data-scroll="contact">Contact</a></li>
            </ul>
            <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
        </nav>
    </div>
</header>

<main class="main">
   <!-- Hero Section -->
<section id="hero" class="hero section accent-background">
    <img src="assets/img/gitamvsp.jpg" alt="" data-aos="fade-in">
    <div class="container text-center" data-aos="fade-up" data-aos-delay="100">
        <div class="cont">
            <h2>Welcome, <?php echo htmlspecialchars($student_name); ?>!</h2>
            <p>Manage your events, recruitments, and applications efficiently.</p>
        </div>
        <a href="#events" class="btn-scroll" title="Scroll Down"><i class="bi bi-chevron-down"></i></a>
    </div>
</section><!-- /Hero Section -->


   <!-- Dynamic Content Sections -->
    <?php if ($updateType == 'events') { ?>
<!-- Events Section -->
<section id="events" class="about section">
    <div class="container section-title" data-aos="fade-up">
        <h2>Events</h2>
        <p>Here are the events available for registration.</p>
    </div>
    <div class="form-container">
        <div class="upbox update-item filter-events active">
           <?php if ($eventsResult && $eventsResult->num_rows > 0): ?>
    <?php while ($event = $eventsResult->fetch_assoc()): ?>
        <div class="update-entry">
            <h4><?php echo htmlspecialchars($event['title']); ?></h4>
            <p><?php echo htmlspecialchars($event['description']); ?></p>
            <p>Club: <?php echo htmlspecialchars($event['club_name'] ?? ''); ?></p>
            <p>Event Start Time: <?php echo htmlspecialchars($event['event_start_time'] ?? ''); ?></p>

            <!-- Check if latitude and longitude are provided for the map link -->
            <?php if (!empty($event['latitude']) && !empty($event['longitude'])): ?>
                <a href="https://www.google.com/maps?q=<?php echo htmlspecialchars($event['latitude']); ?>,<?php echo htmlspecialchars($event['longitude']); ?>" target="_blank" class="btn btn-secondary">View Location</a>
            <?php else: ?>
                <p>Location coordinates not available.</p>
            <?php endif; ?>
            
            <a href="register_event.php?event_id=<?php echo htmlspecialchars($event['id']); ?>&club_id=<?php echo htmlspecialchars($event['club_id']); ?>" class="btn btn-primary">Register</a>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p>No events available at the moment.</p>
<?php endif; ?>

        </div>
    </div>
</section>
<!-- /Events Section -->

<!-- Registered Events Section -->
<section id="registered-events" class="about section">
    <div class="container section-title" data-aos="fade-up">
        <h2>Registered Events</h2>
        <p>Here are the events you have registered for.</p>
    </div>
    <div class="form-container">
        <div class="upbox update-item filter-registered-events">
           <?php if ($registeredEventsResult && $registeredEventsResult->num_rows > 0): ?>
               <?php while ($event = $registeredEventsResult->fetch_assoc()): ?>
                   <div class="update-entry">
                       <h4><?php echo htmlspecialchars($event['title'] ?? ''); ?></h4>
                       <p><?php echo htmlspecialchars($event['description'] ?? ''); ?></p>
                       <p>Club: <?php echo htmlspecialchars($event['club_name'] ?? ''); ?></p>
                       <p>Event Start Time: <?php echo htmlspecialchars($event['event_start_time'] ?? ''); ?></p>
                       <p>Registered on: <?php echo htmlspecialchars(date('Y-m-d H:i:s', strtotime($event['registration_date'] ?? ''))); ?></p>


                       <!-- Check if latitude and longitude are provided for the map link -->
                       <?php if (!empty($event['latitude']) && !empty($event['longitude'])): ?>
                           <a href="https://www.google.com/maps?q=<?php echo htmlspecialchars($event['latitude']); ?>,<?php echo htmlspecialchars($event['longitude']); ?>" target="_blank" class="btn btn-secondary">View Location</a>
                       <?php else: ?>
                           <p>Location coordinates not available.</p>
                       <?php endif; ?>

                       <button onclick="registerForEvent(
                           <?php echo htmlspecialchars($event['event_id'] ?? ''); ?>, 
                           <?php echo htmlspecialchars($event['student_id'] ?? ''); ?>, 
                           '<?php echo htmlspecialchars($event['student_name'] ?? ''); ?>', 
                           '<?php echo htmlspecialchars($event['college_email'] ?? ''); ?>'
                       )" class="btn btn-primary">Attend</button>
                   </div>
               <?php endwhile; ?>
           <?php else: ?>
               <p>You have not registered for any events yet.</p>
           <?php endif; ?>
        </div>
    </div>
</section>
<!-- /Registered Events Section -->



  <script>
    function registerForEvent(eventId, studentId, studentName, studentEmail) {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    var form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'register_student.php';

                    var inputEventId = document.createElement('input');
                    inputEventId.type = 'hidden';
                    inputEventId.name = 'event_id';
                    inputEventId.value = eventId;
                    form.appendChild(inputEventId);

                    var inputStudentId = document.createElement('input');
                    inputStudentId.type = 'hidden';
                    inputStudentId.name = 'student_id';
                    inputStudentId.value = studentId;
                    form.appendChild(inputStudentId);

                    var inputStudentName = document.createElement('input');
                    inputStudentName.type = 'hidden';
                    inputStudentName.name = 'student_name';
                    inputStudentName.value = studentName;
                    form.appendChild(inputStudentName);

                    var inputStudentEmail = document.createElement('input');
                    inputStudentEmail.type = 'hidden';
                    inputStudentEmail.name = 'student_email';
                    inputStudentEmail.value = studentEmail;
                    form.appendChild(inputStudentEmail);

                    var inputLatitude = document.createElement('input');
                    inputLatitude.type = 'hidden';
                    inputLatitude.name = 'latitude';
                    inputLatitude.value = position.coords.latitude;
                    form.appendChild(inputLatitude);

                    var inputLongitude = document.createElement('input');
                    inputLongitude.type = 'hidden';
                    inputLongitude.name = 'longitude';
                    inputLongitude.value = position.coords.longitude;
                    form.appendChild(inputLongitude);

                    document.body.appendChild(form);
                    form.submit();
                },
                function(error) {
                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            alert("User denied the request for Geolocation.");
                            break;
                        case error.POSITION_UNAVAILABLE:
                            alert("Location information is unavailable.");
                            break;
                        case error.TIMEOUT:
                            alert("The request to get user location timed out.");
                            break;
                        case error.UNKNOWN_ERROR:
                            alert("An unknown error occurred.");
                            break;
                    }
                }
            );
        } else {
            alert("Geolocation is not supported by this browser.");
        }
    }
</script>



    
<?php } elseif ($updateType == 'recruitments') { ?>
    <!-- Recruitments Section -->
    <section id="recruitments" class="about section">
        <div class="container section-title" data-aos="fade-up">
            <h2>Recruitments</h2>
            <p>View the current recruitment opportunities below.</p>
        </div>
        <div class="form-container">
            <!-- Existing Recruitments List -->
            <div class="upbox update-item filter-recruitment">
                <?php if ($recruitmentsResult && $recruitmentsResult->num_rows > 0): ?>
                    <?php while ($recruitment = $recruitmentsResult->fetch_assoc()): ?>
                        <div class="update-entry">
                            <h4><?php echo htmlspecialchars($recruitment['role']); ?></h4>
                            <p><?php echo htmlspecialchars($recruitment['description']); ?></p>
                            <p>Deadline: <?php echo htmlspecialchars($recruitment['deadline']); ?></p>
                            <p>Club: <?php echo htmlspecialchars($recruitment['club_name']); ?></p>
                            <a href="Club/student/application.php?club_id=<?php echo htmlspecialchars($recruitment['club_id']); ?>" class="btn btn-primary">Apply</a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No recruitments available at the moment.</p>
                <?php endif; ?>
            </div>
        </div>
    </section><!-- /Recruitments Section -->
<?php } ?>



    <!-- Contact Section -->
    <section id="contact" class="contact section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>Contact</h2>
        <p>Necessitatibus eius consequatur ex aliquid fuga eum quidem sint consectetur velit</p>
      </div><!-- End Section Title -->

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row gy-4">

          <div class="col-lg-5">

            <div class="info-wrap">
              <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="200">
                <i class="bi bi-geo-alt flex-shrink-0"></i>
                <div>
                  <h3>Address</h3>
                  <p>A108 Adam Street, New York, NY 535022</p>
                </div>
              </div><!-- End Info Item -->

              <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="300">
                <i class="bi bi-telephone flex-shrink-0"></i>
                <div>
                  <h3>Call Us</h3>
                  <p>+1 5589 55488 55</p>
                </div>
              </div><!-- End Info Item -->

              <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="400">
                <i class="bi bi-envelope flex-shrink-0"></i>
                <div>
                  <h3>Email Us</h3>
                  <p>info@example.com</p>
                </div>
              </div><!-- End Info Item -->

              <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d48389.78314118045!2d-74.006138!3d40.710059!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c25a22a3bda30d%3A0xb89d1fe6bc499443!2sDowntown%20Conference%20Center!5e0!3m2!1sen!2sus!4v1676961268712!5m2!1sen!2sus" frameborder="0" style="border:0; width: 100%; height: 270px;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
          </div>

          <div class="col-lg-7">
            <form action="forms/contact.php" method="post" class="php-email-form" data-aos="fade-up" data-aos-delay="200">
              <div class="row gy-4">

                <div class="col-md-6">
                  <label for="name-field" class="pb-2">Your Name</label>
                  <input type="text" name="name" id="name-field" class="form-control" required="">
                </div>

                <div class="col-md-6">
                  <label for="email-field" class="pb-2">Your Email</label>
                  <input type="email" class="form-control" name="email" id="email-field" required="">
                </div>

                <div class="col-md-12">
                  <label for="subject-field" class="pb-2">Subject</label>
                  <input type="text" class="form-control" name="subject" id="subject-field" required="">
                </div>

                <div class="col-md-12">
                  <label for="message-field" class="pb-2">Message</label>
                  <textarea class="form-control" name="message" rows="10" id="message-field" required=""></textarea>
                </div>

                <div class="col-md-12 text-center">
                  <div class="loading">Loading</div>
                  <div class="error-message"></div>
                  <div class="sent-message">Your message has been sent. Thank you!</div>

                  <button type="submit">Send Message</button>
                </div>

              </div>
            </form>
          </div><!-- End Contact Form -->

        </div>

      </div>

    </section><!-- /Contact Section -->

  </main>

  <footer id="footer" class="footer dark-background">

    <div class="container footer-top">
      <div class="row gy-4">
        <div class="col-lg-4 col-md-6 footer-about">
          <a href="index.html" class="logo d-flex align-items-center">
            <span class="sitename">Squadfree</span>
          </a>
          <div class="footer-contact pt-3">
            <p>A108 Adam Street</p>
            <p>New York, NY 535022</p>
            <p class="mt-3"><strong>Phone:</strong> <span>+1 5589 55488 55</span></p>
            <p><strong>Email:</strong> <span>info@example.com</span></p>
          </div>
          <div class="social-links d-flex mt-4">
            <a href=""><i class="bi bi-twitter-x"></i></a>
            <a href=""><i class="bi bi-facebook"></i></a>
            <a href=""><i class="bi bi-instagram"></i></a>
            <a href=""><i class="bi bi-linkedin"></i></a>
          </div>
        </div>

        <div class="col-lg-2 col-md-3 footer-links">
          <h4>Useful Links</h4>
          <ul>
            <li><a href="#">Home</a></li>
            <li><a href="#">About us</a></li>
            <li><a href="#">Services</a></li>
            <li><a href="#">Terms of service</a></li>
            <li><a href="#">Privacy policy</a></li>
          </ul>
        </div>

        <div class="col-lg-2 col-md-3 footer-links">
          <h4>Our Services</h4>
          <ul>
            <li><a href="#">Web Design</a></li>
            <li><a href="#">Web Development</a></li>
            <li><a href="#">Product Management</a></li>
            <li><a href="#">Marketing</a></li>
            <li><a href="#">Graphic Design</a></li>
          </ul>
        </div>

        <div class="col-lg-4 col-md-12 footer-newsletter">
          <h4>Our Newsletter</h4>
          <p>Subscribe to our newsletter and receive the latest news about our products and services!</p>
          <form action="forms/newsletter.php" method="post" class="php-email-form">
            <div class="newsletter-form"><input type="email" name="email"><input type="submit" value="Subscribe"></div>
            <div class="loading">Loading</div>
            <div class="error-message"></div>
            <div class="sent-message">Your subscription request has been sent. Thank you!</div>
          </form>
        </div>

      </div>
    </div>

    <div class="container copyright text-center mt-4">
      <p>© <span>Copyright</span> <strong class="px-1 sitename">Squadfree</strong> <span>All Rights Reserved</span></p>
      <div class="credits">
        <!-- All the links in the footer should remain intact. -->
        <!-- You can delete the links only if you've purchased the pro version. -->
        <!-- Licensing information: https://bootstrapmade.com/license/ -->
        <!-- Purchase the pro version with working PHP/AJAX contact form: [buy-url] -->
        Designed by <a href="https://bootstrapmade.com/">BootstrapMade</a>
      </div>
    </div>

  </footer>

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Preloader -->
  <div id="preloader"></div>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="assets/vendor/imagesloaded/imagesloaded.pkgd.min.js"></script>
  <script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>

  <!-- Main JS File -->
  <script src="assets/js/main.js"></script>
    <!-- Include jQuery if not already included -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function(){
    // Smooth scrolling for navigation links
    $('a[href^="#"]').on('click', function(event) {
        event.preventDefault(); // Prevent default anchor behavior

        var target = $(this.getAttribute('href'));

        if (target.length) {
            $('html, body').stop().animate({
                scrollTop: target.offset().top
            }, 1000); // Adjust the speed (1000ms) as needed
        }
    });
});
</script>

</body>

</html>
 
 
