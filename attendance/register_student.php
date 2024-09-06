<?php
include('/var/www/html/db_connect.php');

// Get data from the form
$name = $_POST['name'];
$email = $_POST['email'];
$user_latitude = $_POST['latitude'];
$user_longitude = $_POST['longitude'];
$event_id = $_POST['event_id'];

// Get the user's IP address
$ip_address = $_SERVER['REMOTE_ADDR'];

// Fetch the event details from the database
$stmt = $conn->prepare("SELECT title, event_start_time, event_duration, latitude, longitude FROM forms WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$stmt->bind_result($event_title, $event_start_time, $event_duration, $event_latitude, $event_longitude);
$stmt->fetch();
$stmt->close();

// Geofence parameters (you can adjust the range if needed)
$geofence_radius = 1.0; // 1 km radius

// Function to calculate the distance between two GPS coordinates (Haversine formula)
function haversine_distance($lat1, $lon1, $lat2, $lon2) {
    $earth_radius = 6371;  // Earth radius in kilometers

    // Convert degrees to radians
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    // Haversine formula
    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    // Calculate the distance
    return $earth_radius * $c;
}

// Calculate the distance between the event location and the user's location
$distance_to_event = haversine_distance($user_latitude, $user_longitude, $event_latitude, $event_longitude);

// Time Zone conversion
$ist_timezone = new DateTimeZone('Asia/Kolkata');
$us_east_timezone = new DateTimeZone('America/New_York');

// Convert event start time from IST to US-East
$event_start_time_ist = new DateTime($event_start_time, $ist_timezone);
$event_start_time_us_east = $event_start_time_ist->setTimezone($us_east_timezone);

// Add the event duration to the event start time (in US-East time zone)
$event_end_time_us_east = clone $event_start_time_us_east;
$event_end_time_us_east->add(new DateInterval('PT' . $event_duration . 'M')); // Add event duration in minutes

// Get the current server time (in US-East time zone)
$current_time_us_east = new DateTime('now', $us_east_timezone);

// Check if the user is within the geofence radius
if ($distance_to_event <= $geofence_radius) {
    if ($current_time_us_east >= $event_start_time_us_east && $current_time_us_east <= $event_end_time_us_east) {
        // User is within the event duration and geofence
        echo "<p>You are within the geofenced area and the event duration. Proceed with final registration.</p>";
        echo "<a href='final_registration.php?name=$name&email=$email&event_id=$event_id'>Complete Final Registration</a>";
    } else {
        // Event has not started or has ended
        $remaining_time = $event_start_time_us_east->getTimestamp() - $current_time_us_east->getTimestamp();
        if ($remaining_time > 0) {
            echo "<p>The event has not started yet. Please wait for " . gmdate("H:i:s", $remaining_time) . " until the event begins.</p>";
        } else {
            echo "<p>The event has ended. You cannot register now.</p>";
        }
    }
} else {
    // User is outside the geofenced area
    echo "<p>You are not within the geofenced area for the event. Please move closer to the event location to register.</p>";
}

// Insert the registration details into the 'registrations' table
$insert_stmt = $conn->prepare("INSERT INTO registrations (name, email, latitude, longitude, event_id, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
$insert_stmt->bind_param("sssdis", $name, $email, $user_latitude, $user_longitude, $event_id, $ip_address);
$insert_stmt->execute();
$insert_stmt->close();

$conn->close();
?>
