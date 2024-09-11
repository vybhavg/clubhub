<?php
session_start();
include('/var/www/html/db_connect.php'); // Ensure this file connects to your database correctly
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the user is logged in and has a valid club_id
if (!isset($_SESSION['club_id'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit;
}

// Get session variables
$club_id = $_SESSION['club_id'];
$branch_id = $_SESSION['branch_id'];
$updateType = isset($_GET['update_type']) ? $_GET['update_type'] : 'events'; // Default to 'events'

// Initialize $club_name
$club_name = 'Club'; // Default value

// Fetch club name from the database
$stmt = $conn->prepare("SELECT club_name FROM clubs WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $club_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $club = $result->fetch_assoc();
        $club_name = htmlspecialchars($club['club_name']); // Sanitize output
    }
    $stmt->close();
} else {
    error_log("Prepare failed: " . $conn->error);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_event'])) {
        $title = $_POST['event_name'];
        $description = $_POST['event_description'];
        $latitude = $_POST['latitude']; // Latitude input
        $longitude = $_POST['longitude']; // Longitude input
        $event_start_time = $_POST['event_start_time']; // Event start time input (should be datetime-local format)
        $event_duration = intval($_POST['event_duration']); // Event duration input, ensure it's an integer

        if ($latitude && $longitude && $event_start_time && $event_duration) {
            // Calculate the event end time based on start time and duration in minutes
            $event_end_time = date('Y-m-d H:i:s', strtotime($event_start_time) + ($event_duration * 60));

            // Prepare and execute the statement to insert the event details
            $stmt = $conn->prepare("INSERT INTO events (title, description, latitude, longitude, event_start_time, event_duration, event_end_time, club_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("sssssisi", $title, $description, $latitude, $longitude, $event_start_time, $event_duration, $event_end_time, $club_id);
                if ($stmt->execute()) {
                    $_SESSION['message'] = "Event added successfully.";
                } else {
                    error_log("Execute failed: " . $stmt->error);
                    $_SESSION['message'] = "Error adding event.";
                }
                $stmt->close();
            } else {
                error_log("Prepare failed: " . $conn->error);
            }
        } else {
            $_SESSION['message'] = "Invalid data. Please ensure all fields are filled in.";
        }
    }
    // Add Recruitment
    elseif (isset($_POST['add_recruitment'])) {
        $role = $_POST['role'];
        $description = $_POST['recruitment_description'];
        $deadline = $_POST['deadline'];

        $stmt = $conn->prepare("INSERT INTO recruitments (role, description, deadline, club_id) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sssi", $role, $description, $deadline, $club_id);
            if (!$stmt->execute()) {
                error_log("Execute failed: " . $stmt->error);
                $_SESSION['message'] = "Error adding recruitment.";
            } else {
                $_SESSION['message'] = "Recruitment added successfully.";
            }
            $stmt->close();
        } else {
            error_log("Prepare failed: " . $conn->error);
        }
    }
    
// Handle the "Give Attendance" request
if (isset($_POST['give_attendance'])) {
    $event_id = (int) $_POST['event_id']; // Cast to integer

    // Get the current time for button access time
    $current_time = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
    $button_access_time = $current_time->format('Y-m-d H:i:s');

    // Prepare the SQL statement to update both attendance_allowed and button_access_time
    $stmt = $conn->prepare("
        UPDATE events 
        SET attendance_allowed = 1, button_access_time = ? 
        WHERE id = ?
    ");
    $stmt->bind_param("si", $button_access_time, $event_id);
    if ($stmt->execute()) {
        echo "<p>Attendance confirmation has been enabled for the selected event.</p>";
    } else {
        echo "<p>Failed to update event. Please try again.</p>";
    }
    $stmt->close();
}

    // Delete Event
    elseif (isset($_POST['delete_event'])) {
        $event_id = $_POST['event_id'];

        $stmt = $conn->prepare("DELETE FROM events WHERE id = ? AND club_id = ?");
        if ($stmt) {
            $stmt->bind_param("ii", $event_id, $club_id);
            if (!$stmt->execute()) {
                error_log("Execute failed: " . $stmt->error);
                $_SESSION['message'] = "Error deleting event.";
            } else {
                $_SESSION['message'] = "Event deleted successfully.";
            }
            $stmt->close();
        } else {
            error_log("Prepare failed: " . $conn->error);
        }
    }
    // Delete Recruitment
    elseif (isset($_POST['delete_recruitment'])) {
        $recruitment_id = $_POST['recruitment_id'];

        $stmt = $conn->prepare("DELETE FROM recruitments WHERE id = ? AND club_id = ?");
        if ($stmt) {
            $stmt->bind_param("ii", $recruitment_id, $club_id);
            if (!$stmt->execute()) {
                error_log("Execute failed: " . $stmt->error);
                $_SESSION['message'] = "Error deleting recruitment.";
            } else {
                $_SESSION['message'] = "Recruitment deleted successfully.";
            }
            $stmt->close();
        } else {
            error_log("Prepare failed: " . $conn->error);
        }
    }
    // Handle Application Accept/Reject
    elseif (isset($_POST['accept_application']) || isset($_POST['reject_application'])) {
        $application_id = $_POST['application_id'];
        $status = isset($_POST['accept_application']) ? 'accepted' : 'rejected';

        // Update the application status
        $stmt_update_application_status = $conn->prepare("UPDATE applications SET status = ? WHERE id = ?");
        if ($stmt_update_application_status) {
            $stmt_update_application_status->bind_param("si", $status, $application_id);
            if ($stmt_update_application_status->execute()) {
                if ($status == 'accepted') {
                    // Move accepted application to onboarding table
                    $stmt_fetch_application_details = $conn->prepare("SELECT student_id, club_id FROM applications WHERE id = ?");
                    if ($stmt_fetch_application_details) {
                        $stmt_fetch_application_details->bind_param("i", $application_id);
                        $stmt_fetch_application_details->execute();
                        $result = $stmt_fetch_application_details->get_result();
                        if ($result->num_rows > 0) {
                            $application = $result->fetch_assoc();
                            $student_id = $application['student_id'];
                            $club_id = $application['club_id'];

                            $stmt_insert_onboarding = $conn->prepare("INSERT INTO onboarding (student_id, club_id) VALUES (?, ?)");
                            if ($stmt_insert_onboarding) {
                                $stmt_insert_onboarding->bind_param("ii", $student_id, $club_id);
                                if (!$stmt_insert_onboarding->execute()) {
                                    error_log("Error inserting into onboarding table: " . $stmt_insert_onboarding->error);
                                    $_SESSION['message'] = "Error moving application to onboarding.";
                                }
                                $stmt_insert_onboarding->close();
                            } else {
                                error_log("Prepare failed: " . $conn->error);
                            }
                        }
                        $stmt_fetch_application_details->close();
                    } else {
                        error_log("Prepare failed: " . $conn->error);
                    }
                }
                $_SESSION['message'] = "Application status updated successfully.";
            } else {
                error_log("Execute failed: " . $stmt_update_application_status->error);
                $_SESSION['message'] = "Error updating application status.";
            }
            $stmt_update_application_status->close();
        } else {
            error_log("Prepare failed: " . $conn->error);
        }
    }

    // Redirect to avoid form resubmission
    header("Location: ".$_SERVER['PHP_SELF']."?update_type=".$updateType);
    exit;
}
// Fetch ongoing events for the logged-in club
$current_time = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
$current_time_formatted = $current_time->format('Y-m-d H:i:s');



// Fetch events, recruitments, applications, and onboarding data for the logged-in club
$eventsResult = $conn->prepare("SELECT * FROM events WHERE club_id = ? AND event_end_time > ?");

$recruitmentsResult = $conn->prepare("SELECT * FROM recruitments WHERE club_id = ?");
$onboardingResult = $conn->prepare("SELECT o.id, s.name as student_name, s.email as email FROM onboarding o INNER JOIN students s ON o.student_id = s.id WHERE o.club_id = ?");

if ($eventsResult) {
    $eventsResult->bind_param("is", $club_id, $current_time_formatted);
    $eventsResult->execute();
    $eventsResult = $eventsResult->get_result();
} else {
    error_log("Prepare failed: " . $conn->error);
}

if ($recruitmentsResult) {
    $recruitmentsResult->bind_param("i", $club_id);
    $recruitmentsResult->execute();
    $recruitmentsResult = $recruitmentsResult->get_result();
} else {
    error_log("Prepare failed: " . $conn->error);
}



if ($onboardingResult) {
    $onboardingResult->bind_param("i", $club_id);
    $onboardingResult->execute();
    $onboardingResult = $onboardingResult->get_result();
} else {
    error_log("Prepare failed: " . $conn->error);
}
// Fetch pending applications
$stmt_fetch_pending_applications = $conn->prepare("SELECT a.id, s.name AS student_name, s.email, a.resume_path
    FROM applications a
    JOIN students s ON a.student_id = s.id
    WHERE a.status = 'pending' AND a.club_id = ?");
if ($stmt_fetch_pending_applications) {
    $stmt_fetch_pending_applications->bind_param("i", $club_id); // Assuming $club_id is set correctly
    $stmt_fetch_pending_applications->execute();
    $applicationsResult = $stmt_fetch_pending_applications->get_result();
    $stmt_fetch_pending_applications->close();
} else {
    error_log("Prepare failed: " . $conn->error);
    $_SESSION['message'] = "Error fetching applications.";
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
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
     <style>
        .form-container {
            max-width: 600px;
            margin: 5px auto;
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        #map {
            width: 100%; /* Adjust width to fit form container */
            height: 300px; /* Fixed height for square shape */
            border: 1px solid #ccc; /* Optional: border for better visibility */
            border-radius: 5px; /* Optional: rounded corners */
            margin-bottom: 20px; /* Space between map and form fields */
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
        }

        .btn-custom {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-custom:hover {
            background-color: #0056b3;
        }
    </style>
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
        <img src="assets/img/hero-bg.jpg" alt="" data-aos="fade-in">
        <div class="container text-center" data-aos="fade-up" data-aos-delay="100">
            <div class="cont">
                <h2>Welcome, <?php echo htmlspecialchars($club_name ?: 'Club'); ?> Club Members!</h2>
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
            <p>Manage and view the events here.</p>
        </div>
    </section><!-- /Events Section -->

   
        <!-- Form Container for Adding Events -->
        <div class="form-container">
            <div id="map"></div>
            <form method="post" action="">
                <div class="form-group">
                    <label for="event_name">Event Name:</label>
                    <input type="text" name="event_name" id="event_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="event_description">Description:</label>
                    <textarea name="event_description" id="event_description" class="form-control" required></textarea>
                </div>
                <div class="form-group">
    <label for="event_start_time">Start Time:</label>
    <input type="datetime-local" name="event_start_time" id="event_start_time" class="form-control" required>
</div>

                <div class="form-group">
                    <label for="event_duration">Duration (minutes):</label>
                    <input type="number" name="event_duration" id="event_duration" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="latitude">Latitude:</label>
                    <input type="text" name="latitude" id="latitude" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label for="longitude">Longitude:</label>
                    <input type="text" name="longitude" id="longitude" class="form-control" readonly>
                </div>
                <button type="submit" name="add_event" class="btn btn-custom">Submit Event</button>
            </form>
        </div>
    

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        function initMap() {
            var defaultLocation = { lat: 17.782067586690925, lng: 83.37835326649015 }; // Default location
            var map = L.map('map').setView(defaultLocation, 15);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            var marker = L.marker(defaultLocation, { draggable: true }).addTo(map);

            // Update latitude and longitude fields when marker is dragged
            marker.on('dragend', function (e) {
                var lat = e.target.getLatLng().lat.toFixed(8);
                var lng = e.target.getLatLng().lng.toFixed(8);
                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;
            });

            // Initialize latitude and longitude fields with default marker location
            document.getElementById('latitude').value = defaultLocation.lat.toFixed(8);
            document.getElementById('longitude').value = defaultLocation.lng.toFixed(8);
        }

        window.onload = initMap;
    </script>
    
<!-- Existing Events List -->
<section id="faq" class="faq section light-background">
    <div class="container section-title" data-aos="fade-up">
        <h2>Existing Events</h2>
        <p>Here are the existing events.</p>
    </div>
    <div class="container">
        <div class="row faq-item" data-aos="fade-up" data-aos-delay="100">
            <div class="col-lg-12">
                <ul class="list-group">
                    <?php 
                    if ($eventsResult && $eventsResult->num_rows > 0) {
                        while ($event = $eventsResult->fetch_assoc()) { ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?php echo htmlspecialchars($event['title']); ?>: <?php echo htmlspecialchars($event['description']); ?>
                                <div class="d-flex">
                                    <!-- Give Attendance Button -->
                                    <form method="post" class="mr-2">
                                        <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($event['id']); ?>">
                                        <button type="submit" name="give_attendance" class="btn btn-primary btn-sm">Give Attendance</button>
                                    </form>
                                    <!-- Delete Event Button -->
                                    <form method="post">
                                        <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($event['id']); ?>">
                                        <button type="submit" name="delete_event" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </div>
                            </li>
                        <?php }
                    } else {
                        echo "<li class='list-group-item'>No events available</li>";
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>
</section><!-- /Faq Section -->



    <?php } elseif ($updateType == 'recruitments') { ?>
        <!-- Recruitments Section -->
        <section id="recruitments" class="about section">
            <div class="container section-title" data-aos="fade-up">
                <h2>Recruitments</h2>
                <p>Manage and view the recruitments here.</p>
            </div>
        </section><!-- /Recruitments Section -->

        <div class="form-container">
            <form method="post" class="mb-4">
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
                <input type="hidden" name="club_id" value="<?php echo htmlspecialchars($club_id); ?>">
                <button type="submit" name="add_recruitment" class="btn btn-custom">Add Recruitment</button>
            </form>

            <!-- Existing Recruitments List -->
            <section id="faq" class="faq section light-background">
                <div class="container section-title" data-aos="fade-up">
                    <h2>Current Recruitments</h2>
                    <p>Here are the current recruitment opportunities.</p>
                </div>
                <div class="container">
                    <div class="row faq-item" data-aos="fade-up" data-aos-delay="100">
                        <div class="col-lg-12">
                            <ul class="list-group">
                                <?php 
                                if ($recruitmentsResult && $recruitmentsResult->num_rows > 0) {
                                    while ($recruitment = $recruitmentsResult->fetch_assoc()) { ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <?php echo htmlspecialchars($recruitment['role']); ?>: <?php echo htmlspecialchars($recruitment['description']); ?>
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
                </div>
            </section><!-- /Faq Section -->
        </div>

<?php } elseif ($updateType == 'applications') { ?>
    <!-- Applications Section -->
    <section id="applications" class="about section">
        <div class="container section-title" data-aos="fade-up">
            <h2>Applications</h2>
            <p>View and manage student applications here.</p>
        </div>
    </section><!-- /Applications Section -->

    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h3>Applications for Your Club</h3>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Email</th>
                            <th>Resume</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($applicationsResult && $applicationsResult->num_rows > 0) {
                            while ($application = $applicationsResult->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($application['student_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($application['email'] ?? 'N/A'); ?></td>
                                    <td><a href="http://18.212.212.22/<?php echo htmlspecialchars($application['resume_path'] ?? ''); ?>" class="btn btn-info" target="_blank">View Resume</a></td>
                                    <td>
                                        <form method="POST" action="">
                                            <input type="hidden" name="application_id" value="<?php echo htmlspecialchars($application['id'] ?? ''); ?>">
                                            <button type="submit" name="accept_application" class="btn btn-success">Accept</button>
                                            <button type="submit" name="reject_application" class="btn btn-danger">Reject</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php }
                        } else {
                            echo "<tr><td colspan='4'>No applications available</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php } ?>

<?php
// Ensure this code is placed within your PHP script where it handles different update types

if ($updateType == 'onboarding') { ?>
    <!-- Onboarding Section -->
    <section id="onboarding" class="about section">
        <div class="container section-title" data-aos="fade-up">
            <h2>Onboarding</h2>
            <p>View and manage students who have been onboarded to your club.</p>
        </div>
    </section><!-- /Onboarding Section -->

    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h3>Onboarded Students for Your Club</h3>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($onboardingResult && $onboardingResult->num_rows > 0) {
                            while ($onboarded = $onboardingResult->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($onboarded['student_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($onboarded['email'] ?? 'N/A'); ?></td>
                                </tr>
                            <?php }
                        } else {
                            echo "<tr><td colspan='3'>No onboarded students available</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
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

</body>

</html>
 
 
