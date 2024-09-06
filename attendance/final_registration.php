<?php
// final_registration.php
include('/var/www/html/db_connect.php');

$event_id = $_GET['event_id'] ?? null;
$student_id = $_GET['student_id'] ?? null;

if (!$event_id || !$student_id) {
    die('Invalid access!');
}

// Code to handle the final registration process
echo "Final registration for event ID: $event_id and student ID: $student_id is complete!";

// Perform necessary registration logic here

$conn->close();
?>
