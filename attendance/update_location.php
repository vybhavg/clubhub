<?php
include('/var/www/html/db_connect.php'); // Include your database connection

// Start the session
session_start();

// Check if student_id is set in the session
if (!isset($_SESSION['student_id'])) {
    die('Student ID not found. Please log in.');
}

// Get data from the form
$student_id = $_SESSION['student_id'];
$user_latitude = isset($_POST['latitude']) ? (float) $_POST['latitude'] : null;
$user_longitude = isset($_POST['longitude']) ? (float) $_POST['longitude'] : null;

// Fetch the latest event details
$stmt = $conn->prepare("SELECT event_id, latitude, longitude FROM student_attendance WHERE student_id = ? AND exit_time IS NULL ORDER BY entry_time DESC LIMIT 1");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$stmt->bind_result($event_id, $event_latitude, $event_longitude);
$stmt->fetch();
$stmt->close();

if ($event_id) {
    // Store location update
    $stmt = $conn->prepare("INSERT INTO location_updates (student_id, event_id, latitude, longitude, timestamp) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("iidd", $student_id, $event_id, $user_latitude, $user_longitude);
    if (!$stmt->execute()) {
        echo "Error inserting location update: " . $stmt->error;
    }
    $stmt->close();
}

$conn->close();
?>
