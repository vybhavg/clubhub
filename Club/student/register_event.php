<?php
session_start();
include('/var/www/html/db_connect.php'); // Ensure this file connects to your database correctly
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: student_login.php'); // Redirect to login page if not logged in
    exit();
}

$student_id = $_SESSION['student_id'];
$event_id = isset($_GET['event_id']) ? $_GET['event_id'] : '';

if ($event_id) {
    // Insert registration record
    $stmt_register = $conn->prepare("INSERT INTO event_registrations (student_id, event_id) VALUES (?, ?)");
    $stmt_register->bind_param("ii", $student_id, $event_id);

    if ($stmt_register->execute()) {
        $_SESSION['message'] = "Successfully registered for the event!";
    } else {
        $_SESSION['message'] = "Error registering for the event.";
    }

    $stmt_register->close();
} else {
    $_SESSION['message'] = "Invalid event ID.";
}

$conn->close();
header('Location: index.php?update_type=events'); // Redirect back to events page
?>
