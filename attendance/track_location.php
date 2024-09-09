<?php
include('/var/www/html/db_connect.php'); // Include your database connection

// Start the session
session_start();

// Check if student_id is set in the session
if (!isset($_SESSION['student_id'])) {
    die('Student ID not found. Please log in.');
}

// Retrieve POST data with default values
$student_id = isset($_POST['student_id']) ? (int) $_POST['student_id'] : null;
$event_id = isset($_POST['event_id']) ? (int) $_POST['event_id'] : null;
$user_latitude = isset($_POST['latitude']) ? (float) $_POST['latitude'] : null;
$user_longitude = isset($_POST['longitude']) ? (float) $_POST['longitude'] : null;
$email = isset($_POST['student_email']) ? filter_var(trim($_POST['student_email']), FILTER_SANITIZE_EMAIL) : '';

// Validate the input
if ($student_id === null || $event_id === null || $user_latitude === null || $user_longitude === null || empty($email)) {
    die('Required data is missing.');
}

// Fetch the event details from the database
$stmt = $conn->prepare("SELECT latitude, longitude FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$stmt->bind_result($event_latitude, $event_longitude);
$stmt->fetch();
$stmt->close();

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
$distance_to_event = haversine_distance($user_latitude, $user_longitude, $event_latitude, $event_longitude);

// Check if the user is within the geofence
if ($distance_to_event <= $geofence_radius) {
    // Check if the user has an existing entry
    $entry_check_stmt = $conn->prepare("SELECT id, entry_time FROM student_attendance WHERE event_id = ? AND student_email = ?");
    $entry_check_stmt->bind_param("is", $event_id, $email);
    $entry_check_stmt->execute();
    $entry_check_stmt->bind_result($log_id, $entry_time);
    $entry_check_stmt->fetch();
    $entry_check_stmt->close();

    if (!$entry_time) {
        // Log the entry time (user enters geofence)
        $entry_time = time();  // Use current time as entry time
        $insert_entry_stmt = $conn->prepare("INSERT INTO student_attendance (student_email, event_id, entry_time) VALUES (?, ?, ?)");
        $insert_entry_stmt->bind_param("sii", $email, $event_id, $entry_time);
        if ($insert_entry_stmt->execute()) {
            echo "<p>Welcome! Your entry time has been logged.</p>";
        } else {
            echo "Error logging entry time: " . $insert_entry_stmt->error;
        }
        $insert_entry_stmt->close();
    } else {
        echo "<p>You are already within the geofence.</p>";
    }
} else {
    // User is outside the geofence, check for exit
    $exit_check_stmt = $conn->prepare("SELECT id, entry_time FROM student_attendance WHERE event_id = ? AND student_email = ?");
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
        $update_exit_stmt->bind_param("iii", $exit_time, $time_spent, $log_id);
        if ($update_exit_stmt->execute()) {
            echo "<p>Exit logged: $exit_time, Time spent: $time_spent seconds.</p>";
        } else {
            echo "Error updating exit time: " . $update_exit_stmt->error;
        }
        $update_exit_stmt->close();
    } else {
        echo "<p>You are not currently within the geofence.</p>";
    }
}

// Close the database connection
$conn->close();
?>
