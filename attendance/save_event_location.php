<?php
include('/var/www/html/db_connect.php'); // Include your database connection

$event_name = $_POST['event_name'];
$latitude = $_POST['latitude'];
$longitude = $_POST['longitude'];
$event_duration = $_POST['event_duration']; // Duration in minutes

if ($latitude && $longitude && $event_name && $event_duration) {
    $stmt = $conn->prepare("INSERT INTO forms (title, latitude, longitude, duration) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sddi", $event_name, $latitude, $longitude, $event_duration);

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
