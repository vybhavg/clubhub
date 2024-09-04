<?php
include('/var/www/html/db_connect.php'); // Include your database connection

session_start();

if (!isset($_POST['student_id']) || !isset($_POST['event_id'])) {
    die('Access Denied');
}

$student_id = $_POST['student_id'];
$event_id = $_POST['event_id'];

// Check if student is eligible
$stmt = $conn->prepare("SELECT * FROM event_attendance WHERE student_id = ? AND event_id = ?");
$stmt->bind_param("ii", $student_id, $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('Access Denied: Not Eligible or Link Not Valid');
}

// Proceed with registration
$stmt = $conn->prepare("INSERT INTO registrations (student_id, event_id) VALUES (?, ?)");
$stmt->bind_param("ii", $student_id, $event_id);

if ($stmt->execute()) {
    echo "Registration successful!";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
