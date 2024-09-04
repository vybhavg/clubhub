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

    // Convert event start time to a timestamp
    $event_start_time_utc = new DateTime($event_start_time, new DateTimeZone('UTC'));
    $event_start_time_local = $event_start_time_utc->setTimezone(new DateTimeZone('Asia/Kolkata')); // Adjust for IST
    $event_start_timestamp = $event_start_time_local->getTimestamp();

    // Calculate event end time
    $event_end_timestamp = $event_start_timestamp + ($event_duration * 60);

    // Check if the student is within the geofence
    $distance = haversineGreatCircleDistance($latitude, $longitude, $eventLatitude, $eventLongitude);
    $geofence_radius = 1000; // 1000 meters

    if ($distance <= $geofence_radius) {
        // Save registration details
        $stmt = $conn->prepare("INSERT INTO registrations (name, email, latitude, longitude, ip_address, event_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssddsi", $name, $email, $latitude, $longitude, $_SERVER['REMOTE_ADDR'], $event_id);

        if ($stmt->execute()) {
            // Calculate remaining time for final registration link
            $current_timestamp = time();
            $remaining_time = max(0, $event_end_timestamp - $current_timestamp);

            $minutes = floor($remaining_time / 60);
            $seconds = $remaining_time % 60;

            echo "Registration successful! Final registration link will be available in $minutes minutes $seconds seconds.";
            echo "<script>var eventEndTime = $event_end_timestamp; var currentTime = $current_timestamp;</script>";
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Status</title>
    <script>
        function updateCountdown() {
            var eventEndTime = window.eventEndTime; // The end time from PHP
            var currentTime = window.currentTime; // The current time from PHP

            var countdownElement = document.getElementById('countdown');
            
            var remainingTime = eventEndTime - currentTime;
            var minutes = Math.floor(remainingTime / 60);
            var seconds = remainingTime % 60;

            countdownElement.textContent = `Final registration link will be available in ${minutes} minutes ${seconds} seconds`;

            if (remainingTime <= 0) {
                countdownElement.textContent = "Final registration link is now available!";
                clearInterval(timerInterval);
            }
        }

        var timerInterval = setInterval(updateCountdown, 1000); // Update every second
    </script>
</head>
<body>
    <h1>Registration Status</h1>
    <p id="countdown">Calculating time...</p>
</body>
</html>
