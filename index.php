<?php
// index.php

include('db_connect.php'); // Ensure this path is correct

// Display errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fetch events from the events table
$events_sql = "SELECT e.*, c.club_name 
               FROM events e 
               LEFT JOIN clubs c ON e.club_id = c.id 
               ORDER BY e.id DESC";
$events_result = $conn->query($events_sql);

$events = array();
if ($events_result && $events_result->num_rows > 0) {
    while($row = $events_result->fetch_assoc()) {
        $events[] = $row;
    }
}

// Fetch recruitments from the recruitments table
$recruitments_sql = "SELECT r.*, c.club_name 
                     FROM recruitments r 
                     LEFT JOIN clubs c ON r.club_id = c.id 
                     ORDER BY r.id DESC";
$recruitments_result = $conn->query($recruitments_sql);

$recruitments = array();
if ($recruitments_result && $recruitments_result->num_rows > 0) {
    while ($row = $recruitments_result->fetch_assoc()) {
        $recruitments[] = $row;
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

  <title>ClubHub</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="Club/assets/img/favic.ico" rel="icon">
  <link href="Club/assets/img/favic.ico" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Raleway:300,300i,400,400i,500,500i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="Club/assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="Club/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="Club/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="Club/assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="Club/assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="Club/assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="Club/assets/css/style.css" rel="stylesheet">

  <!-- =======================================================
  * Template Name: Squadfree
  * Template URL: https://bootstrapmade.com/squadfree-free-bootstrap-template-creative/
  * Updated: Mar 17 2024 with Bootstrap v5.3.3
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>

<body>

  <!-- ======= Header ======= -->
  <header id="header" class="fixed-top header-transparent">
    <div class="container d-flex align-items-center justify-content-between position-relative">

      <div class="logo">
        <h1 class="text-light"><a href="index.html"><span>ClubHub</span></a></h1>
        <!-- Uncomment below if you prefer to use an image logo -->
        <!-- <a href="index.html"><img src="Club/assets/img/logo.png" alt="" class="img-fluid"></a>-->
      </div>

      <nav id="navbar" class="navbar">
        <ul>
          <li><a class="nav-link scrollto active" href="#hero">Home</a></li>
          <li><a class="nav-link scrollto" href="Club/comm.html">About Us</a></li>
          <li><a class="nav-link scrollto" href="#services">Campusessssssssssss</a></li>
                    
          <li class="dropdown megamenu"><a href="#Clubs"><span>Clubs</span> <i class="bi bi-chevron-down"></i></a>
            <ul>
              <li>
                <strong>Visakhapatnam</strong>
                <a href="#portfolio">Cultural and Creative Clubssssss</a>
                <a href="#portfolio">Academic and Professional Development Clubs</a>
                <a href="#portfolio">Recreational and Hobby Clubs</a>
                <a href="#portfolio">Community Service and Social Clubs</a>

              </li>
              <li>
                <strong>Hyderabad</strong>
                <a href="#portfolio">Cultural and Creative Clubs</a>
                <a href="#portfolio">Academic and Professional Development Clubs</a>
                <a href="#portfolio">Recreational and Hobby Clubs</a>
                <a href="#portfolio">Community Service and Social Clubs</a>
              </li>
              <li>
                <strong>Bangalore</strong>
                <a href="#portfolio">Cultural and Creative Clubs</a>
                <a href="#portfolio">Academic and Professional Development Clubs</a>
                <a href="#portfolio">Recreational and Hobby Clubs</a>
                <a href="#portfolio">Community Service and Social Clubs</a>
              </li>
              
            </ul>
          </li>
          <li><a class="nav-link scrollto" href="Club/login/login.php">Members</a></li>
          <li><a class="nav-link scrollto" href="#contact">Contact</a></li>
        </ul>
        <i class="bi bi-list mobile-nav-toggle"></i>
      </nav><!-- .navbar -->

    </div>
  </header><!-- End Header -->

  <!-- ======= Hero Section ======= -->
  <section id="hero">
    <div class="hero-container" data-aos="fade-up">
      <h1>Welcome to ClubHub</h1>
      <h2>The Students Club Interaction Platform</h2>
      <a href="#about" class="btn-get-started scrollto"><i class="bx bx-chevrons-down"></i></a>
    </div>
  </section><!-- End Hero -->

  <main id="main">

    <!-- ======= About Section ======= -->
    <section id="about" class="about">
      <div class="container">

        <div class="row no-gutters">
          <div class="content col-xl-5 d-flex align-items-stretch" data-aos="fade-up">
            <div class="content">
              <h3>ClubHub Purpose</h3>
              <p>
                The purpose of a club hub is to simplify the process of discovering, joining, and participating in student clubs, while also facilitating communication and support for club activities within a university.              </p>
              <a href="Club/comm.html" class="about-btn">About us <i class="bx bx-chevron-right"></i></a>
            </div>
          </div>
          <div class="col-xl-7 d-flex align-items-stretch">
            <div class="icon-boxes d-flex flex-column justify-content-center">
              <div class="row">
                <div class="col-md-6 icon-box" data-aos="fade-up" data-aos-delay="100">
                  <i class="bx bx-receipt"></i>
                  <h4>Networking Opportunities</h4>
                  <p>In this platform College clubs often bring together students with similar interests and career aspirations</p>
                </div>
                <div class="col-md-6 icon-box" data-aos="fade-up" data-aos-delay="200">
                  <i class="bx bx-cube-alt"></i>
                  <h4>Strong Community</h4>
                  <p>Students able to know and interact with the clubs</p>
                </div>
                <div class="col-md-6 icon-box" data-aos="fade-up" data-aos-delay="300">
                  <i class="bx bx-shield"></i>
                  <h4>Centralized Information</h4>
                  <p>Students get information about all the clubs and events taking place</p>
                </div>
                <div class="col-md-6 icon-box" data-aos="fade-up" data-aos-delay="400">
                  <i class="bx bx-image"></i>
                  <h4>Recruitment Updates</h4>
                  <p>Students get Recruitment updates on this platform</p>
                </div>
              </div>
            </div><!-- End .content-->
          </div>
        </div>

      </div>
    </section><!-- End About Section -->



<!-- Updates Section -->
<section id="update" class="contact section-bg">
  <div class="container" data-aos="fade-up">
    <div class="section-title">
      <h2>Updates</h2>
    </div>

    <div class="row">
      <div class="col-lg-12 d-flex justify-content-center">
        <ul id="update-flters">
          <li data-filter=".filter-events" class="filter-active">Events</li>
          <li data-filter=".filter-recruitment">Recruitments</li>
        </ul>
      </div>
    </div>

    <div class="upbox update-item filter-events">
      <h3>Events</h3>
      <?php foreach ($events as $event): ?>
        <div class="update-entry">
          <h4><?php echo htmlspecialchars($event['title']); ?></h4>
          <p><?php echo htmlspecialchars($event['description']); ?></p>
          <p>Club: <?php echo htmlspecialchars($event['club_name']); ?></p>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="upbox update-item filter-recruitment" style="display: none;">
      <h3>Recruitments</h3>
      <?php foreach ($recruitments as $recruitment): ?>
        <div class="update-entry">
          <h4><?php echo htmlspecialchars($recruitment['role']); ?></h4>
          <p><?php echo htmlspecialchars($recruitment['description']); ?></p>
          <p>Deadline: <?php echo htmlspecialchars($recruitment['deadline']); ?></p>
          <p>Club: <?php echo htmlspecialchars($recruitment['club_name']); ?></p>
          <a href="Club/student/student.php" class="btn btn-primary">Apply</a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section><!-- End Updates Section -->


    <!-- ======= Services Section ======= -->
    <section id="services" class="services">
      <div class="container">

        <div class="section-title" data-aos="fade-in" data-aos-delay="100">
          <h2>Campuses</h2>
          <p>Total three campuses where each campus has their specific clubs.</p>
        </div>

        <div class="row">
          <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
            <div class="icon-box" data-aos="fade-up">
              <div class="icon"><i class="bx bxl-dribbble"></i></div>
              <h4 class="title"><a href="https://www.gitam.edu/academics/gitam-campuses/visakhapatnam-campus">Visakhapatnam</a></h4>
              <p class="description">Established in 1980, the GITAM Visakhapatnam campus is the main GITAM campus and has the most GITAM institutes under one roof.</p>
            </div>
          </div>

          <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
            <div class="icon-box" data-aos="fade-up" data-aos-delay="100">
              <div class="icon"><i class="bx bx-file"></i></div>
              <h4 class="title"><a href="https://www.gitam.edu/academics/gitam-campuses/hyderabad-campus">Hyderabad</a></h4>
              <p class="description">Established in 2009, today GITAM (Deemed to be University) Hyderabad has grown into a well-acclaimed hub of higher education in the state of Telangana.</p>
            </div>
          </div>

          <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
            <div class="icon-box" data-aos="fade-up" data-aos-delay="200">
              <div class="icon"><i class="bx bx-tachometer"></i></div>
              <h4 class="title"><a href="https://www.gitam.edu/academics/gitam-campuses/bengaluru-campus">Bangalore</a></h4>
              <p class="description">Established in 2012, GITAM (Deemed to be University) Bengaluru is a poised academic building, reflecting modern infrastructure.</p>
            </div>
          </div>

          <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0">
            <div class="icon-box" data-aos="fade-up" data-aos-delay="300">
              <div class="icon"><i class="bx bx-arrow-back"></i></div>
              <h4 class="title"><a href="https://www.gitam.edu/academics/gitam-campuses">Learn more about campuses</a></h4>
              
            </div>
          </div>

        </div>

      </div>
    </section><!-- End Services Section -->

    <!-- ======= Counts Section ======= -->
    <section id="counts" class="counts  section-bg">
      <div class="container">

        <div class="row no-gutters">

          <div class="col-lg-3 col-md-6 d-md-flex align-items-md-stretch">
            <div class="count-box">
              <i class="bi bi-emoji-smile"></i>
              <span data-purecounter-start="0" data-purecounter-end="9" data-purecounter-duration="1" class="purecounter"></span>
              <p><strong>Cultural and Creative Clubs</strong></p>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 d-md-flex align-items-md-stretch">
            <div class="count-box">
              <i class="bi bi-journal-richtext"></i>
              <span data-purecounter-start="0" data-purecounter-end="6" data-purecounter-duration="1" class="purecounter"></span>
              <p><strong>Academic and Professional Development Clubs</strong> </p>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 d-md-flex align-items-md-stretch">
            <div class="count-box">
              <i class="bi bi-headset"></i>
              <span data-purecounter-start="0" data-purecounter-end="9" data-purecounter-duration="1" class="purecounter"></span>
              <p><strong>Recreational and Hobby Clubs</strong></p>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 d-md-flex align-items-md-stretch">
            <div class="count-box">
              <i class="bi bi-people"></i>
              <span data-purecounter-start="0" data-purecounter-end="15" data-purecounter-duration="1" class="purecounter"></span>
              <p><strong>Community Service and Social Clubs</strong></p>
            </div>
          </div>

        </div>

      </div>
    </section><!-- End Counts Section -->

  
    <!-- ======= Portfolio Section ======= -->
    <section id="portfolio" class="portfolio">
      <div class="container">

        <div class="section-title" data-aos="fade-in" data-aos-delay="100">
          <h2>Clubs</h2>
          <p>Gitam has a total of 39 clubs in all the three campuses</p>
        </div>

        <div class="row" data-aos="fade-in">
          <div class="col-lg-12 d-flex justify-content-center">
            <ul id="portfolio-flters">
              <li data-filter="*" class="filter-active">All</li>
              <li data-filter=".filter-app">VSP</li>
              <li data-filter=".filter-card">HYD</li>
              <li data-filter=".filter-web">BLR</li>
            </ul>
          </div>
        </div>

        <div class="row portfolio-container" data-aos="fade-up">

          <div class="col-lg-3 col-md-6 portfolio-item filter-card">
            <div class="portfolio-wrap">
              <img src="https://www.gitam.edu/sites/default/files/2022-09/gcgc.png" class="img-fluid" alt="">
              <div class="portfolio-links">
                <a href="https://www.gitam.edu/sites/default/files/2022-09/gcgc.png" data-gallery="portfolioGallery" class="portfolio-lightbox" title="Card 2">Cooking Club-HYD</a>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6 portfolio-item filter-card">
            <div class="portfolio-wrap">
              <img src="https://www.gitam.edu/sites/default/files/2022-09/Gitam-quiz-club.png" class="img-fluid" alt="">
              <div class="portfolio-links">
                <a href="https://www.gitam.edu/sites/default/files/2022-09/Gitam-quiz-club.png" data-gallery="portfolioGallery" class="portfolio-lightbox" title="Card 2">Gitam Quiz Club</a>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 portfolio-item filter-web">
            <div class="portfolio-wrap">
              <img src="https://www.gitam.edu/sites/default/files/2022-09/Perspective-arts-GITAM-BLR.png" class="img-fluid" alt="">
              <div class="portfolio-links">
                <a href="https://www.gitam.edu/sites/default/files/2022-09/Perspective-arts-GITAM-BLR.png" data-gallery="portfolioGallery" class="portfolio-lightbox" title="App 2">Perspective Arts Club</a>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 portfolio-item filter-card">
            <div class="portfolio-wrap">
              <img src="https://www.gitam.edu/sites/default/files/2022-09/kcc.png" class="img-fluid" alt="">
              <div class="portfolio-links">
                <a href="https://www.gitam.edu/sites/default/files/2022-09/kcc.png" data-gallery="portfolioGallery" class="portfolio-lightbox" title="Card 2">Korean Culture Club</a>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 portfolio-item filter-web">
            <div class="portfolio-wrap">
              <img src="https://www.gitam.edu/sites/default/files/2022-09/GUSAC.png" class="img-fluid" alt="">
              <div class="portfolio-links">
                <a href="https://www.gitam.edu/sites/default/files/2022-09/GUSAC.png" data-gallery="portfolioGallery" class="portfolio-lightbox" title="Web 2">GUSAC</a>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 portfolio-item filter-card">
            <div class="portfolio-wrap">
              <img src="https://www.gitam.edu/sites/default/files/2022-10/ic_logo.jpg" class="img-fluid" alt="">
              <div class="portfolio-links">
                <a href="https://www.gitam.edu/sites/default/files/2022-10/ic_logo.jpg" data-gallery="portfolioGallery" class="portfolio-lightbox" title="App 3">Innovation Center</a>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 portfolio-item filter-card">
            <div class="portfolio-wrap">
              <img src="https://www.gitam.edu/sites/default/files/2022-10/Kalakrithi.jpg" class="img-fluid" alt="">
              <div class="portfolio-links">
                <a href="https://www.gitam.edu/sites/default/files/2022-10/Kalakrithi.jpg" data-gallery="portfolioGallery" class="portfolio-lightbox" title="Card 1">Kalakrithi - HYD</a>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 portfolio-item filter-app">
            <div class="portfolio-wrap">
              <img src="https://www.gitam.edu/sites/default/files/2022-10/The-Finer--Things-Club.png" class="img-fluid" alt="">
              <div class="portfolio-links">
                <a href="https://www.gitam.edu/sites/default/files/2022-10/The-Finer--Things-Club.png" data-gallery="portfolioGallery" class="portfolio-lightbox" title="Card 3">The Finer Things Club</a>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 portfolio-item filter-web">
            <div class="portfolio-wrap">
              <img src="https://www.gitam.edu/sites/default/files/2022-09/Speaking-club.png" class="img-fluid" alt="">
              <div class="portfolio-links">
                <a href="https://www.gitam.edu/sites/default/files/2022-09/Speaking-club.png" data-gallery="portfolioGallery" class="portfolio-lightbox" title="Card 3">Speaking Club</a>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 portfolio-item filter-card">
            <div class="portfolio-wrap">
              <img src="https://www.gitam.edu/sites/default/files/2022-10/disha.jpg" class="img-fluid" alt="">
              <div class="portfolio-links">
                <a href="https://www.gitam.edu/sites/default/files/2022-10/disha.jpg" data-gallery="portfolioGallery" class="portfolio-lightbox" title="Card 3">Disha</a>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 portfolio-item filter-app">
            <div class="portfolio-wrap">
              <img src="https://www.gitam.edu/sites/default/files/2022-09/agrow.jpg" class="img-fluid" alt="">
              <div class="portfolio-links">
                <a href="https://www.gitam.edu/sites/default/files/2022-09/agrow.jpg" data-gallery="portfolioGallery" class="portfolio-lightbox" title="Card 3">Agrow</a>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 portfolio-item filter-web">
            <div class="portfolio-wrap">
              <img src="https://www.gitam.edu/sites/default/files/2022-09/kalakrithi-blr.png" class="img-fluid" alt="">
              <div class="portfolio-links">
                <a href="https://www.gitam.edu/sites/default/files/2022-09/kalakrithi-blr.png" data-gallery="portfolioGallery" class="portfolio-lightbox" title="Card 3">Kalakrithi-BLR</a>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 portfolio-item filter-card">
            <div class="portfolio-wrap">
              <img src="https://www.gitam.edu/sites/default/files/2022-10/charaiveti_logo.jpg" class="img-fluid" alt="">
              <div class="portfolio-links">
                <a href="https://www.gitam.edu/sites/default/files/2022-10/charaiveti_logo.jpg" data-gallery="portfolioGallery" class="portfolio-lightbox" title="Card 3">Charaiveti</a>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 portfolio-item filter-app">
            <div class="portfolio-wrap">
              <img src="https://www.gitam.edu/sites/default/files/2022-08/aikya.jpg" class="img-fluid" alt="">
              <div class="portfolio-links">
                <a href="https://www.gitam.edu/sites/default/files/2022-08/aikya.jpg" data-gallery="portfolioGallery" class="portfolio-lightbox" title="Card 3">Aikya</a>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 portfolio-item filter-web">
            <div class="portfolio-wrap">
              <img src="https://www.gitam.edu/sites/default/files/2022-10/gstudio.jpg" class="img-fluid" alt="">
              <div class="portfolio-links">
                <a href="https://www.gitam.edu/sites/default/files/2022-10/gstudio.jpg" data-gallery="portfolioGallery" class="portfolio-lightbox" title="Card 3">Gstudio - BLR</a>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 portfolio-item filter-app">
            <div class="portfolio-wrap">
              <img src="https://www.gitam.edu/sites/default/files/2022-09/bcg.png" class="img-fluid" alt="">
              <div class="portfolio-links">
                <a href="https://www.gitam.edu/sites/default/files/2022-09/bcg.png" data-gallery="portfolioGallery" class="portfolio-lightbox" title="Card 3">Biotechnology Club</a>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 portfolio-item filter-app">
            <div class="portfolio-wrap">
              <img src="https://www.gitam.edu/sites/default/files/2022-09/cats-logo.jpg" class="img-fluid" alt="">
              <div class="portfolio-links">
                <a href="https://www.gitam.edu/sites/default/files/2022-09/cats-logo.jpg" data-gallery="portfolioGallery" class="portfolio-lightbox" title="Card 3">CATS</a>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 portfolio-item filter-app">
            <div class="portfolio-wrap">
              <img src="https://www.gitam.edu/sites/default/files/2022-10/Creative-Arts,Kalakrithi_Club_Visakhapatnam.jpg" class="img-fluid" alt="">
              <div class="portfolio-links">
                <a href="https://www.gitam.edu/sites/default/files/2022-10/Creative-Arts,Kalakrithi_Club_Visakhapatnam.jpg" data-gallery="portfolioGallery" class="portfolio-lightbox" title="Card 3">Creative Arts Club</a>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 portfolio-item filter-card">
            <div class="portfolio-wrap">
              <img src="https://www.gitam.edu/sites/default/files/2022-10/Engineers-Without-Borders-(EWB.jpg" class="img-fluid" alt="">
              <div class="portfolio-links">
                <a href="https://www.gitam.edu/sites/default/files/2022-10/Engineers-Without-Borders-(EWB.jpg" data-gallery="portfolioGallery" class="portfolio-lightbox" title="Card 3">Engineers Without Borders</a>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 portfolio-item filter-app">
            <div class="portfolio-wrap">
              <img src="https://www.gitam.edu/sites/default/files/2022-10/creseendo.jpg" class="img-fluid" alt="">
              <div class="portfolio-links">
                <a href="https://www.gitam.edu/sites/default/files/2022-10/creseendo.jpg" data-gallery="portfolioGallery" class="portfolio-lightbox" title="Card 3">Creseendo</a>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 portfolio-item filter-app">
            <div class="portfolio-wrap">
              <img src="https://www.gitam.edu/sites/default/files/2022-09/CYSEC.png" class="img-fluid" alt="">
              <div class="portfolio-links">
                <a href="https://www.gitam.edu/sites/default/files/2022-09/CYSEC.png" data-gallery="portfolioGallery" class="portfolio-lightbox" title="Card 3">CYSEC</a>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 portfolio-item filter-card">
            <div class="portfolio-wrap">
              <img src="https://www.gitam.edu/sites/default/files/2022-09/Entrepreneurs-club.png" class="img-fluid" alt="">
              <div class="portfolio-links">
                <a href="https://www.gitam.edu/sites/default/files/2022-09/Entrepreneurs-club.png" data-gallery="portfolioGallery" class="portfolio-lightbox" title="Card 3">Entrepreneurs club - HYD</a>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 portfolio-item filter-app">
            <div class="portfolio-wrap">
              <img src="https://www.gitam.edu/sites/default/files/2022-09/dance-club.png" class="img-fluid" alt="">
              <div class="portfolio-links">
                <a href="https://www.gitam.edu/sites/default/files/2022-09/dance-club.png" data-gallery="portfolioGallery" class="portfolio-lightbox" title="Card 3">Dance club</a>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 portfolio-item filter-app">
            <div class="portfolio-wrap">
              <img src="https://www.gitam.edu/sites/default/files/2022-09/faces.png" class="img-fluid" alt="">
              <div class="portfolio-links">
                <a href="https://www.gitam.edu/sites/default/files/2022-09/faces.png" data-gallery="portfolioGallery" class="portfolio-lightbox" title="Card 3">Faces</a>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 portfolio-item filter-app">
            <div class="portfolio-wrap">
              <img src="https://www.gitam.edu/sites/default/files/2022-09/GITAM-Aeromodelling-club.png" class="img-fluid" alt="">
              <div class="portfolio-links">
                <a href="https://www.gitam.edu/sites/default/files/2022-09/GITAM-Aeromodelling-club.png" data-gallery="portfolioGallery" class="portfolio-lightbox" title="Card 3">GITAM Aeromodelling club</a>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 portfolio-item filter-app">
            <div class="portfolio-wrap">
              <img src="https://www.gitam.edu/sites/default/files/2022-10/GITAM-National-Service-Scheme.jpg" class="img-fluid" alt="">
              <div class="portfolio-links">
                <a href="https://www.gitam.edu/sites/default/files/2022-10/GITAM-National-Service-Scheme.jpg" data-gallery="portfolioGallery" class="portfolio-lightbox" title="Card 3">GITAM National Service Scheme</a>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 portfolio-item filter-app">
            <div class="portfolio-wrap">
              <img src="https://www.gitam.edu/sites/default/files/2022-08/GITAM-Toastmasters-club.jpg" class="img-fluid" alt="">
              <div class="portfolio-links">
                <a href="https://www.gitam.edu/sites/default/files/2022-08/GITAM-Toastmasters-club.jpg" data-gallery="portfolioGallery" class="portfolio-lightbox" title="Card 3">Toastmasters Club</a>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 portfolio-item filter-app">
            <div class="portfolio-wrap">
              <img src="https://www.gitam.edu/sites/default/files/2022-09/glug-logo.jpg" class="img-fluid" alt="">
              <div class="portfolio-links">
                <a href="https://www.gitam.edu/sites/default/files/2022-09/glug-logo.jpg" data-gallery="portfolioGallery" class="portfolio-lightbox" title="Card 3">Glug</a>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 portfolio-item filter-app">
            <div class="portfolio-wrap">
              <img src="https://www.gitam.edu/sites/default/files/2022-10/gstudio.jpg" class="img-fluid" alt="">
              <div class="portfolio-links">
                <a href="https://www.gitam.edu/sites/default/files/2022-10/gstudio.jpg" data-gallery="portfolioGallery" class="portfolio-lightbox" title="Card 3">Gstudio - VSP</a>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 portfolio-item filter-app">
            <div class="portfolio-wrap">
              <img src="https://www.gitam.edu/sites/default/files/2022-09/GUSAC.png" class="img-fluid" alt="">
              <div class="portfolio-links">
                <a href="https://www.gitam.edu/sites/default/files/2022-09/GUSAC.png" data-gallery="portfolioGallery" class="portfolio-lightbox" title="Card 3">GUSAC - VSP</a>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 portfolio-item filter-app">
            <div class="portfolio-wrap">
              <img src="https://www.gitam.edu/sites/default/files/2022-08/helping-hands.jpg" class="img-fluid" alt="">
              <div class="portfolio-links">
                <a href="https://www.gitam.edu/sites/default/files/2022-08/helping-hands.jpg" data-gallery="portfolioGallery" class="portfolio-lightbox" title="Card 3">Helping Hands</a>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 portfolio-item filter-app">
            <div class="portfolio-wrap">
              <img src="https://www.gitam.edu/sites/default/files/2022-09/cooking-club-logo.jpg" class="img-fluid" alt="">
              <div class="portfolio-links">
                <a href="https://www.gitam.edu/sites/default/files/2022-09/cooking-club-logo.jpg" data-gallery="portfolioGallery" class="portfolio-lightbox" title="Card 3">Cooking Club - VSP</a>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 portfolio-item filter-app">
            <div class="portfolio-wrap">
              <img src="https://www.gitam.edu/sites/default/files/2022-09/Entrepreneurs-club.png" class="img-fluid" alt="">
              <div class="portfolio-links">
                <a href="https://www.gitam.edu/sites/default/files/2022-09/Entrepreneurs-club.png" data-gallery="portfolioGallery" class="portfolio-lightbox" title="Card 3">Entrepreneurs Club - VSP</a>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 portfolio-item filter-app">
            <div class="portfolio-wrap">
              <img src="https://www.gitam.edu/sites/default/files/2022-09/ncc-logo.jpg" class="img-fluid" alt="">
              <div class="portfolio-links">
                <a href="https://www.gitam.edu/sites/default/files/2022-09/ncc-logo.jpg" data-gallery="portfolioGallery" class="portfolio-lightbox" title="Card 3">NCC</a>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 portfolio-item filter-app">
            <div class="portfolio-wrap">
              <img src="https://www.gitam.edu/sites/default/files/2022-09/NOVUS_CLUB_VSP_3.png" class="img-fluid" alt="">
              <div class="portfolio-links">
                <a href="https://www.gitam.edu/sites/default/files/2022-09/NOVUS_CLUB_VSP_3.png" data-gallery="portfolioGallery" class="portfolio-lightbox" title="Card 3">NOVUS Club</a>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 portfolio-item filter-app">
            <div class="portfolio-wrap">
              <img src="https://www.gitam.edu/sites/default/files/2022-09/phonia.png" class="img-fluid" alt="">
              <div class="portfolio-links">
                <a href="https://www.gitam.edu/sites/default/files/2022-09/phonia.png" data-gallery="portfolioGallery" class="portfolio-lightbox" title="Card 3">Phonia</a>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 portfolio-item filter-app">
            <div class="portfolio-wrap">
              <img src="https://www.gitam.edu/sites/default/files/2022-09/Rotaract-Club-of-GITAM.jpg" class="img-fluid" alt="">
              <div class="portfolio-links">
                <a href="https://www.gitam.edu/sites/default/files/2022-09/Rotaract-Club-of-GITAM.jpg" data-gallery="portfolioGallery" class="portfolio-lightbox" title="Card 3">Rotaract Club</a>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 portfolio-item filter-app">
            <div class="portfolio-wrap">
              <img src="https://www.gitam.edu/sites/default/files/2022-09/softskills-logo.jpg" class="img-fluid" alt="">
              <div class="portfolio-links">
                <a href="https://www.gitam.edu/sites/default/files/2022-09/softskills-logo.jpg" data-gallery="portfolioGallery" class="portfolio-lightbox" title="Card 3">Softskills Club</a>
              </div>
            </div>
          </div>


          <div class="col-lg-3 col-md-6 portfolio-item filter-app">
            <div class="portfolio-wrap">
              <img src="https://www.gitam.edu/sites/default/files/2022-09/the-anchoring-club.jpg" class="img-fluid" alt="">
              <div class="portfolio-links">
                <a href="https://www.gitam.edu/sites/default/files/2022-09/the-anchoring-club.jpg" data-gallery="portfolioGallery" class="portfolio-lightbox" title="Web 3">The Anchoring Club</a>
              </div>
            </div>
          </div>

        </div>

      </div>
    </section><!-- End Portfolio Section -->


    <!-- ======= Contact Section ======= -->
    <section id="contact" class="contact section-bg">
      <div class="container" data-aos="fade-up">

        <div class="section-title">
          <h2>Contact</h2>
          <p>If you have any concerns or queries regarding this platform feel free to contact us.</p>
        </div>

        <div class="row">
          <div class="col-lg-6">
            <div class="info-box mb-4">
              <i class="bx bx-map"></i>
              <h3>Our Address</h3>
              <p>Gitam School of Science, Gandhi Nagar, Rushikonda, Visakhapatnam, Andhra Pradesh, India 530045</p>
            </div>
          </div>

          <div class="col-lg-3 col-md-6">
            <div class="info-box  mb-4">
              <i class="bx bx-envelope"></i>
              <h3>Email Us</h3>
              <p>clubhub@gmail.com</p>
            </div>
          </div>

          <div class="col-lg-3 col-md-6">
            <div class="info-box  mb-4">
              <i class="bx bx-phone-call"></i>
              <h3>Call Us</h3>
              <p>+91 63054 85269</p>
            </div>
          </div>

        </div>

        <div class="row">

          <div class="col-lg-6 ">
            <iframe class="mb-4 mb-lg-0" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3799.243749179112!2d83.37449957391598!3d17.780237891371655!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3a395b1e12cab301%3A0x6ee2b3b00e71f727!2sGitam%20School%20Of%20Science!5e0!3m2!1sen!2sin!4v1716714867507!5m2!1sen!2sin" frameborder="0" style="border:0; width: 100%; height: 384px;" allowfullscreen></iframe>
          </div>

          <div class="col-lg-6">
            <form action="Club/forms/contact.php" method="post" role="form" class="php-email-form">
              <div class="row">
                <div class="col-md-6 form-group">
                  <input type="text" name="name" class="form-control" id="name" placeholder="Your Name" required>
                </div>
                <div class="col-md-6 form-group mt-3 mt-md-0">
                  <input type="email" class="form-control" name="email" id="email" placeholder="Your Email" required>
                </div>
              </div>
              <div class="form-group mt-3">
                <input type="text" class="form-control" name="subject" id="subject" placeholder="Subject" required>
              </div>
              <div class="form-group mt-3">
                <textarea class="form-control" name="message" rows="5" placeholder="Message" required></textarea>
              </div>
              <div class="my-3">
                <div class="loading">Loading</div>
                <div class="error-message"></div>
                <div class="sent-message">Your message has been sent. Thank you!</div>
              </div>
              <div class="text-center"><button type="submit">Send Message</button></div>
            </form>
          </div>

        </div>

      </div>
    </section><!-- End Contact Section -->

  </main><!-- End #main -->

  <!-- ======= Footer ======= -->
  <footer id="footer">
    <div class="footer-top">
      <div class="container">
        <div class="row">

          <div class="col-lg-4 col-md-6">
            <div class="footer-info">
              <h3>ClubHub</h3>
              <p class="pb-3"><em>An Interactive Students Club PlatForm</em></p>
              <p>
                Gitam School of Science, Gandhi nagar, Rushikonda<br>
                Visakhapatnam, Andhra Pradesh, India 530045<br><br>
                <strong>Phone:</strong> +91 63054 85269<br>
                <strong>Email:</strong> clubhub@gmail.com<br>
              </p>
              <div class="social-links mt-3">
                <a href="#" class="twitter"><i class="bx bxl-twitter"></i></a>
                <a href="#" class="facebook"><i class="bx bxl-facebook"></i></a>
                <a href="#" class="instagram"><i class="bx bxl-instagram"></i></a>
                <a href="#" class="google-plus"><i class="bx bxl-skype"></i></a>
                <a href="#" class="linkedin"><i class="bx bxl-linkedin"></i></a>
              </div>
            </div>
          </div>

          <div class="col-lg-2 col-md-6 footer-links">
            <h4>Useful Links</h4>
            <ul>
              <li><i class="bx bx-chevron-right"></i> <a href="#">Home</a></li>
              <li><i class="bx bx-chevron-right"></i> <a href="#">About us</a></li>
              <li><i class="bx bx-chevron-right"></i> <a href="#">Clubs</a></li>
              <li><i class="bx bx-chevron-right"></i> <a href="#">Terms of service</a></li>
              <li><i class="bx bx-chevron-right"></i> <a href="#">Privacy policy</a></li>
            </ul>
          </div>

          <div class="col-lg-2 col-md-6 footer-links">
            <h4>Club Branches</h4>
            <ul>
              <li><i class="bx bx-chevron-right"></i> <a href="https://www.gitam.edu/gitam-school-science-visakhapatnam">School of Science</a></li>
              <li><i class="bx bx-chevron-right"></i> <a href="https://www.gitam.edu/gitam-school-pharmacy-visakhapatnam">School of Pharmacy</a></li>
              <li><i class="bx bx-chevron-right"></i> <a href="https://www.gitam.edu/gitam-school-of-technology-visakhapatnam">School of Technology</a></li>
              <li><i class="bx bx-chevron-right"></i> <a href="https://www.gitam.edu/gitam-school-law-visakhapatnam">School of Law</a></li>
              <li><i class="bx bx-chevron-right"></i> <a href="https://www.gitam.edu/gitam-school-business-visakhapatnam">School of Business</a></li>
            </ul>
          </div>

          <div class="col-lg-4 col-md-6 footer-newsletter">
            <h4>Our Newsletter</h4>
            <p>To get updates on latest events, please subscribe</p>
            <form action="" method="post">
              <input type="email" name="email"><input type="submit" value="Subscribe">
            </form>

          </div>

        </div>
      </div>
    </div>

    <div class="container">
      <div class="copyright">
        &copy; Copyright <strong><span>ClubHub</span></strong>. All Rights Reserved
      </div>
      <div class="credits">
        <!-- All the links in the footer should remain intact. -->
        <!-- You can delete the links only if you purchased the pro version. -->
        <!-- Licensing information: https://bootstrapmade.com/license/ -->
        <!-- Purchase the pro version with working PHP/AJAX contact form: https://bootstrapmade.com/squadfree-free-bootstrap-template-creative/ -->
        Designed by <a href="https://bootstrapmade.com/">Vybhav</a>
      </div>
    </div>
  </footer><!-- End Footer -->

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="Club/assets/vendor/purecounter/purecounter_vanilla.js"></script>
  <script src="Club/assets/vendor/aos/aos.js"></script>
  <script src="Club/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="Club/assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="Club/assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
  <script src="Club/assets/vendor/swiper/swiper-bundle.min.js"></script>
  <script src="Club/assets/vendor/php-email-form/validate.js"></script>

  <!-- Template Main JS File -->
  <script src="Club/assets/js/main.js"></script>
<img src="https://www.gitam.edu/sites/default/files/2022-09/Rotaract-Club-of-GITAM.jpg">
</body>

</html>
