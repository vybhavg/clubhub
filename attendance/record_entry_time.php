<?php
// record_entry_time.php
include('/var/www/html/db_connect.php');

$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : null;
$student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : null;

if ($event_id && $student_id) {
    $entry_time = time(); // Store current time as the entry time
    // Save the entry time in the 'student_event' table
    $stmt = $conn->prepare("UPDATE student_event SET entry_time = ? WHERE event_id = ? AND student_id = ?");
    $stmt->bind_param("iii", $entry_time, $event_id, $student_id);
    $stmt->execute();
    echo "Entry time recorded";
} else {
    echo "Invalid access";
}

$conn->close();
?>
