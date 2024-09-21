<?php
include('/var/www/html/db_connect.php'); // Include your database connection

// Start the session
session_start();

// Check if student_id is set in the session
if (!isset($_SESSION['student_id'])) {
    die('Student ID not found. Please log in.');
}

// Get data from the form and cast to appropriate types
$student_id = (int) $_POST['student_id']; // Cast to integer
$event_id = (int) $_POST['event_id']; // Cast to integer
$user_latitude = isset($_POST['latitude']) ? (float) $_POST['latitude'] : null;  // Cast to float, use null if not set
$user_longitude = isset($_POST['longitude']) ? (float) $_POST['longitude'] : null; // Cast to float, use null if not set
$name = isset($_POST['student_name']) ? trim($_POST['student_name']) : ''; // Trim and sanitize student name
$email = isset($_POST['student_email']) ? filter_var(trim($_POST['student_email']), FILTER_SANITIZE_EMAIL) : ''; // Trim, sanitize and validate student email

// Fetch the event details from the database
$stmt = $conn->prepare("SELECT title, event_start_time, event_end_time, latitude, longitude, attendance_allowed, button_access_time FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$stmt->bind_result($event_title, $event_start_time, $event_end_time, $event_latitude, $event_longitude, $attendance_allowed, $button_access_time);
$stmt->fetch();
$stmt->close();

// Ensure event latitude and longitude are cast to float
$event_latitude = (float) $event_latitude;
$event_longitude = (float) $event_longitude;

// Geofence parameters
$geofence_radius = 1.0; // 1 km radius (adjusted to km)

// Haversine formula to calculate the distance between two GPS coordinates
function haversine_distance($lat1, $lon1, $lat2, $lon2) {
    $earth_radius = 6371;  // Earth radius in kilometers
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return $earth_radius * $c;
}

// Calculate the distance between the event location and the user's location
$distance_to_event = haversine_distance($user_latitude, $user_longitude, $event_latitude, $event_longitude);

// Set server time to IST
$server_timezone = new DateTimeZone('UTC'); // Assuming server is in UTC
$ist_timezone = new DateTimeZone('Asia/Kolkata');

// Get current server time and convert to IST
$current_time = new DateTime('now', $server_timezone);
$current_time->setTimezone($ist_timezone);
$current_time_timestamp = $current_time->getTimestamp(); // Use this timestamp for time calculations

// Convert event start and end times to timestamps
$event_start_time_ist = new DateTime($event_start_time, $ist_timezone);
$event_end_time_ist = new DateTime($event_end_time, $ist_timezone);
$event_start_timestamp = $event_start_time_ist->getTimestamp();
$event_end_timestamp = $event_end_time_ist->getTimestamp();

// Function to start the timer
echo '<script>
function startTimer(startTimestamp, endTimestamp) {
    var timerElement = document.getElementById("timer");
    var interval = setInterval(function() {
        var now = Math.floor(Date.now() / 1000);
        var remaining;

        if (now < startTimestamp) {
            remaining = startTimestamp - now;
            timerElement.innerHTML = "Event starts in: " + formatTime(remaining);
        } else if (now < endTimestamp) {
            remaining = endTimestamp - now;
            timerElement.innerHTML = "Event ends in: " + formatTime(remaining);
        } else {
            clearInterval(interval);
            timerElement.innerHTML = "The event has ended.";
        }
    }, 1000);

    function formatTime(seconds) {
        var hours = Math.floor(seconds / 3600);
        var minutes = Math.floor((seconds % 3600) / 60);
        var secs = seconds % 60;
        return hours + "h " + minutes + "m " + secs + "s";
    }
}
</script>';

// CSS for styling
echo '<style>
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    color: #333;
    margin: 20px;
}

p {
    font-size: 18px;
    margin: 10px 0;
}

.timer {
    font-weight: bold;
    font-size: 24px;
    color: #ff5733; /* Timer color */
}

.button {
    background-color: #28a745; /* Button color */
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
}

.button:hover {
    background-color: #218838; /* Darker green on hover */
}
</style>';

// Display event status and timer
if ($distance_to_event <= $geofence_radius) {
    echo "<p>You are within the geofence!</p>";

    // Always show the event status and timer
    if ($current_time_timestamp < $event_start_timestamp) {
        echo "<p>Attendance is not yet allowed. Please check back later.</p>";
        echo "<p>It will begin in: <span id='timer' class='timer'></span></p>";
        echo "<script>startTimer(" . $event_start_timestamp . ", " . $event_end_timestamp . ");</script>";
    } elseif ($current_time_timestamp < $event_end_timestamp) {
        echo "<p>The event is currently live!</p>";
        echo "<p>It will end in: <span id='timer' class='timer'></span></p>";
        echo "<script>startTimer(" . $event_start_timestamp . ", " . $event_end_timestamp . ");</script>";
        
        // Show the Confirm Attendance button only if attendance is allowed
        if ($attendance_allowed) {
            echo '<form method="post" action="confirm_attendance.php">
                    <input type="hidden" name="student_id" value="' . htmlspecialchars($student_id) . '">
                    <input type="hidden" name="event_id" value="' . htmlspecialchars($event_id) . '">
                    <input type="hidden" name="latitude" value="' . htmlspecialchars($user_latitude) . '">
                    <input type="hidden" name="longitude" value="' . htmlspecialchars($user_longitude) . '">
                    <input type="hidden" name="student_name" value="' . htmlspecialchars($name) . '">
                    <input type="hidden" name="student_email" value="' . htmlspecialchars($email) . '">
                    <button type="submit" class="button">Confirm Attendance</button>
                  </form>';
        }
    } else {
        echo "<p>The event has ended.</p>";
    }
} else {
    echo "<p>You're currently outside the geofence. The 'Confirm Attendance' button will be available once you are inside.</p>";
}

// Close the database connection
$conn->close();
?>
