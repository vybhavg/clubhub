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

// Registration logic here...
// For now, checking the geofence and timing logic, etc.

if (/* Student is within geofence and time */) {
    echo "Registration successful!";

    // Show final registration link
    echo "<p>Event: $event_title</p>";
    echo "<p><a href='final_registration.php?student_id=$student_id&event_id=$event_id'>Complete Final Registration</a></p>";
} else {
    echo "You are not within the event's time or geofence area.";
}

$conn->close();
?>
