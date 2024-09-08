<?php
session_start();
include('/var/www/html/db_connect.php'); // Ensure this file connects to your database correctly
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Get the student_id from the session
$student_id = isset($_SESSION['student_id']) ? $_SESSION['student_id'] : 0;
$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;
$club_id = isset($_GET['club_id']) ? intval($_GET['club_id']) : 0;

// Fetch the student's name from the `students` table based on `student_id`
$stmt_fetch_name = $conn->prepare("SELECT name FROM students WHERE id = ?");
$stmt_fetch_name->bind_param("i", $student_id);
$stmt_fetch_name->execute();
$stmt_fetch_name->bind_result($name);
$stmt_fetch_name->fetch();
$stmt_fetch_name->close();

// If no name is found, set an error message
if (empty($name)) {
    $_SESSION['message'] = "Student name not found.";
    header("Location: error_page.php"); // Redirect to an error page or show an error message
    exit;
}

// Insert the event registration data including the student name
$stmt = $conn->prepare("INSERT INTO event_registrations (name, student_id, event_id, club_id) VALUES (?, ?, ?, ?)");
$stmt->bind_param("siii", $name, $student_id, $event_id, $club_id);

if ($stmt->execute()) {
    $_SESSION['message'] = "Event registration successful!";
} else {
    $_SESSION['message'] = "Error registering for event.";
}

// Close the statement and connection
$stmt->close();
$conn->close();

// Redirect to a confirmation page or back to the events page
header("Location: confirmation_page.php");
exit;
?>
