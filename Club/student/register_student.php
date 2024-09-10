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

// Convert event start and end times to IST
$event_start_time_ist = new DateTime($event_start_time, $ist_timezone);
$event_end_time_ist = new DateTime($event_end_time, $ist_timezone);

// Calculate time differences
$time_until_start = $event_start_time_ist->getTimestamp() - $current_time_timestamp;
$time_until_end = $event_end_time_ist->getTimestamp() - $current_time_timestamp;

// Function to format time differences
function format_time($seconds) {
    $days = floor($seconds / 86400);
    $hours = floor(($seconds % 86400) / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $seconds = $seconds % 60;

    $formatted = '';
    if ($days > 0) $formatted .= $days . ' days ';
    if ($hours > 0) $formatted .= $hours . ' hours ';
    if ($minutes > 0) $formatted .= $minutes . ' minutes ';
    if ($seconds > 0) $formatted .= $seconds . ' seconds ';

    return $formatted ?: '0 seconds';
}

// Display event times and time left
echo "<p>Event Start Time (IST): " . $event_start_time_ist->format('Y-m-d H:i:s') . "</p>";
echo "<p>Time until Event Starts: " . format_time(max($time_until_start, 0)) . "</p>";
echo "<p>Event End Time (IST): " . $event_end_time_ist->format('Y-m-d H:i:s') . "</p>";
echo "<p>Time until Event Ends: " . format_time(max($time_until_end, 0)) . "</p>";

// Check if the user is within the geofence
if ($distance_to_event <= $geofence_radius) {
    echo "<p>You are within the geofence.</p>";

    // Check if button access time is set and calculate if within 5 minutes window
    if ($attendance_allowed) {
        $button_access_time_ist = new DateTime($button_access_time, $ist_timezone);
        $button_access_time_timestamp = $button_access_time_ist->getTimestamp();
        $time_since_button_access = $current_time_timestamp - $button_access_time_timestamp;
        $five_minutes = 5 * 60; // 5 minutes in seconds

        if ($time_since_button_access <= $five_minutes) {
            // Display "Confirm Attendance" button if the current time is within 5 minutes of button access time
            if ($current_time_timestamp >= $event_start_time_ist->getTimestamp()) {
                echo '<form method="post" action="confirm_attendance.php">
                        <input type="hidden" name="student_id" value="' . htmlspecialchars($student_id) . '">
                        <input type="hidden" name="event_id" value="' . htmlspecialchars($event_id) . '">
                        <input type="hidden" name="latitude" value="' . htmlspecialchars($user_latitude) . '">
                        <input type="hidden" name="longitude" value="' . htmlspecialchars($user_longitude) . '">
                        <input type="hidden" name="student_name" value="' . htmlspecialchars($name) . '">
                        <input type="hidden" name="student_email" value="' . htmlspecialchars($email) . '">
                        <button type="submit">Confirm Attendance</button>
                      </form>';
            } else {
                echo "<p>The 'Confirm Attendance' button will be available once the event starts and attendance is allowed.</p>";
            }
        } else {
            echo "<p>The 'Confirm Attendance' button is no longer available as the 5-minute window has passed.</p>";
        }
    } else {
        echo "<p>The 'Confirm Attendance' button will be available once attendance is allowed.</p>";
    }
} else {
    echo "<p>You are outside the geofence. The 'Confirm Attendance' button will not be available until you are within the geofence.</p>";
}

// Close the database connection
$conn->close();
?>
