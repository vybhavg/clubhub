<?php
include('/var/www/html/db_connect.php');

// Start the session
session_start();

// Get data passed through the URL
$name = $_GET['name'];
$email = $_GET['email'];
$event_id = $_GET['event_id'];

// Ensure data is available
if (!$name || !$email || !$event_id) {
    echo "<p>Missing required data for final registration.</p>";
    exit;
}

// Fetch event (form) details
$stmt = $conn->prepare("SELECT title, event_duration FROM forms WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$stmt->bind_result($event_title, $event_duration);
$stmt->fetch();
$stmt->close();

// Log final registration
$final_registration_stmt = $conn->prepare("INSERT INTO final_registrations (name, email, event_id) VALUES (?, ?, ?)");
$final_registration_stmt->bind_param("ssi", $name, $email, $event_id);
$final_registration_stmt->execute();
$final_registration_stmt->close();

echo "<p>Thank you, $name! You have successfully completed the registration for the event: $event_title.</p>";

$conn->close();
?>
