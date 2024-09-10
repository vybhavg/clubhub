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
$stmt = $conn->prepare("SELECT title, latitude, longitude, button_access_time, event_start_time, event_end_time FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$stmt->bind_result($event_title, $event_latitude, $event_longitude, $button_access_time, $event_start_time, $event_end_time);
$stmt->fetch();
$stmt->close();

// Ensure event latitude and longitude are cast to float
$event_latitude = (float) $event_latitude;
$event_longitude = (float) $event_longitude;

// Handle cases where times are not available
if (is_null($button_access_time) || is_null($event_start_time) || is_null($event_end_time)) {
    die('Event times are not available.');
}

// Convert times to DateTime objects
$button_access_time_dt = new DateTime($button_access_time, new DateTimeZone('Asia/Kolkata'));
$event_start_time_dt = new DateTime($event_start_time, new DateTimeZone('Asia/Kolkata'));
$event_end_time_dt = new DateTime($event_end_time, new DateTimeZone('Asia/Kolkata'));

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

// Check if the user is within the geofence
if ($distance_to_event <= $geofence_radius) {
    // Get current time
    $current_time = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
    $current_timestamp = $current_time->format('Y-m-d H:i:s');

    // Calculate time difference
    $time_diff = $current_time->getTimestamp() - $button_access_time_dt->getTimestamp();

    // Check if the button access time is within the last 5 minutes
    if ($time_diff <= 300) { // 300 seconds = 5 minutes
        // Display "Confirm Attendance" button
        if ($current_time->getTimestamp() >= $event_start_time_dt->getTimestamp()) {
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
            echo "<p>The 'Confirm Attendance' button will be available once the event starts.</p>";
        }
    } else {
        echo "<p>The 'Confirm Attendance' button is no longer available. 5 minutes have passed since access was granted.</p>";
    }
} else {
    echo "<p>You are outside the geofence. The 'Confirm Attendance' button will not be available until you are within the geofence.</p>";
}

// Close the database connection
$conn->close();
?>
