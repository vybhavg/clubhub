<?php
include('/var/www/html/db_connect.php'); // Include your database connection

// Start the session
session_start();

// Check if student_id is set in the session
if (!isset($_SESSION['student_id'])) {
    die('Student ID is not available. Please log in again.');
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
        h1 {
            font-size: 2.5em;
            margin-bottom: 20px;
        }
        .message {
            margin: 20px 0;
            font-size: 1.5em;
            background-color: rgba(255, 255, 255, 0.2);
            padding: 20px;
            border-radius: 15px;
            display: inline-block;
            max-width: 600px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }
        img {
            width: 300px; /* Adjusted size */
            height: auto;
            margin-bottom: 20px;
        }
        .countdown {
            display: flex;
            justify-content: space-around;
            margin-top: 20px;
        }
        .time-wrapper {
            flex: 1;
            text-align: center;
        }
        .time {
            font-size: 2.5em;
        }
        .metric {
            font-size: 1.2em;
        }
        .button {
            background-color: #28a745; /* Button color */
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
            transition: background-color 0.3s ease;
        }
        .button:hover {
            background-color: #218838; /* Darker green on hover */
        }
        @media (max-width: 600px) {
            h1 {
                font-size: 2em;
            }
            .message {
                font-size: 1.2em;
            }
            .time {
                font-size: 2em;
            }
            .metric {
                font-size: 1em;
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
                    document.querySelector(".days").innerHTML = days;
                    document.querySelector(".hours").innerHTML = hours;
                    document.querySelector(".minutes").innerHTML = minutes;
                    document.querySelector(".seconds").innerHTML = seconds;
                } else if (timeUntilEnd > 0) {
                    const days = Math.floor(timeUntilEnd / 86400);
                    const hours = Math.floor((timeUntilEnd % 86400) / 3600);
                    const minutes = Math.floor((timeUntilEnd % 3600) / 60);
                    const seconds = timeUntilEnd % 60;
                    document.querySelector(".days").innerHTML = days;
                    document.querySelector(".hours").innerHTML = hours;
                    document.querySelector(".minutes").innerHTML = minutes;
                    document.querySelector(".seconds").innerHTML = seconds;
                } else {
                    clearInterval(interval);
                    document.querySelector(".message").innerHTML = "The event has concluded!";
                }
            }, 1000);
        }
    </script>
</head>
<body>';

echo '<img src="https://media.tenor.com/0HwF_YNt5coAAAAj/school-penguin.gif" alt="Celebration GIF"/>';
echo '<h1>' . htmlspecialchars($event_title) . ' is Approaching!</h1>';
echo '<div class="message">';

if ($distance_to_event <= $geofence_radius) {
    echo "<p>You are currently within the geofence!</p>";

    // Always show the event status and timer
    if ($current_time_timestamp < $event_start_timestamp) {
        echo "<p>Attendance is not yet permitted. Please check back shortly.</p>";
        echo '<section class="countdown">
                <div class="time-wrapper">
                    <div class="time-content">
                        <div class="time">
                            <span class="days">0</span>
                        </div>
                    </div>
                    <p class="metric">Days</p>
                </div>
                <div class="time-wrapper">
                    <div class="time-content">
                        <div class="time">
                            <span class="hours">0</span>
                        </div>
                    </div>
                    <p class="metric">Hours</p>
                </div>
                <div class="time-wrapper">
                    <div class="time-content">
                        <div class="time">
                            <span class="minutes">0</span>
                        </div>
                    </div>
                    <p class="metric">Minutes</p>
                </div>
                <div class="time-wrapper">
                    <div class="time-content">
                        <div class="time">
                            <span class="seconds">0</span>
                        </div>
                    </div>
                    <p class="metric">Seconds</p>
                </div>
              </section>';
        echo "<script>startTimer(" . $event_start_timestamp . ", " . $event_end_timestamp . ");</script>";
    } elseif ($current_time_timestamp < $event_end_timestamp) {
        echo "<p>The event is currently in progress!</p>";
        echo '<section class="countdown">
                <div class="time-wrapper">
                    <div class="time-content">
                        <div class="time">
                            <span class="days">0</span>
                        </div>
                    </div>
                    <p class="metric">Days</p>
                </div>
                <div class="time-wrapper">
                    <div class="time-content">
                        <div class="time">
                            <span class="hours">0</span>
                        </div>
                    </div>
                    <p class="metric">Hours</p>
                </div>
                <div class="time-wrapper">
                    <div class="time-content">
                        <div class="time">
                            <span class="minutes">0</span>
                        </div>
                    </div>
                    <p class="metric">Minutes</p>
                </div>
                <div class="time-wrapper">
                    <div class="time-content">
                        <div class="time">
                            <span class="seconds">0</span>
                        </div>
                    </div>
                    <p class="metric">Seconds</p>
                </div>
              </section>';
        echo "<script>startTimer(" . $event_start_timestamp . ", " . $event_end_timestamp . ");</script>";
    } else {
        echo "<p>Attendance is no longer allowed as the event has concluded.</p>";
    }
} else {
    echo "<p>Unfortunately, you are outside the geofence radius for this event.</p>";
}
echo '</div>';
echo '</body>';
echo '</html>';
?>
