<?php
include('/var/www/html/db_connect.php');

// Helper function to calculate the Haversine distance
function haversineGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000) {
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

// Get POST parameters
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

    // Convert event start time from IST to UTC
    $event_start_time_ist = new DateTime($event_start_time, new DateTimeZone('Asia/Kolkata'));
    $event_start_time_utc = $event_start_time_ist->setTimezone(new DateTimeZone('UTC'));
    $event_start_timestamp = $event_start_time_utc->getTimestamp();

    // Calculate event end time
    $event_end_timestamp = $event_start_timestamp + ($event_duration * 60); // duration in minutes converted to seconds

    // Get current server time in UTC
    $current_timestamp = time(); // Current time in UTC
    
    // Debug output for server time and event times
    error_log("Server Time (UTC): " . date('Y-m-d H:i:s', $current_timestamp));
    error_log("Event Start Time (UTC): " . date('Y-m-d H:i:s', $event_start_timestamp));
    error_log("Event End Time (UTC): " . date('Y-m-d H:i:s', $event_end_timestamp));

    // Check if the student is within the geofence
    $distance = haversineGreatCircleDistance($latitude, $longitude, $eventLatitude, $eventLongitude);
    $geofence_radius = 1000; // 1000 meters

    if ($distance <= $geofence_radius) {
        // Save registration details
        $stmt = $conn->prepare("INSERT INTO registrations (name, email, latitude, longitude, ip_address, event_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssddsi", $name, $email, $latitude, $longitude, $_SERVER['REMOTE_ADDR'], $event_id);

        if ($stmt->execute()) {
            // Output the event end time for the countdown timer
            $remaining_time = max(0, $event_end_timestamp - $current_timestamp);

            $minutes = floor($remaining_time / 60);
            $seconds = $remaining_time % 60;

            echo "Registration successful! Final registration link will be available in <span id='timer'>$minutes minutes $seconds seconds</span>.";
            echo "<script>var eventEndTime = $event_end_timestamp * 1000; var currentTime = " . ($current_timestamp * 1000) . ";</script>";
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
