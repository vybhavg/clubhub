<?php
include('/var/www/html/db_connect.php'); // Include your database connection

// Start the session
session_start();

// Get data from the form and cast to appropriate types
$name = $_POST['name'];
$email = $_POST['email'];
$user_latitude = (float) $_POST['latitude'];  // Cast to float
$user_longitude = (float) $_POST['longitude']; // Cast to float
$event_id = (int) $_POST['event_id']; // Cast to integer

// Fetch the event details from the database
$stmt = $conn->prepare("SELECT title, event_start_time, event_duration, event_end_time, latitude, longitude FROM forms WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$stmt->bind_result($event_title, $event_start_time, $event_duration, $event_end_time, $event_latitude, $event_longitude);
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

// Set server time to IST
$server_timezone = new DateTimeZone('UTC'); // Assuming server is in UTC
$ist_timezone = new DateTimeZone('Asia/Kolkata');

// Get current server time and convert to IST
$current_time = new DateTime('now', $server_timezone);
$current_time->setTimezone($ist_timezone);
$current_time_timestamp = $current_time->getTimestamp(); // Use this timestamp for time calculations

// Convert event start and end times to IST
$event_start_time_ist = new DateTime($event_start_time, $ist_timezone);
$event_end_time_ist = new DateTime($event_end_time, $ist_timezone);

// Calculate time differences
$time_until_start = $event_start_time_ist->getTimestamp() - $current_time_timestamp;
$time_until_end = $event_end_time_ist->getTimestamp() - $current_time_timestamp;

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
    // Check if the user has an existing entry
    $entry_check_stmt = $conn->prepare("SELECT id, entry_time, exit_time FROM student_attendance WHERE event_id = ? AND student_email = ?");
    $entry_check_stmt->bind_param("is", $event_id, $email);
    $entry_check_stmt->execute();
    $entry_check_stmt->bind_result($log_id, $entry_time, $exit_time);
    $entry_check_stmt->fetch();
    $entry_check_stmt->close();

    if ($entry_time && !$exit_time) {
        // If the user entered before the event started
        if ($entry_time < $event_start_time_ist->getTimestamp()) {
            // Adjust entry time to event start time
            $entry_time = $event_start_time_ist->getTimestamp();
        }

        // Check if the event has ended
        if ($time_until_end <= 0) {
            // Event has ended, log exit
            echo "<p>The event has ended. Logging your exit automatically.</p>";
            $exit_time = $event_end_time_ist->getTimestamp();  // Use event end time as exit time
            $time_spent = $exit_time - $entry_time;  // Calculate time spent from adjusted entry time

            // Update the log with the exit time
            $update_exit_stmt = $conn->prepare("UPDATE student_attendance SET exit_time = ?, time_spent = ? WHERE id = ?");
            $update_exit_stmt->bind_param("iii", $exit_time, $time_spent, $log_id);
            if (!$update_exit_stmt->execute()) {
                echo "Error updating exit_time: " . $update_exit_stmt->error;
            }
            $update_exit_stmt->close();

            echo "Exit logged: $exit_time, Time spent: $time_spent";  // Debugging statement

            // Display the link if the user has spent significant time at the event
            if ($time_spent >= $event_duration) {
                echo "<p>Thank you for attending the event! Here is your link: <a href='http://example.com/special-link'>Special Link</a></p>";

                // Insert into final_attendance if the duration is met
                $insert_final_attendance_stmt = $conn->prepare(
                    "INSERT INTO final_attendance (student_name, student_email, event_id, entry_time, exit_time, time_spent) 
                     VALUES (?, ?, ?, ?, ?, ?)"
                );
                $insert_final_attendance_stmt->bind_param("ssiiii", $name, $email, $event_id, $entry_time, $exit_time, $time_spent);
                
                if (!$insert_final_attendance_stmt->execute()) {
                    echo "Error inserting into final_attendance: " . $insert_final_attendance_stmt->error;
                } else {
                    echo "Successfully inserted into final_attendance!";
                }

                $insert_final_attendance_stmt->close();
            }

            exit();  // Stop further processing
        } else {
            echo "<p>You are already within the geofence.</p>";
        }
    } else {
        // Log the entry time (user enters geofence)
        if (!$entry_time) {
            // Adjust entry time if the user enters before the event start time
            $entry_time = max($current_time->getTimestamp(), $event_start_time_ist->getTimestamp());  // Use event start time if entry is before it
            $insert_entry_stmt = $conn->prepare("INSERT INTO student_attendance (student_name, student_email, event_id, entry_time) VALUES (?, ?, ?, ?)");
            $insert_entry_stmt->bind_param("ssis", $name, $email, $event_id, $entry_time);
            $insert_entry_stmt->execute();
            $insert_entry_stmt->close();

            echo "<p>Welcome! Your entry time has been logged. Continue participating in the event.</p>";
        } else {
            echo "<p>Entry time already logged.</p>";
        }
    }
}
else {
    // User is outside the geofence, check for exit
    $exit_check_stmt = $conn->prepare("SELECT id, entry_time, exit_time FROM student_attendance WHERE event_id = ? AND student_email = ?");
    $exit_check_stmt->bind_param("is", $event_id, $email);
    $exit_check_stmt->execute();
    $exit_check_stmt->bind_result($log_id, $entry_time, $exit_time);
    $exit_check_stmt->fetch();
    $exit_check_stmt->close();

    if ($entry_time && !$exit_time) {
        // The user is leaving the geofence, log exit time
        if ($time_until_end <= 0) {
            // The event has ended
            echo "Event ended. Logging exit.";
            $exit_time = $current_time->getTimestamp();  // Convert to timestamp for logging
            $time_spent = $exit_time - $entry_time;

            // Update the log with the exit time
            $update_exit_stmt = $conn->prepare("UPDATE student_attendance SET exit_time = ?, time_spent = ? WHERE id = ?");
            $update_exit_stmt->bind_param("iii", $exit_time, $time_spent, $log_id);
            if (!$update_exit_stmt->execute()) {
                echo "Error updating exit_time: " . $update_exit_stmt->error;
            }
            $update_exit_stmt->close();

            echo "Exit logged: $exit_time, Time spent: $time_spent";  // Debugging statement

            // Display the link if the user has spent significant time at the event
            // After logging the exit and calculating time_spent
           

  if ($time_spent >= $event_duration) {
    echo "<p>Thank you for attending the event! Here is your link: <a href='http://example.com/special-link'>Special Link</a></p>";
    
    // Debugging statement
    echo "Time spent: $time_spent, Event duration: $event_duration";  

    // Insert the student into final_attendance if the duration is met
    $insert_final_attendance_stmt = $conn->prepare(
        "INSERT INTO final_attendance (student_name, student_email, event_id, entry_time, exit_time, time_spent) 
         VALUES (?, ?, ?, ?, ?, ?)"
    );

    // Bind parameters
    $insert_final_attendance_stmt->bind_param("ssiiii", $name, $email, $event_id, $entry_time, $exit_time, $time_spent);

    // Execute and check for errors
    if (!$insert_final_attendance_stmt->execute()) {
        // Display error message if execution fails
        echo "Error inserting into final_attendance: " . $insert_final_attendance_stmt->error;
    } else {
        // Debugging statement for successful insertion
        echo "Inserting student into final_attendance with details: Name = $name, Email = $email, Event ID = $event_id, Entry Time = $entry_time, Exit Time = $exit_time, Time Spent = $time_spent";
        echo "Successfully inserted into final_attendance!";
    }

    // Close the prepared statement
    $insert_final_attendance_stmt->close();
}





            exit();  // Stop further processing
        } else {
            echo "Event not ended yet.";  // Debugging statement
        }
    } else {
        echo "No entry_time found.";  // Debugging statement
    }
}

// Function to get the user's IP address
function get_user_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

// Get the user's IP address
$ip_address = get_user_ip();

// Check if the student has already registered for the event
$check_registration_stmt = $conn->prepare("SELECT id FROM registrations WHERE email = ? AND event_id = ?");
$check_registration_stmt->bind_param("si", $email, $event_id);
$check_registration_stmt->execute();
$check_registration_stmt->store_result();

if ($check_registration_stmt->num_rows == 0) {
    // Register the student for the event
    $insert_stmt = $conn->prepare("INSERT INTO registrations (name, email, latitude, longitude, event_id, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
    $insert_stmt->bind_param("ssddis", $name, $email, $user_latitude, $user_longitude, $event_id, $ip_address);
    $insert_stmt->execute();
    $insert_stmt->close();
}

$check_registration_stmt->close();

// PRG pattern to prevent form resubmission
exit();

?>
