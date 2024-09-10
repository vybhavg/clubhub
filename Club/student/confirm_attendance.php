<?php
include('/var/www/html/db_connect.php'); // Include your database connection

// Start the session
session_start();

// Check if student_id is set in the session
if (!isset($_SESSION['student_id'])) {
    die('Student ID not found. Please log in.');
}

// Get data from the form and cast to appropriate types
$student_id = (int) $_POST['student_id']; // Cast to integer
$event_id = (int) $_POST['event_id']; // Cast to integer
$user_latitude = (float) $_POST['latitude'];  // Cast to float
$user_longitude = (float) $_POST['longitude']; // Cast to float
$name = trim($_POST['student_name']); // Trim and sanitize student name
$email = filter_var(trim($_POST['student_email']), FILTER_SANITIZE_EMAIL); // Trim, sanitize and validate student email

// Fetch the event details from the database
$stmt = $conn->prepare("SELECT title, latitude, longitude FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$stmt->bind_result($event_title, $event_latitude, $event_longitude);
$stmt->fetch();
$stmt->close();

// Ensure event latitude and longitude are cast to float
$event_latitude = (float) $event_latitude;
$event_longitude = (float) $event_longitude;

// Geofence parameters
$geofence_radius = 1.0; // 1 km radius (adjusted to km)

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
    // Log the attendance
    $current_time = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
    $current_timestamp = $current_time->getTimestamp(); // Use this timestamp for logging

    // Insert into final_attendance
    $insert_final_attendance_stmt = $conn->prepare(
        "INSERT INTO final_attendance (student_name, student_email, event_id, entry_time, exit_time, time_spent) 
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $insert_final_attendance_stmt->bind_param(
        "ssiii", 
        $name, 
        $email, 
        $event_id, 
        $current_timestamp, 
        $current_timestamp, 
        0
    );
    $insert_final_attendance_stmt->execute();
    $insert_final_attendance_stmt->close();

    echo "<p>Attendance confirmed successfully.</p>";
} else {
    echo "<p>You are not within the geofence. Attendance could not be confirmed.</p>";
}

// Close the database connection
$conn->close();
?>
