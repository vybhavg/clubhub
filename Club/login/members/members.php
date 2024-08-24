<?php
// Start session and include the database connection file
session_start();
include('/var/www/html/db_connect.php'); // Include your database connection file here
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set default values for the first visit after login
if (!isset($_SESSION['selected_branch'])) {
    $_SESSION['selected_branch'] = null;
}
if (!isset($_SESSION['selected_club'])) {
    $_SESSION['selected_club'] = null;
}
if (!isset($_SESSION['update_type'])) {
    $_SESSION['update_type'] = 'events'; // Default to 'events'
}

$selectedBranch = $_SESSION['selected_branch'];
$selectedClub = $_SESSION['selected_club'];
$updateType = isset($_GET['update_type']) ? $_GET['update_type'] : $_SESSION['update_type'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_event'])) {
        // Handle adding events
        $title = $_POST['event_title'];
        $description = $_POST['event_description'];
        $club_id = $_POST['club_id'];

        $sql = "INSERT INTO events (title, description, club_id) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $title, $description, $club_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Event added successfully.";
        } else {
            $_SESSION['message'] = "Error adding event.";
        }
        $stmt->close();
    } elseif (isset($_POST['add_recruitment'])) {
        // Handle adding recruitments
        $role = $_POST['role'];
        $description = $_POST['recruitment_description'];
        $deadline = $_POST['deadline'];
        $club_id = $_POST['club_id'];

        $sql = "INSERT INTO recruitments (role, description, deadline, club_id) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $role, $description, $deadline, $club_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Recruitment added successfully.";
        } else {
            $_SESSION['message'] = "Error adding recruitment.";
        }
        $stmt->close();
    } elseif (isset($_POST['delete_event'])) {
        // Handle deleting events
        $event_id = $_POST['event_id'];
        $sql = "DELETE FROM events WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $event_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Event deleted successfully.";
        } else {
            $_SESSION['message'] = "Error deleting event.";
        }
        $stmt->close();
    } elseif (isset($_POST['delete_recruitment'])) {
        // Handle deleting recruitments
        $recruitment_id = $_POST['recruitment_id'];
        $sql = "DELETE FROM recruitments WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $recruitment_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Recruitment deleted successfully.";
        } else {
            $_SESSION['message'] = "Error deleting recruitment.";
        }
        $stmt->close();
    } elseif (isset($_POST['select_branch'])) {
        $_SESSION['selected_branch'] = $_POST['branch_id'];
        $selectedBranch = $_POST['branch_id'];
    } elseif (isset($_POST['select_club'])) {
        $_SESSION['selected_club'] = $_POST['club_id'];
        $selectedClub = $_POST['club_id'];
    }
}



// Fetch events and recruitments based on selected club
$eventsResult = $selectedClub ? $conn->prepare("SELECT * FROM events WHERE club_id = ?") : null;
$recruitmentsResult = $selectedClub ? $conn->prepare("SELECT * FROM recruitments WHERE club_id = ?") : null;
if ($eventsResult) {
    $eventsResult->bind_param("i", $selectedClub);
    $eventsResult->execute();
    $eventsResult = $eventsResult->get_result();
}
if ($recruitmentsResult) {
    $recruitmentsResult->bind_param("i", $selectedClub);
    $recruitmentsResult->execute();
    $recruitmentsResult = $recruitmentsResult->get_result();
}

// Fetch applications based on selected club
$applicationsResult = $selectedClub ? $conn->prepare("SELECT s.name as student_name, s.email as email, a.resume_path as resume_path FROM applications a INNER JOIN students s ON a.student_id = s.id WHERE a.club_id = ?") : null;
if ($applicationsResult) {
    $applicationsResult->bind_param("i", $selectedClub);
    $applicationsResult->execute();
    $applicationsResult = $applicationsResult->get_result();
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

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
    <li><a href="#contact" class="scroll-link" data-scroll="contact">Contact</a></li>
  </ul>
  <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
</nav>

    </div>
  </header>
<main class="main">

    <!-- Hero Section -->
    <section id="hero" class="hero section accent-background">
        <img src="assets/img/hero-bg.jpg" alt="" data-aos="fade-in">
        <div class="container text-center" data-aos="fade-up" data-aos-delay="100">
           <div class="cont" ><h2>Welcome, Club Members!</h2>
            <p>Manage your events, recruitments, and applications efficiently.</p></div>
            <a href="#events" class="btn-scroll" title="Scroll Down"><i class="bi bi-chevron-down"></i></a>
        </div>
    </section><!-- /Hero Section -->
    
    <?php if ($updateType == 'events') { ?>
        <!-- About Section -->
    <section id="events" class="about section">
        <!-- Section Title -->
        <div class="container section-title" data-aos="fade-up">
            <h2>Events</h2> 
            <p>Necessitatibus eius consequatur ex aliquid fuga eum quidem sint consectetur velit</p>
        </div><!-- End Section Title -->
    </section><!-- /About Section -->


    <?php } elseif ($updateType == 'recruitments') { ?>
        <!-- About Section -->
    <section id="recruitments" class="about section">
        <!-- Section Title -->
        <div class="container section-title" data-aos="fade-up">
            <h2>Recruitments</h2> 
            <p>Necessitatibus eius consequatur ex aliquid fuga eum quidem sint consectetur velit</p>
        </div><!-- End Section Title -->
    </section><!-- /About Section -->
   
    <?php } elseif ($updateType == 'applications') { ?>
        <!-- About Section -->
    <section id="applications" class="about section">
        <!-- Section Title -->
        <div class="container section-title" data-aos="fade-up">
            <h2>Applications</h2> 
            <p>Necessitatibus eius consequatur ex aliquid fuga eum quidem sint consectetur velit</p>
        </div><!-- End Section Title -->
    </section><!-- /About Section -->
    <?php } ?>

    
<div class="form-container">
    <!-- Branch Selection Form -->
    <form id="branch-form" method="post" class="mb-4">
        <div class="form-group">
            <label for="branch_id">Select Branch:</label>
            <select name="branch_id" id="branch_id" class="form-control">
                <option value="">Select Branch</option>
                <?php 
                if ($branchesResult && $branchesResult->num_rows > 0) {
                    while ($branch = $branchesResult->fetch_assoc()) { ?>
                        <option value="<?php echo htmlspecialchars($branch['id']); ?>" <?php if ($selectedBranch == $branch['id']) echo 'selected'; ?>><?php echo htmlspecialchars($branch['branch_name']); ?></option>
                    <?php } 
                } else {
                    echo "<option value=''>No branches available</option>";
                }
                ?>
            </select>
        </div>
        <button type="submit" name="select_branch" class="btn btn-custom">Select Branch</button>
    </form>

    <!-- Club Selection Form -->
    <form id="club-form" method="post" class="mb-4">
        <div class="form-group">
            <label for="club_id">Select Club:</label>
            <select name="club_id" id="club_id" class="form-control">
                <option value="">Select Club</option>
                <?php 
                if ($clubsResult && $clubsResult->num_rows > 0) {
                    while ($club = $clubsResult->fetch_assoc()) { ?>
                        <option value="<?php echo htmlspecialchars($club['id']); ?>" <?php if ($selectedClub == $club['id']) echo 'selected'; ?>><?php echo htmlspecialchars($club['club_name']); ?></option>
                    <?php } 
                } else {
                    echo "<option value=''>No clubs available</option>";
                }
                ?>
            </select>
        </div>
        <button type="submit" name="select_club" class="btn btn-custom">Select Club</button>
    </form>

    <!-- Event Form -->
    <?php if ($updateType == 'events') { ?>
        <form id="event-form" method="post" class="mb-4">
            <div class="form-group">
                <label for="event_title">Event Title:</label>
                <input type="text" name="event_title" id="event_title" class="form-control">
            </div>
            <div class="form-group">
                <label for="event_description">Event Description:</label>
                <textarea name="event_description" id="event_description" class="form-control"></textarea>
            </div>
            <input type="hidden" name="club_id" id="event_club_id" value="<?php echo htmlspecialchars($selectedClub); ?>">
            <button type="submit" name="add_event" class="btn btn-custom">Add Event</button>
        </form>
    <?php } ?>

    <!-- Recruitment Form -->
    <?php if ($updateType == 'recruitments') { ?>
        <form id="recruitment-form" method="post" class="mb-4">
            <div class="form-group">
                <label for="role">Role:</label>
                <input type="text" name="role" id="role" class="form-control">
            </div>
            <div class="form-group">
                <label for="recruitment_description">Description:</label>
                <textarea name="recruitment_description" id="recruitment_description" class="form-control"></textarea>
            </div>
            <div class="form-group">
                <label for="deadline">Deadline:</label>
                <input type="date" name="deadline" id="deadline" class="form-control">
            </div>
            <input type="hidden" name="club_id" id="recruitment_club_id" value="<?php echo htmlspecialchars($selectedClub); ?>">
            <button type="submit" name="add_recruitment" class="btn btn-custom">Add Recruitment</button>
        </form>
    <?php } ?>
</div>

</main>



    <!-- Faq Section -->
    <section id="faq" class="faq section light-background">
    <div class="container section-title" data-aos="fade-up">
        <?php if ($updateType == 'events') { ?>
            <h2>Existing Events</h2>
            <p>Here are the existing events.</p>
        <?php } elseif ($updateType == 'recruitments') { ?>
            <h2>Current Recruitments</h2>
            <p>Here are the current recruitment opportunities.</p>
        <?php } elseif ($updateType == 'applications') { ?>
            <h2>Student Applications</h2>
            <p>Here are the applications received.</p>
        <?php } ?>
    </div><!-- End Section Title -->

    <div class="container">
        <?php if ($updateType == 'events') { ?>
            <div class="row faq-item" data-aos="fade-up" data-aos-delay="100">
                <div class="col-lg-12">
                    <h4>Existing Events</h4>
                    <ul class="list-group">
                        <?php 
                        if ($eventsResult && $eventsResult->num_rows > 0) {
                            while ($event = $eventsResult->fetch_assoc()) { ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php echo htmlspecialchars($event['title']); ?>: <?php echo htmlspecialchars($event['description']); ?>
                                    <form method="post" class="d-inline-block">
                                        <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($event['id']); ?>">
                                        <button type="submit" name="delete_event" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </li>
                            <?php }
                        } else {
                            echo "<li class='list-group-item'>No events available</li>";
                        }
                        ?>
                    </ul>
                </div>
            </div>

        <?php } elseif ($updateType == 'recruitments') { ?>
            <div class="row faq-item" data-aos="fade-up" data-aos-delay="100">
                <div class="col-lg-12">
                    <h4>Existing Recruitments</h4>
                    <ul class="list-group">
                        <?php 
                        if ($recruitmentsResult && $recruitmentsResult->num_rows > 0) {
                            while ($recruitment = $recruitmentsResult->fetch_assoc()) { ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php echo htmlspecialchars($recruitment['role']); ?>: <?php echo htmlspecialchars($recruitment['description']); ?> (Deadline: <?php echo htmlspecialchars($recruitment['deadline']); ?>)
                                    <form method="post" class="d-inline-block">
                                        <input type="hidden" name="recruitment_id" value="<?php echo htmlspecialchars($recruitment['id']); ?>">
                                        <button type="submit" name="delete_recruitment" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </li>
                            <?php }
                        } else {
                            echo "<li class='list-group-item'>No recruitments available</li>";
                        }
                        ?>
                    </ul>
                </div>
            </div>

        <?php } elseif ($updateType == 'applications') { ?>
            <div class="row faq-item" data-aos="fade-up" data-aos-delay="100">
                <div class="col-lg-12">
                    <h4>Existing Applications</h4>
                    <ul class="list-group">
                        <?php 
                        if ($applicationsResult && $applicationsResult->num_rows > 0) {
                            while ($application = $applicationsResult->fetch_assoc()) { ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php echo htmlspecialchars($application['student_name']); ?>: <?php echo htmlspecialchars($application['email']); ?>
                                    <a href="<?php echo htmlspecialchars($application['resume_path']); ?>" class="btn btn-primary btn-sm" target="_blank">View Resume</a>
                                </li>
                            <?php }
                        } else {
                            echo "<li class='list-group-item'>No applications available</li>";
                        }
                        ?>
                    </ul>
                </div>
            </div>
        <?php } ?>
    </div>
</section>
<!-- /Faq Section -->

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
<script>
$(document).ready(function() {
    // Handle branch form submission
    $('#branch-form').on('submit', function(event) {
        event.preventDefault(); // Prevent traditional form submission
        fetchData();
    });

    // Handle club form submission
    $('#club-form').on('submit', function(event) {
        event.preventDefault(); // Prevent traditional form submission
        fetchData();
    });

    // Handle event form submission
    $('#event-form').on('submit', function(event) {
        event.preventDefault(); // Prevent traditional form submission
        $.ajax({
            url: 'fetch_data.php',
            type: 'POST',
            data: $(this).serialize(), // Serialize form data
            success: function(response) {
                // Handle success - update the page with the response
                console.log(response);
            },
            error: function(xhr, status, error) {
                // Handle error
                console.error(error);
            }
        });
    });

    // Handle recruitment form submission
    $('#recruitment-form').on('submit', function(event) {
        event.preventDefault(); // Prevent traditional form submission
        $.ajax({
            url: 'fetch_data.php',
            type: 'POST',
            data: $(this).serialize(), // Serialize form data
            success: function(response) {
                // Handle success - update the page with the response
                console.log(response);
            },
            error: function(xhr, status, error) {
                // Handle error
                console.error(error);
            }
        });
    });

    function fetchData() {
        var branch = $('#branch_id').val();
        var club = $('#club_id').val();
        var updateType = $('#update_type').val();

        $.ajax({
            url: 'fetch_data.php',
            type: 'POST',
            data: {
                branch: branch,
                club: club,
                updateType: updateType
            },
            success: function(response) {
                // Handle success - update the page with the response
                // Example: Update a section of the page with new data
                console.log(response);
            },
            error: function(xhr, status, error) {
                // Handle error
                console.error(error);
            }
        });
    }
});
</script>

</body>

</html>
