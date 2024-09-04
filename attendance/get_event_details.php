<?php
include('/var/www/html/db_connect.php'); // Include your database connection

// Assuming you have the event ID in a session or passed in some way
$event_id = $_GET['event_id']; // Fetch the event ID from query string or session

$stmt = $conn->prepare("SELECT latitude, longitude, duration FROM forms WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $event = $result->fetch_assoc();
    echo json_encode($event);
} else {
    echo json_encode(['latitude' => 0, 'longitude' => 0, 'duration' => 0]);
}

$stmt->close();
$conn->close();
?>
