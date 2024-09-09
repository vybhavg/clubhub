<?php
// Start session
session_start();

// Check if session data is available
if (!isset($_SESSION['student_id']) || !isset($_SESSION['event_id']) || !isset($_SESSION['email'])) {
    die('Required session data is missing.');
}

// Retrieve session data
$student_id = $_SESSION['student_id'];
$event_id = $_SESSION['event_id'];
$email = $_SESSION['email'];

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Invalid request method.');
}

// Retrieve POST data
$student_id = isset($_POST['student_id']) ? (int) $_POST['student_id'] : null;
$event_id = isset($_POST['event_id']) ? (int) $_POST['event_id'] : null;
$email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
$user_latitude = isset($_POST['latitude']) ? (float) $_POST['latitude'] : null;
$user_longitude = isset($_POST['longitude']) ? (float) $_POST['longitude'] : null;

// Validate input
if ($student_id === null || $event_id === null || empty($email) || $user_latitude === null || $user_longitude === null) {
    die('Required data is missing or invalid.');
}

// Database connection
require_once 'db_connect.php'; // Ensure you include your actual DB connection

// Fetch event details
$stmt = $conn->prepare("SELECT latitude, longitude FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$stmt->bind_result($event_latitude, $event_longitude);
$stmt->fetch();
$stmt->close();

if ($event_latitude === null || $event_longitude === null) {
    die('Event location not found.');
}

// Geofence radius in km
$geofence_radius = 1.0;

// Haversine formula to calculate the distance between two coordinates
function haversine_distance($lat1, $lon1, $lat2, $lon2) {
    $earth_radius = 6371;  // Earth radius in kilometers
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return $earth_radius * $c;
}

// Calculate the distance between user's location and the event location
$distance_to_event = haversine_distance($user_latitude, $user_longitude, $event_latitude, $event_longitude);

// Check if the user is within the geofence
if ($distance_to_event <= $geofence_radius) {
    // Check if the user has an existing attendance log
    $entry_check_stmt = $conn->prepare("SELECT id, entry_time FROM student_attendance WHERE event_id = ? AND student_email = ?");
    $entry_check_stmt->bind_param("is", $event_id, $email);
    $entry_check_stmt->execute();
    $entry_check_stmt->bind_result($log_id, $entry_time);
    $entry_check_stmt->fetch();
    $entry_check_stmt->close();

    if (!$entry_time) {
        // Log the entry time (user enters geofence)
        $entry_time = time();
        $insert_entry_stmt = $conn->prepare("INSERT INTO student_attendance (student_email, event_id, entry_time) VALUES (?, ?, ?)");
        $insert_entry_stmt->bind_param("sii", $email, $event_id, $entry_time);
        if ($insert_entry_stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Entry time logged successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error logging entry time: ' . $insert_entry_stmt->error]);
        }
        $insert_entry_stmt->close();
    } else {
        echo json_encode(['status' => 'info', 'message' => 'Already within geofence.']);
    }
} else {
    // If user is outside the geofence, log exit
    $exit_check_stmt = $conn->prepare("SELECT id, entry_time FROM student_attendance WHERE event_id = ? AND student_email = ?");
    $exit_check_stmt->bind_param("is", $event_id, $email);
    $exit_check_stmt->execute();
    $exit_check_stmt->bind_result($log_id, $entry_time);
    $exit_check_stmt->fetch();
    $exit_check_stmt->close();

    if ($entry_time) {
        // The user is leaving the geofence, log exit time
        $exit_time = time();
        $time_spent = $exit_time - $entry_time;

        // Update the log with exit time
        $update_exit_stmt = $conn->prepare("UPDATE student_attendance SET exit_time = ?, time_spent = ? WHERE id = ?");
        $update_exit_stmt->bind_param("iii", $exit_time, $time_spent, $log_id);
        if ($update_exit_stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Exit time logged successfully.', 'time_spent' => $time_spent]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error logging exit time: ' . $update_exit_stmt->error]);
        }
        $update_exit_stmt->close();
    } else {
        echo json_encode(['status' => 'info', 'message' => 'You are not currently within the geofence.']);
    }
}

// Close the database connection
$conn->close();
?>
