<?php
include('/var/www/html/db_connect.php'); // Include your database connection

$event_name = $_POST['event_name'];
$latitude = $_POST['latitude'];
$longitude = $_POST['longitude'];

if ($latitude && $longitude && $event_name) {
    $stmt = $conn->prepare("INSERT INTO forms (title, latitude, longitude) VALUES (?, ?, ?)");
    $stmt->bind_param("sdd", $event_name, $latitude, $longitude);

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
