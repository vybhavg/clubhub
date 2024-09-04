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

    // Convert event_start_time from UTC to the local time zone (IST)
    $event_start_time_utc = new DateTime($event_start_time, new DateTimeZone('UTC'));
    $event_start_time_ist = $event_start_time_utc->setTimezone(new DateTimeZone('Asia/Kolkata'));

    // Calculate event end time
    $event_end_time = $event_start_time_utc->getTimestamp() + ($event_duration * 60); // Event duration in seconds
    $current_time = time(); // Current time in UTC

    // Debugging information
    error_log("Event Start Time (UTC): " . $event_start_time_utc->format('Y-m-d H:i:s'));
    error_log("Event End Time (UTC): " . date("Y-m-d H:i:s", $event_end_time));
    error_log("Current Time (UTC): " . date("Y-m-d H:i:s", $current_time));

    // Check if the student is within geofence
    $isWithinGeofence = haversineGreatCircleDistance($latitude, $longitude, $eventLatitude, $eventLongitude) <= 1000;

    if ($isWithinGeofence) {
        // Save the registration details to the database
        $stmt = $conn->prepare("INSERT INTO registrations (name, email, latitude, longitude, ip_address, event_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssddsi", $name, $email, $latitude, $longitude, $_SERVER['REMOTE_ADDR'], $event_id);

        if ($stmt->execute()) {
            $registration_id = $stmt->insert_id;
            
            // Calculate remaining time for final registration link
            $remaining_time = $event_end_time - $current_time;
            $remaining_minutes = floor($remaining_time / 60);
            $remaining_seconds = $remaining_time % 60;

            if ($remaining_time > 0) {
                echo "Final registration link will be available in " . $remaining_minutes . "m " . $remaining_seconds . "s.";
            } else {
                echo "The event has ended.";
            }
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "You are not within the geofenced area for the event.";
    }
} else {
    echo "Invalid data!";
}

$conn->close();
?>
