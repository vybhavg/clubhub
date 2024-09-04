<?php
include('/var/www/html/db_connect.php'); // Ensure this path is correct

// Fetch event details from the database
$event_id = $_GET['event_id'] ?? null;

if (!$event_id) {
    die('Event ID is missing!');
}

$stmt = $conn->prepare("SELECT title, event_start_time, event_duration FROM forms WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$stmt->bind_result($title, $event_start_time, $event_duration);
$stmt->fetch();
$stmt->close();

$event_end_time = date('Y-m-d H:i:s', strtotime($event_start_time) + ($event_duration * 60));
$current_time = date('Y-m-d H:i:s');

// Output for testing
echo "<h1>Event and Server Time</h1>";
echo "<p>Server Time (UTC): " . date('Y-m-d H:i:s') . "</p>";
echo "<p>Event Start Time: $event_start_time</p>";
echo "<p>Event End Time: $event_end_time</p>";

$conn->close();
?>
