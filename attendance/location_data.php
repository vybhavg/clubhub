<?php
include('/var/www/html/db_connect.php'); // Include your database connection

// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['student_id']) || !isset($_SESSION['event_id']) || !isset($_SESSION['email'])) {
    header('Content-Type: application/json');
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Retrieve session data
$student_id = $_SESSION['student_id'];
$event_id = $_SESSION['event_id'];
$email = $_SESSION['email'];

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    // Read and decode the JSON input
    $data = json_decode(file_get_contents('php://input'), true);

    // Check if JSON data is valid
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON']);
        exit;
    }

    // Retrieve data from JSON
    $latitude = isset($data['latitude']) ? (float)$data['latitude'] : null;
    $longitude = isset($data['longitude']) ? (float)$data['longitude'] : null;

    // Validate the input
    if ($latitude === null || $longitude === null) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required data.']);
        exit;
    }

    // Fetch event location from the database
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

    // Geofence parameters
    $geofence_radius = 1.0; // 1 km radius

    // Function to calculate the distance between two GPS coordinates
    function haversine_distance($lat1, $lon1, $lat2, $lon2) {
        $earth_radius = 6371; // Earth radius in kilometers
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earth_radius * $c;
    }

    // Calculate the distance between the event location and the user's location
    $distance_to_event = haversine_distance($latitude, $longitude, $event_latitude, $event_longitude);

    if ($distance_to_event <= $geofence_radius) {
        // User is within the geofence, check if there is an existing entry
        $entry_check_stmt = $conn->prepare("SELECT id, entry_time FROM student_attendance WHERE event_id = ? AND student_id = ?");
        $entry_check_stmt->bind_param("ii", $event_id, $student_id);
        $entry_check_stmt->execute();
        $entry_check_stmt->bind_result($log_id, $entry_time);
        $entry_check_stmt->fetch();
        $entry_check_stmt->close();

        if (!$entry_time) {
            // Log the entry time
            $entry_time = time();
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
        // User is outside the geofence, check if there is an existing entry to log exit
        $exit_check_stmt = $conn->prepare("SELECT id, entry_time FROM student_attendance WHERE event_id = ? AND student_id = ?");
        $exit_check_stmt->bind_param("ii", $event_id, $student_id);
        $exit_check_stmt->execute();
        $exit_check_stmt->bind_result($log_id, $entry_time);
        $exit_check_stmt->fetch();
        $exit_check_stmt->close();

        if ($entry_time) {
            // Log exit time
            $exit_time = time();
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
} else {
    // If the request method is not POST
    header('Content-Type: application/json');
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
}
?>
 
