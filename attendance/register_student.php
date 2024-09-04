<?php
include('/var/www/html/db_connect.php'); // Include your database connection

function haversineGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000) {
    // Convert from degrees to radians
    $latFrom = deg2rad($latitudeFrom);
    $lonFrom = deg2rad($longitudeFrom);
    $latTo = deg2rad($latitudeTo);
    $lonTo = deg2rad($longitudeTo);

    $latDelta = $latTo - $latFrom;
    $lonDelta = $lonTo - $lonFrom;

    $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
      cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
    return $angle * $earthRadius;
}

$name = $_POST['name'];
$email = $_POST['email'];
$latitude = $_POST['latitude'];
$longitude = $_POST['longitude'];
$event_id = $_POST['event_id'];

if ($latitude && $longitude && $name && $email && $event_id) {
    $stmt = $conn->prepare("SELECT latitude, longitude FROM forms WHERE id = ?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $stmt->bind_result($eventLatitude, $eventLongitude);

    $isWithinGeofence = false;
    $geofenceRadius = 1000; // Geofence radius in meters

    if ($stmt->fetch()) {
        $distance = haversineGreatCircleDistance($latitude, $longitude, $eventLatitude, $eventLongitude);
        if ($distance <= $geofenceRadius) {
            $isWithinGeofence = true;
        }
    }

    $stmt->close();

    if ($isWithinGeofence) {
        // Save the registration details to the database
        $stmt = $conn->prepare("INSERT INTO registrations (name, email, latitude, longitude, ip_address, event_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssddsi", $name, $email, $latitude, $longitude, $_SERVER['REMOTE_ADDR'], $event_id);

        if ($stmt->execute()) {
            echo "Registration successful!";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "You are not within the geofenced area for this event.";
    }
} else {
    echo "Invalid data!";
}

$conn->close();
?>
