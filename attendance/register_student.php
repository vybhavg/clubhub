<?php
include('/var/www/html/db_connect.php');

// Get data from the form
$name = $_POST['name'];
$email = $_POST['email'];
$latitude = $_POST['latitude'];
$longitude = $_POST['longitude'];
$event_id = $_POST['event_id'];

// Fetch the event details from the database
$stmt = $conn->prepare("SELECT title, event_start_time, event_duration, latitude, longitude FROM forms WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$stmt->bind_result($event_title, $event_start_time, $event_duration, $event_lat, $event_lng);
$stmt->fetch();
$stmt->close();

// Convert event start time and duration to DateTime objects
$event_start_time = new DateTime($event_start_time);
$event_end_time = clone $event_start_time;
$event_end_time->add(new DateInterval("PT" . $event_duration . "M"));

// Get the current time in UTC
$current_time = new DateTime("now", new DateTimeZone("UTC"));

// Calculate the distance between the student's location and the event location (Haversine formula)
$earth_radius = 6371; // in kilometers

$lat_diff = deg2rad($event_lat - $latitude);
$lng_diff = deg2rad($event_lng - $longitude);

$a = sin($lat_diff / 2) * sin($lat_diff / 2) +
     cos(deg2rad($latitude)) * cos(deg2rad($event_lat)) *
     sin($lng_diff / 2) * sin($lng_diff / 2);
$c = 2 * atan2(sqrt($a), sqrt(1 - $a));

$distance = $earth_radius * $c; // Distance in kilometers

// Define geofence radius (in kilometers)
$geofence_radius = 0.1; // 100 meters

// Check if the student is within the geofence and event time
if ($distance <= $geofence_radius && $current_time >= $event_start_time && $current_time <= $event_end_time) {
    echo "Registration successful!";

    // Show final registration link
    echo "<p>Event: $event_title</p>";
    echo "<p><a href='final_registration.php?event_id=$event_id'>Complete Final Registration</a></p>";
} else {
    echo "You are not within the event's time or geofence area.";
}

$conn->close();
?>
