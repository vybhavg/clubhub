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
$stmt = $conn->prepare("SELECT title, event_start_time, event_end_time, latitude, longitude, attendance_allowed FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$stmt->bind_result($event_title, $event_start_time, $event_end_time, $event_latitude, $event_longitude, $attendance_allowed);
$stmt->fetch();
$stmt->close();

$event_latitude = (float) $event_latitude;
$event_longitude = (float) $event_longitude;

// Geofence parameters
$geofence_radius = 1.0; // 1 km radius

// Haversine formula to calculate distance
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
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .caption {
            font-size: 2.5em;
            margin-bottom: 20px;
        }
        .countdown {
            display: flex;
            justify-content: space-around;
            gap: 20px;
            margin-top: 30px;
        }
        .time-wrapper {
            text-align: center;
        }
        .time-content {
            position: relative;
            display: inline-block;
        }
        .time {
            font-size: 4em;
            background-color: rgba(255, 255, 255, 0.2);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }
        .metric {
            font-size: 1.5em;
            margin-top: 10px;
            color: #f0e68c;
        }
        .rings {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 5px solid #f0e68c;
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            animation: rotate 4s infinite linear;
        }
        @keyframes rotate {
            0% {
                transform: translateX(-50%) rotate(0deg);
            }
            100% {
                transform: translateX(-50%) rotate(360deg);
            }
        }
    </style>
    <script>
        function startTimer(eventStartTime, eventEndTime) {
            const interval = setInterval(() => {
                const now = Math.floor(Date.now() / 1000);
                const timeUntilStart = eventStartTime - now;
                const timeUntilEnd = eventEndTime - now;

                if (timeUntilStart > 0) {
                    const days = Math.floor(timeUntilStart / 86400);
                    const hours = Math.floor((timeUntilStart % 86400) / 3600);
                    const minutes = Math.floor((timeUntilStart % 3600) / 60);
                    const seconds = timeUntilStart % 60;
                    document.getElementById("days").innerText = days;
                    document.getElementById("hours").innerText = hours;
                    document.getElementById("minutes").innerText = minutes;
                    document.getElementById("seconds").innerText = seconds;
                } else if (timeUntilEnd > 0) {
                    const days = Math.floor(timeUntilEnd / 86400);
                    const hours = Math.floor((timeUntilEnd % 86400) / 3600);
                    const minutes = Math.floor((timeUntilEnd % 3600) / 60);
                    const seconds = timeUntilEnd % 60;
                    document.getElementById("days").innerText = days;
                    document.getElementById("hours").innerText = hours;
                    document.getElementById("minutes").innerText = minutes;
                    document.getElementById("seconds").innerText = seconds;
                } else {
                    clearInterval(interval);
                    document.getElementById("timer").innerHTML = "Event has ended!";
                }
            }, 1000);
        }
    </script>
</head>
<body>
    <div class="container">
        <h1 class="caption">Event Status: ' . htmlspecialchars($event_title) . '</h1>
        <section class="countdown" id="timer">
            <div class="time-wrapper">
                <div class="time-content">
                    <div class="time">
                        <span class="days" id="days">00</span>
                        <div class="rings"></div>
                    </div>
                </div>
                <p class="metric">Days</p>
            </div>

            <div class="time-wrapper">
                <div class="time-content">
                    <div class="time">
                        <span class="hours" id="hours">00</span>
                        <div class="rings"></div>
                    </div>
                </div>
                <p class="metric">Hours</p>
            </div>

            <div class="time-wrapper">
                <div class="time-content">
                    <div class="time">
                        <span class="minutes" id="minutes">00</span>
                        <div class="rings"></div>
                    </div>
                </div>
                <p class="metric">Minutes</p>
            </div>

            <div class="time-wrapper">
                <div class="time-content">
                    <div class="time">
                        <span class="seconds" id="seconds">00</span>
                        <div class="rings"></div>
                    </div>
                </div>
                <p class="metric">Seconds</p>
            </div>
        </section>';

if ($distance_to_event <= $geofence_radius) {
    if ($current_time_timestamp < $event_start_timestamp) {
        echo "<p>Attendance is not yet allowed. Please check back later.</p>";
        echo "<script>startTimer(" . $event_start_timestamp . ", " . $event_end_timestamp . ");</script>";
    } elseif ($current_time_timestamp < $event_end_timestamp) {
        echo "<p>The event is currently live!</p>";
        echo "<script>startTimer(" . $event_start_timestamp . ", " . $event_end_timestamp . ");</script>";

        // Show the Confirm Attendance button only if attendance is allowed
        if ($attendance_allowed) {
            echo '<form method="post" action="confirm_attendance.php">
                    <input type="hidden" name="student_id" value="' . htmlspecialchars($student_id) . '">
                    <input type="hidden" name="event_id" value="' . htmlspecialchars($event_id) . '">
                    <input type="hidden" name="latitude" value="' . htmlspecialchars($user_latitude) . '">
                    <input type="hidden" name="longitude" value="' . htmlspecialchars($user_longitude) . '">
                    <button type="submit">Confirm Attendance</button>
                </form>';
        }
    } else {
        echo "<p>The event has ended. Thank you for your interest!</p>";
    }
} else {
    echo "<p>You are outside the event location. Please check your coordinates.</p>";
}

echo '
        </div>
    </body>
</html>';
?>
