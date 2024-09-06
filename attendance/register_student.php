<?php
include('/var/www/html/db_connect.php');

// Start the session
session_start();

// Get data from the form
$name = $_POST['name'];
$email = $_POST['email'];
$user_latitude = (float) $_POST['latitude']; // Convert to float
$user_longitude = (float) $_POST['longitude']; // Convert to float
$event_id = $_POST['event_id'];

// Get the user's IP address
$ip_address = $_SERVER['REMOTE_ADDR'];

// Fetch the event (form) details from the database
$stmt = $conn->prepare("SELECT title, event_start_time, event_duration, latitude, longitude FROM forms WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$stmt->bind_result($event_title, $event_start_time, $event_duration, $event_latitude, $event_longitude);
$stmt->fetch();
$stmt->close();

// Convert event latitude and longitude to floats
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

// Time Zone conversion (IST)
$ist_timezone = new DateTimeZone('Asia/Kolkata');
$current_time_ist = new DateTime('now', $ist_timezone);

// Calculate event end time
$event_end_time = new DateTime($event_start_time, $ist_timezone);
$event_end_time->modify('+' . $event_duration . ' minutes');
$event_end_timestamp = $event_end_time->getTimestamp();

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
        $entry_time = $current_time_ist->getTimestamp();
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
        $exit_time = $current_time_ist->getTimestamp();
        $time_spent = min($exit_time, $event_end_timestamp) - $entry_time; // Ensure time does not exceed event end time

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
