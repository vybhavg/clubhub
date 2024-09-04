<?php
include('/var/www/html/db_connect.php');

// Get event_id and student_id from the query parameters
$event_id = $_GET['event_id'] ?? null;
$student_id = $_GET['student_id'] ?? null;

if (!$event_id || !$student_id) {
    die('Invalid access!');
}

// Fetch event details from the database
$stmt = $conn->prepare("SELECT title, latitude, longitude, start_time, duration FROM forms WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$stmt->bind_result($title, $event_lat, $event_lng, $start_time, $duration);
$stmt->fetch();
$stmt->close();

$current_time = time();
$event_end_time = strtotime($start_time) + ($duration * 60);

if ($current_time >= strtotime($start_time) && $current_time <= $event_end_time) {
    echo "<h3>You are within the event's time duration. Please proceed with the final registration.</h3>";
    echo "<p>Event: $title</p>";
    echo "<p><a href='final_registration.php?student_id=$student_id&event_id=$event_id'>Complete Final Registration</a></p>";
} else {
    echo "You are not within the event's time duration.";
}

$conn->close();
?>

