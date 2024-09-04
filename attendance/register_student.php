<?php
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $event_id = $_POST['event_id'];
    $student_name = $_POST['student_name'];
    $student_id = $_POST['student_id'];
    $geofence_status = $_POST['geofence_status'];
    $total_time_in_geofence = $_POST['total_time_in_geofence'];

    // Fetch event details from the database
    $stmt = $conn->prepare("SELECT event_start_time, event_duration FROM events WHERE id = ?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $stmt->bind_result($event_start_time, $event_duration);
    $stmt->fetch();
    $stmt->close();

    $event_end_time = new DateTime($event_start_time);
    $event_end_time->add(new DateInterval("PT{$event_duration}M"));
    
    $current_time = new DateTime("now", new DateTimeZone("UTC"));
    
    // Calculate remaining time for event completion
    $time_left = $current_time->diff($event_end_time);

    // Check if the student has spent the required time in the geofence area
    if ($total_time_in_geofence >= $event_duration) {
        echo "Registration successful! The final registration link will be available after the event completes.";
        // Here you would typically insert the registration record into the database
    } else {
        echo "You have not spent enough time within the geofence to qualify for final registration.";
    }
}
?>
