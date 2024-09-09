<?php
// Include your database connection
include('/var/www/html/db_connect.php');

// Handle POST request for location updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    // Read the JSON input
    $data = json_decode(file_get_contents('php://input'), true);

    // Check if JSON data is valid
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON']);
        exit;
    }

    // Retrieve data from JSON
    $email = isset($data['email']) ? $data['email'] : null;
    $lat = isset($data['latitude']) ? (float)$data['latitude'] : null;
    $lng = isset($data['longitude']) ? (float)$data['longitude'] : null;
    $event_id = isset($data['event_id']) ? (int)$data['event_id'] : null;

    // Validate the input
    if ($email === null || $lat === null || $lng === null || $event_id === null) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required data.']);
        exit;
    }

    // Fetch student data from the database based on email
    $student_query = $conn->prepare("SELECT id FROM students WHERE email = ?");
    $student_query->bind_param("s", $email);
    $student_query->execute();
    $student_query->bind_result($student_id);
    $student_query->fetch();
    $student_query->close();

    if (!$student_id) {
        http_response_code(404);
        echo json_encode(['error' => 'Student not found.']);
        exit;
    }

    // Fetch the event location from the database
    $event_query = $conn->prepare("SELECT latitude, longitude FROM events WHERE id = ?");
    $event_query->bind_param("i", $event_id);
    $event_query->execute();
    $event_query->bind_result($event_latitude, $event_longitude);
    $event_query->fetch();
    $event_query->close();

    if ($event_latitude === null || $event_longitude === null) {
        http_response_code(404);
        echo json_encode(['error' => 'Event location not found.']);
        exit;
    }

    // Ensure event latitude and longitude are cast to float
    $event_latitude = (float) $event_latitude;
    $event_longitude = (float) $event_longitude;

    // Geofence parameters
    $geofence_radius = 1.0; // 1 km radius

    // Haversine formula to calculate the distance between two GPS coordinates
    function haversine_distance($lat1, $lon1, $lat2, $lon2) {
        $earth_radius = 6371;  // Earth radius in kilometers
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earth_radius * $c;
    }

    // Calculate the distance between the event location and the user's location
    $distance_to_event = haversine_distance($lat, $lng, $event_latitude, $event_longitude);

    // Check if the user is within the geofence
    if ($distance_to_event <= $geofence_radius) {
        // Check if the user has an existing entry
        $entry_check_stmt = $conn->prepare("SELECT id, entry_time FROM student_attendance WHERE event_id = ? AND student_id = ?");
        $entry_check_stmt->bind_param("ii", $event_id, $student_id);
        $entry_check_stmt->execute();
        $entry_check_stmt->bind_result($log_id, $entry_time);
        $entry_check_stmt->fetch();
        $entry_check_stmt->close();

        if (!$entry_time) {
            // Log the entry time (user enters geofence)
            $entry_time = time();  // Use current time as entry time
            $insert_entry_stmt = $conn->prepare("INSERT INTO student_attendance (student_id, event_id, entry_time) VALUES (?, ?, ?)");
            $insert_entry_stmt->bind_param("iii", $student_id, $event_id, $entry_time);
            if ($insert_entry_stmt->execute()) {
                echo json_encode(['message' => 'Welcome! Your entry time has been logged.']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error logging entry time: ' . $insert_entry_stmt->error]);
            }
            $insert_entry_stmt->close();
        } else {
            echo json_encode(['message' => 'You are already within the geofence.']);
        }
    } else {
        // User is outside the geofence, check for exit
        $exit_check_stmt = $conn->prepare("SELECT id, entry_time FROM student_attendance WHERE event_id = ? AND student_id = ?");
        $exit_check_stmt->bind_param("ii", $event_id, $student_id);
        $exit_check_stmt->execute();
        $exit_check_stmt->bind_result($log_id, $entry_time);
        $exit_check_stmt->fetch();
        $exit_check_stmt->close();

        if ($entry_time) {
            // The user is leaving the geofence, log exit time
            $exit_time = time();  // Use current time as exit time
            $time_spent = $exit_time - $entry_time;

            // Update the log with exit time
            $update_exit_stmt = $conn->prepare("UPDATE student_attendance SET exit_time = ?, time_spent = ? WHERE id = ?");
            $update_exit_stmt->bind_param("iii", $exit_time, $time_spent, $log_id);
            if ($update_exit_stmt->execute()) {
                echo json_encode([
                    'message' => 'Exit logged: ' . date('Y-m-d H:i:s', $exit_time) . '. Time spent: ' . $time_spent . ' seconds.'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error updating exit time: ' . $update_exit_stmt->error]);
            }
            $update_exit_stmt->close();
        } else {
            echo json_encode(['message' => 'You are not currently within the geofence.']);
        }
    }

    // Close the database connection
    $conn->close();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Location Tracker</title>
    <script>
        // Function to send location data to the server in JSON format
        function sendLocationData(email, latitude, longitude, event_id) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'location.php', true); // Use the same script for handling the data
            xhr.setRequestHeader('Content-Type', 'application/json');

            // Create a JSON object
            const data = JSON.stringify({
                email: email,
                latitude: latitude,
                longitude: longitude,
                event_id: event_id
            });

            xhr.send(data);

            xhr.onload = function () {
                if (xhr.status === 200) {
                    console.log('Location submitted successfully');
                } else {
                    console.log('Error submitting location');
                }
            };
        }

        // Function to track and send location periodically
        function trackLocationPeriodically(email, event_id) {
            if (navigator.geolocation) {
                setInterval(function () {
                    navigator.geolocation.getCurrentPosition(function (position) {
                        const latitude = position.coords.latitude;
                        const longitude = position.coords.longitude;

                        // Send location data to the server
                        sendLocationData(email, latitude, longitude, event_id);
                    }, function (error) {
                        console.error('Error fetching location: ' + error.message);
                    });
                }, 10000); // Send location every 10 seconds
            } else {
                alert('Geolocation is not supported by this browser.');
            }
        }

        // Start tracking once the page loads
        window.onload = function () {
            // Example values for email and event_id; replace with actual values
            const email = '<?php echo addslashes($email); ?>';
            const event_id = <?php echo intval($event_id); ?>;

            // Start location tracking
            trackLocationPeriodically(email, event_id);
        };
    </script>
</head>
<body>
    <h1>Location Tracker</h1>
</body>
</html>
