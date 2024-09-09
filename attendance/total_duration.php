<?php
include('/var/www/html/db_connect.php'); // Include your database connection

// Start the session
session_start();

// Check if student_id is set in the session
if (!isset($_SESSION['student_id'])) {
    die('Student ID not found. Please log in.');
}

$student_id = $_SESSION['student_id'];

// Get the latest event details
$stmt = $conn->prepare("SELECT event_id FROM student_attendance WHERE student_id = ? AND exit_time IS NULL ORDER BY entry_time DESC LIMIT 1");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$stmt->bind_result($event_id);
$stmt->fetch();
$stmt->close();

if ($event_id) {
    // Get location updates
    $stmt = $conn->prepare("SELECT timestamp FROM location_updates WHERE student_id = ? AND event_id = ? ORDER BY timestamp");
    $stmt->bind_param("ii", $student_id, $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $previous_time = null;
    $total_time_spent = 0;

    while ($row = $result->fetch_assoc()) {
        $current_time = strtotime($row['timestamp']);
        if ($previous_time !== null) {
            $total_time_spent += $current_time - $previous_time;
        }
        $previous_time = $current_time;
    }

    // Update exit time if event has ended
    $stmt = $conn->prepare("SELECT end_time FROM events WHERE id = ?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $stmt->bind_result($event_end_time);
    $stmt->fetch();
    $stmt->close();

    if ($event_end_time) {
        $event_end_time_timestamp = strtotime($event_end_time);
        if ($previous_time && $previous_time < $event_end_time_timestamp) {
            $total_time_spent += $event_end_time_timestamp - $previous_time;
        }
    }

    // Update total time spent in attendance table
    $stmt = $conn->prepare("UPDATE student_attendance SET time_spent = ? WHERE student_id = ? AND event_id = ? AND exit_time IS NULL");
    $stmt->bind_param("iii", $total_time_spent, $student_id, $event_id);
    if (!$stmt->execute()) {
        echo "Error updating time spent: " . $stmt->error;
    }
    $stmt->close();
}

$conn->close();
?>

