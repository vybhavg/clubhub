<?php
include('/var/www/html/db_connect.php'); 
session_start();

// Check if session data is set
if (!isset($_SESSION['student_id']) || !isset($_SESSION['event_id']) || !isset($_SESSION['email'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Required session data is missing.']);
    exit;
}

// Retrieve session data
$student_id = $_SESSION['student_id'];
$event_id = $_SESSION['event_id'];
$email = $_SESSION['email'];

// Handle POST request for location updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    // Read the JSON input
    $data = json_decode(file_get_contents('php://input'), true);

    // Log received data for debugging
    error_log('Received data: ' . print_r($data, true));

    // Check if JSON data is valid
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON']);
        exit;
    }

    // Retrieve data from JSON
    $received_student_id = isset($data['student_id']) ? (int) $data['student_id'] : null;
    $received_event_id = isset($data['event_id']) ? (int) $data['event_id'] : null;
    $received_email = isset($data['email']) ? filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL) : '';
    $latitude = isset($data['latitude']) ? (float) $data['latitude'] : null;
    $longitude = isset($data['longitude']) ? (float) $data['longitude'] : null;

    // Log extracted data for debugging
    error_log('Extracted data: Student ID: ' . $received_student_id . ', Event ID: ' . $received_event_id . ', Latitude: ' . $latitude . ', Longitude: ' . $longitude);

    // Validate received data
    if ($latitude === null || $longitude === null) {
        http_response_code(400);
        echo json_encode(['error' => 'Latitude or Longitude is missing']);
        exit;
    }

    // Process the received data (e.g., save to database)
    // For demonstration, just return the received data
    echo json_encode([
        'message' => 'Location data received successfully',
        'student_id' => $received_student_id,
        'event_id' => $received_event_id,
        'email' => $received_email,
        'latitude' => $latitude,
        'longitude' => $longitude
    ]);
    exit;
}

// If not a POST request, return an error
http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
exit;




    // Fetch the event details from the database
    $stmt = $conn->prepare("SELECT latitude, longitude FROM events WHERE id = ?");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['error' => 'Database prepare error: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $stmt->bind_result($event_latitude, $event_longitude);
    $stmt->fetch();
    $stmt->close();

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
    $distance_to_event = haversine_distance($latitude, $longitude, $event_latitude, $event_longitude);

    // Check if the user is within the geofence
    if ($distance_to_event <= $geofence_radius) {
        // Check if the user has an existing entry
        $entry_check_stmt = $conn->prepare("SELECT id, entry_time FROM student_attendance WHERE event_id = ? AND student_email = ?");
        if (!$entry_check_stmt) {
            http_response_code(500);
            echo json_encode(['error' => 'Database prepare error: ' . $conn->error]);
            exit;
        }
        $entry_check_stmt->bind_param("is", $event_id, $email);
        $entry_check_stmt->execute();
        $entry_check_stmt->bind_result($log_id, $entry_time);
        $entry_check_stmt->fetch();
        $entry_check_stmt->close();

        if (!$entry_time) {
            // Log the entry time (user enters geofence)
            $entry_time = time();  // Use current time as entry time
            $insert_entry_stmt = $conn->prepare("INSERT INTO student_attendance (student_email, event_id, entry_time) VALUES (?, ?, ?)");
            if (!$insert_entry_stmt) {
                http_response_code(500);
                echo json_encode(['error' => 'Database prepare error: ' . $conn->error]);
                exit;
            }
            $insert_entry_stmt->bind_param("sii", $email, $event_id, $entry_time);
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
        $exit_check_stmt = $conn->prepare("SELECT id, entry_time FROM student_attendance WHERE event_id = ? AND student_email = ?");
        if (!$exit_check_stmt) {
            http_response_code(500);
            echo json_encode(['error' => 'Database prepare error: ' . $conn->error]);
            exit;
        }
        $exit_check_stmt->bind_param("is", $event_id, $email);
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
            if (!$update_exit_stmt) {
                http_response_code(500);
                echo json_encode(['error' => 'Database prepare error: ' . $conn->error]);
                exit;
            }
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
