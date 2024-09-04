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
            $registration_id = $stmt->insert_id;
            $current_time = time();
            $event_start_time = strtotime($event_start_time);
            $event_end_time = $event_start_time + ($event_duration * 60); // Event duration in seconds
            $time_left = $event_start_time - $current_time;

            // Prepare data to be sent to the frontend
            $data = [
                'event_start_time' => $event_start_time * 1000, // Convert to milliseconds
                'event_end_time' => $event_end_time * 1000, // Convert to milliseconds
                'registration_id' => $registration_id,
                'event_id' => $event_id,
                'current_time' => $current_time * 1000 // Convert to milliseconds
            ];

            // Output JSON data for the frontend
            echo "<div id='registration-status'>";
            echo "<p>Registration successful!</p>";
            echo "<p id='countdown-timer'></p>";
            echo "<p id='final-registration-link' class='hidden'></p>";
            echo "</div>";

            echo "<script>
                var data = " . json_encode($data) . ";
                var eventStartTime = new Date(data.event_start_time);
                var eventEndTime = new Date(data.event_end_time);
                var registrationId = data.registration_id;
                var eventId = data.event_id;
                var currentTime = new Date(data.current_time);

                function updateTimer() {
                    var now = new Date().getTime();
                    var timeLeft = eventStartTime - now;
                    var timePassed = now - eventStartTime;
                    
                    if (timeLeft > 0) {
                        document.getElementById('countdown-timer').innerText = 'Final registration link will be available in ' + formatTime(timeLeft);
                    } else if (timePassed <= (eventEndTime.getTime() - eventStartTime.getTime())) {
                        document.getElementById('countdown-timer').innerText = 'Final registration link is available now!';
                        document.getElementById('final-registration-link').innerHTML = '<a href=\"final_registration.php?student_id=' + registrationId + '&event_id=' + eventId + '\">Complete Final Registration</a>';
                        document.getElementById('final-registration-link').classList.remove('hidden');
                    } else {
                        document.getElementById('countdown-timer').innerText = 'The event has ended.';
                    }
                }

                function formatTime(ms) {
                    var seconds = Math.floor(ms / 1000);
                    var minutes = Math.floor(seconds / 60);
                    var hours = Math.floor(minutes / 60);
                    var days = Math.floor(hours / 24);
                    hours = hours % 24;
                    minutes = minutes % 60;
                    seconds = seconds % 60;
                    return (days > 0 ? days + 'd ' : '') + (hours > 0 ? hours + 'h ' : '') + (minutes > 0 ? minutes + 'm ' : '') + seconds + 's';
                }

                setInterval(updateTimer, 1000);
                updateTimer();
            </script>";

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
