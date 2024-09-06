<?php
include('/var/www/html/db_connect.php'); // Include your database connection

// Start the session
session_start();

// Get data from the form
$name = $_POST['name'];
$email = $_POST['email'];
$user_latitude = $_POST['latitude'];
$user_longitude = $_POST['longitude'];
$event_id = $_POST['event_id'];

// Fetch the event (form) details from the database
$stmt = $conn->prepare("SELECT title, event_start_time, event_duration, event_end_time, latitude, longitude FROM forms WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$stmt->bind_result($event_title, $event_start_time, $event_duration, $event_end_time, $event_latitude, $event_longitude);
$stmt->fetch();
$stmt->close();

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

// Set server time to IST
$server_timezone = new DateTimeZone('UTC'); // Assuming server is in UTC
$ist_timezone = new DateTimeZone('Asia/Kolkata');

// Get current server time and convert to IST
$current_time = new DateTime('now', $server_timezone);
$current_time->setTimezone($ist_timezone);

// Convert event start and end times to IST
$event_start_time_ist = new DateTime($event_start_time, $ist_timezone);
$event_end_time_ist = new DateTime($event_end_time, $ist_timezone);

// Calculate time differences
$time_until_start = $event_start_time_ist->getTimestamp() - $current_time->getTimestamp();
$time_until_end = $event_end_time_ist->getTimestamp() - $current_time->getTimestamp();

function format_time($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $seconds = $seconds % 60;
    return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
}

// Display event times and time left
echo "<p>Event Start Time (IST): " . $event_start_time_ist->format('Y-m-d H:i:s') . "</p>";
echo "<p>Time until Event Starts: " . format_time(max($time_until_start, 0)) . "</p>";

echo "<p>Event End Time (IST): " . $event_end_time_ist->format('Y-m-d H:i:s') . "</p>";
echo "<p>Time until Event Ends: " . format_time(max($time_until_end, 0)) . "</p>";


// Check if the user is within the geofence
if ($distance_to_event <= $geofence_radius) {
    // Check if the user is entering the geofence
    $entry_check_stmt = $conn->prepare("SELECT id, entry_time FROM student_attendance WHERE event_id = ? AND exit_time IS NULL AND student_email = ?");
    $entry_check_stmt->bind_param("is", $event_id, $email);
    $entry_check_stmt->execute();
    $entry_check_stmt->bind_result($log_id, $entry_time);
    $entry_check_stmt->fetch();
    $entry_check_stmt->close();

    if (!$entry_time) {
        // Log the entry time (user enters geofence)
        $entry_time = $current_time_timestamp;
        $insert_entry_stmt = $conn->prepare("INSERT INTO student_attendance (student_name, student_email, event_id, entry_time) VALUES (?, ?, ?, ?)");
        $insert_entry_stmt->bind_param("ssis", $name, $email, $event_id, $entry_time);
        $insert_entry_stmt->execute();
        $insert_entry_stmt->close();

        echo "<p>Welcome! Your entry time has been logged. Continue participating in the event.</p>";
    } else {
        echo "<p>You are already within the geofence.</p>";
    }
} else {
    // User is leaving the geofence, log exit time
    $exit_check_stmt = $conn->prepare("SELECT id, entry_time FROM student_attendance WHERE event_id = ? AND exit_time IS NULL AND student_email = ?");
    $exit_check_stmt->bind_param("is", $event_id, $email);
    $exit_check_stmt->execute();
    $exit_check_stmt->bind_result($log_id, $entry_time);
    $exit_check_stmt->fetch();
    $exit_check_stmt->close();

    if ($entry_time) {
        // Calculate the time spent in this session
        $exit_time = $current_time_timestamp;
        $time_spent = $exit_time - $entry_time;

        // Update the log with the exit time
        $update_exit_stmt = $conn->prepare("UPDATE student_attendance SET exit_time = ?, time_spent = ? WHERE id = ?");
        $update_exit_stmt->bind_param("iii", $exit_time, $time_spent, $log_id);
        $update_exit_stmt->execute();
        $update_exit_stmt->close();

        // Calculate the total time spent in all sessions
        $total_time_stmt = $conn->prepare("SELECT SUM(time_spent) FROM student_attendance WHERE event_id = ? AND student_email = ?");
        $total_time_stmt->bind_param("is", $event_id, $email);
        $total_time_stmt->execute();
        $total_time_stmt->bind_result($total_time_spent);
        $total_time_stmt->fetch();
        $total_time_stmt->close();

        // Convert event duration from minutes to seconds
        $required_time_spent = $event_duration * 60;

        if ($total_time_spent >= $required_time_spent) {
            // User has met the event duration requirement
            echo "<p>You've completed the required time for this event. Proceed with final registration.</p>";
            echo "<a href='final_registration.php?name=$name&email=$email&event_id=$event_id'>Complete Final Registration</a>";
        } else {
            // Notify user of remaining time to be completed
            $remaining_time = $required_time_spent - $total_time_spent;
            echo "<p>You've spent " . gmdate("H:i:s", $total_time_spent) . " in the event. You need to stay for " . gmdate("H:i:s", $remaining_time) . " more.</p>";
        }
    } else {
        echo "<p>You are outside the geofenced area. Please enter the geofence to participate in the event.</p>";
    }
}

// Insert registration details into the 'registrations' table (only if the registration is completed)
$insert_stmt = $conn->prepare("INSERT INTO registrations (name, email, latitude, longitude, event_id, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
$insert_stmt->bind_param("sssdis", $name, $email, $user_latitude, $user_longitude, $event_id, $ip_address);
$insert_stmt->execute();
$insert_stmt->close();

$conn->close();
?>
 
