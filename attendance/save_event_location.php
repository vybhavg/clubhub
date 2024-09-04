<?php
include('/var/www/html/db_connect.php'); // Include your database connection

$event_name = $_POST['event_name'];
$latitude = $_POST['latitude'];
$longitude = $_POST['longitude'];
$event_start_time = $_POST['event_start_time']; // Event start time input
$event_duration = $_POST['event_duration']; // Event duration input

if ($latitude && $longitude && $event_name && $event_start_time && $event_duration) {
    $stmt = $conn->prepare("INSERT INTO forms (title, latitude, longitude, event_start_time, event_duration) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sddsi", $event_name, $latitude, $longitude, $event_start_time, $event_duration);

    if ($stmt->execute()) {
        echo "Event location saved successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Invalid data!";
}

$conn->close();
?>
