<?php
include('/var/www/html/db_connect.php');

// Get event_id and student_id from the query parameters
$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : null;
$student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : null;

// Check if event_id and student_id are valid
if (!$event_id || !$student_id) {
    die('Invalid access! Event ID or Student ID is missing.');
}

// Fetch event details from the database
$stmt = $conn->prepare("SELECT title, latitude, longitude, start_time, duration FROM forms WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$stmt->bind_result($title, $event_lat, $event_lng, $start_time, $duration);
$stmt->fetch();
$stmt->close();

if (!$start_time || !$duration) {
    die('Event not found or invalid data.');
}

// Convert event times to timestamps
$event_start_time = strtotime($start_time);
$event_end_time = $event_start_time + ($duration * 60);  // Calculate event end time

// Add a 5-minute grace period after the event ends
$grace_period_end_time = $event_end_time + (5 * 60);

// Get the current time
$current_time = time();

// Check if the current time is within the event duration and grace period
if ($current_time >= $event_start_time && $current_time <= $grace_period_end_time) {
    // The user is accessing the link within the allowed time window
    echo "<h3>You are within the event's time duration. Please proceed with the final registration.</h3>";
    echo "<p>Event: $title</p>";
    echo "<p><a href='final_registration.php?student_id=$student_id&event_id=$event_id'>Complete Final Registration</a></p>";
} else {
    // Event hasn't started yet
    if ($current_time < $event_start_time) {
        $time_remaining = $event_start_time - $current_time;
        echo "<p>The event has not started yet. Please wait for " . gmdate("H:i:s", $time_remaining) . " until the event begins.</p>";
    }
    // Event and grace period have both ended
    elseif ($current_time > $grace_period_end_time) {
        echo "The event has ended, and the grace period has passed. Registration is no longer possible.";
    }
}

$conn->close();
?>
