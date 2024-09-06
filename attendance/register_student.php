<?php
include('/var/www/html/db_connect.php');

// Get data from the form
$name = $_POST['name'];
$email = $_POST['email'];
$user_latitude = $_POST['latitude'];
$user_longitude = $_POST['longitude'];
$event_id = $_POST['event_id'];

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

// Check if the user is within the geofence radius
if ($distance_to_event <= $geofence_radius) {
    // Check if the current time is within the event duration
    $current_time = new DateTime();
    $event_start_time = new DateTime($event_start_time);
    $event_end_time = clone $event_start_time;
    $event_end_time->add(new DateInterval('PT' . $event_duration . 'M')); // Add event duration in minutes

    if ($current_time >= $event_start_time && $current_time <= $event_end_time) {
        // User is within the event duration and geofence
        echo "<p>You are within the geofenced area and the event duration. Proceed with final registration.</p>";
        echo "<a href='final_registration.php?name=$name&email=$email&event_id=$event_id'>Complete Final Registration</a>";
    } else {
        // User is outside the event time
        $remaining_time = $event_start_time->getTimestamp() - $current_time->getTimestamp();
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
$insert_stmt = $conn->prepare("INSERT INTO registrations (name, email, latitude, longitude, event_id) VALUES (?, ?, ?, ?, ?)");
$insert_stmt->bind_param("sssdi", $name, $email, $user_latitude, $user_longitude, $event_id);
$insert_stmt->execute();
$insert_stmt->close();

$conn->close();
?>
