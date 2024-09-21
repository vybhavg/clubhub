<?php
include('/var/www/html/db_connect.php'); // Include your database connection

// Start the session
session_start();

// Check if student_id is set in the session
if (!isset($_SESSION['student_id'])) {
    die('Student ID not found. Please log in.');
}

// Get data from the form and cast to appropriate types
$student_id = (int) $_POST['student_id'];
$event_id = (int) $_POST['event_id'];
$user_latitude = isset($_POST['latitude']) ? (float) $_POST['latitude'] : null;
$user_longitude = isset($_POST['longitude']) ? (float) $_POST['longitude'] : null;
$name = isset($_POST['student_name']) ? trim($_POST['student_name']) : '';
$email = isset($_POST['student_email']) ? filter_var(trim($_POST['student_email']), FILTER_SANITIZE_EMAIL) : '';

// Fetch the event details from the database
$stmt = $conn->prepare("SELECT title, event_start_time, event_end_time, latitude, longitude, attendance_allowed, button_access_time FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$stmt->bind_result($event_title, $event_start_time, $event_end_time, $event_latitude, $event_longitude, $attendance_allowed, $button_access_time);
$stmt->fetch();
$stmt->close();

$event_latitude = (float) $event_latitude;
$event_longitude = (float) $event_longitude;

// Geofence parameters
$geofence_radius = 1.0;

// Haversine formula
function haversine_distance($lat1, $lon1, $lat2, $lon2) {
    $earth_radius = 6371; // Earth radius in kilometers
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return $earth_radius * $c;
}

// Calculate distance
$distance_to_event = haversine_distance($user_latitude, $user_longitude, $event_latitude, $event_longitude);

// Set server time to IST
$server_timezone = new DateTimeZone('UTC');
$ist_timezone = new DateTimeZone('Asia/Kolkata');

// Get current time and event times
$current_time = new DateTime('now', $server_timezone);
$current_time->setTimezone($ist_timezone);
$current_time_timestamp = $current_time->getTimestamp();
$event_start_time_ist = new DateTime($event_start_time, $ist_timezone);
$event_end_time_ist = new DateTime($event_end_time, $ist_timezone);
$event_start_timestamp = $event_start_time_ist->getTimestamp();
$event_end_timestamp = $event_end_time_ist->getTimestamp();

// HTML Output
echo '<html>
<head>
    <style>
        body {
            background-color: #4b9abb;
            color: white;
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 20px;
        }
        h1 {
            font-size: 2.5em;
            margin-bottom: 20px;
        }
        .message {
            margin: 20px 0;
            font-size: 1.5em;
            background-color: rgba(255, 255, 255, 0.2);
            padding: 15px;
            border-radius: 10px;
            display: inline-block;
        }
        img {
            width: 200px;
            height: auto;
            margin-bottom: 20px;
        }
        .timer {
            font-size: 2em;
            background-color: rgba(255, 255, 255, 0.2);
            padding: 10px;
            border-radius: 10px;
            display: inline-block;
            margin-top: 20px;
        }
    </style>
    <script>
        function startTimer(eventStartTime, eventEndTime) {
            const interval = setInterval(() => {
                const now = Math.floor(Date.now() / 1000);
                const timeUntilStart = eventStartTime - now;
                const timeUntilEnd = eventEndTime - now;

                if (timeUntilStart > 0) {
                    const minutes = Math.floor((timeUntilStart % 3600) / 60);
                    const seconds = timeUntilStart % 60;
                    document.getElementById("timer").innerHTML = "Starts in: " + minutes + "m " + seconds + "s";
                } else if (timeUntilEnd > 0) {
                    const minutes = Math.floor((timeUntilEnd % 3600) / 60);
                    const seconds = timeUntilEnd % 60;
                    document.getElementById("timer").innerHTML = "Ends in: " + minutes + "m " + seconds + "s";
                } else {
                    clearInterval(interval);
                    document.getElementById("timer").innerHTML = "Event has ended!";
                }
            }, 1000);
        }
    </script>
</head>
<body>';

echo '<img src="https://media.tenor.com/5miqL4qPOGgAAAAj/school-book.gif" alt="Loading..."/>';
echo '<h1>Event Status</h1>';
echo '<div class="message">';

if ($distance_to_event <= $geofence_radius) {
    echo "<p>You are within the geofence!</p>";

    if ($attendance_allowed) {
        if ($current_time_timestamp < $event_start_timestamp) {
            echo "<p>The event is not yet started. Please check back later.</p>";
            echo "<p>It will begin in: <span id='timer' class='timer'></span></p>";
            echo "<script>startTimer(" . $event_start_timestamp . ", " . $event_end_timestamp . ");</script>";
        } elseif ($current_time_timestamp < $event_end_timestamp) {
            echo "<p>The event is currently live!</p>";
            echo "<p>It will end in: <span id='timer' class='timer'></span></p>";
            echo "<script>startTimer(" . $event_start_timestamp . ", " . $event_end_timestamp . ");</script>";
        } else {
            echo "<p>The event has ended.</p>";
        }
    } else {
        echo "<p>Attendance is not yet allowed. Please check back later.</p>";
    }
} else {
    echo "<p>You're currently outside the geofence. The 'Confirm Attendance' button will be available once you are inside.</p>";
}

echo '</div></body></html>';

// Close the database connection
$conn->close();
?>
