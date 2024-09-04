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

if ($latitude && $longitude && $name && $email) {
    $stmt = $conn->prepare("SELECT title, latitude, longitude, event_start_time, event_duration FROM forms");
    $stmt->execute();
    $stmt->bind_result($title, $eventLatitude, $eventLongitude, $eventStartTime, $eventDuration);

    $isWithinGeofence = false;
    $geofenceRadius = 1000; // Geofence radius in meters

    $currentTime = new DateTime("now", new DateTimeZone('UTC')); // Current server time in UTC
    $currentTimeStr = $currentTime->format('Y-m-d H:i:s');
    $currentTimeUnix = $currentTime->getTimestamp();

    while ($stmt->fetch()) {
        $eventStartTimeUTC = new DateTime($eventStartTime, new DateTimeZone('Asia/Kolkata')); // Convert IST to UTC
        $eventEndTimeUTC = clone $eventStartTimeUTC;
        $eventEndTimeUTC->add(new DateInterval("PT{$eventDuration}M"));

        $eventStartTimeStr = $eventStartTimeUTC->format('Y-m-d H:i:s');
        $eventEndTimeStr = $eventEndTimeUTC->format('Y-m-d H:i:s');

        $eventStartUnix = $eventStartTimeUTC->getTimestamp();
        $eventEndUnix = $eventEndTimeUTC->getTimestamp();

        $distance = haversineGreatCircleDistance($latitude, $longitude, $eventLatitude, $eventLongitude);
        if ($distance <= $geofenceRadius && $currentTimeUnix >= $eventStartUnix && $currentTimeUnix <= $eventEndUnix) {
            $isWithinGeofence = true;
            break;
        }
    }

    $stmt->close();

    if ($isWithinGeofence) {
        // Calculate the time until the final registration link appears
        $finalRegistrationAvailableTime = new DateTime("now", new DateTimeZone('UTC'));
        $finalRegistrationAvailableTime->add(new DateInterval("PT" . $eventDuration . "M"));

        $interval = $finalRegistrationAvailableTime->diff(new DateTime("now", new DateTimeZone('UTC')));
        $remainingTime = $interval->format('%h hours %i minutes %s seconds');

        echo "Registration successful! Final registration link will be available in $remainingTime.";
    } else {
        echo "You are not within the geofenced area for any event.";
    }
} else {
    echo "Invalid data!";
}

$conn->close();
?>
