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
$event_id = $_POST['event_id'];
$latitude = $_POST['latitude'];
$longitude = $_POST['longitude'];

if ($latitude && $longitude && $name && $email && $event_id) {
    // Get event details
    $stmt = $conn->prepare("SELECT latitude, longitude, event_start_time, event_duration FROM forms WHERE id = ?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $stmt->bind_result($eventLatitude, $eventLongitude, $event_start_time, $event_duration);
    $stmt->fetch();
    $stmt->close();

    $isWithinGeofence = false;
    $geofenceRadius = 1000; // Geofence radius in meters
    $distance = haversineGreatCircleDistance($latitude, $longitude, $eventLatitude, $eventLongitude);

    if ($distance <= $geofenceRadius) {
        $isWithinGeofence = true;
    }

    if ($isWithinGeofence) {
        // Save the registration details to the database
        $stmt = $conn->prepare("INSERT INTO registrations (name, email, latitude, longitude, ip_address, event_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssddsi", $name, $email, $latitude, $longitude, $_SERVER['REMOTE_ADDR'], $event_id);

        if ($stmt->execute()) {
            echo "Registration successful!";

            // Timer Logic
            $current_time = time();
            $event_start_time = strtotime($event_start_time);
            $event_end_time = $event_start_time + ($event_duration * 60); // Event duration in seconds
            $time_left = $event_start_time - $current_time;

            if ($current_time >= $event_start_time && $current_time <= $event_end_time) {
                echo "<p>The final registration link is available now:</p>";
                echo "<a href='final_registration.php?student_id={$stmt->insert_id}&event_id=$event_id'>Complete Final Registration</a>";
            } elseif ($time_left > 0) {
                echo "<p>The final registration link will be available in " . gmdate("H:i:s", $time_left) . "</p>";
            } else {
                echo "<p>The event has ended.</p>";
            }
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
