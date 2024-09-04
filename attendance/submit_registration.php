<?php
include('/var/www/html/db_connect.php');

// Check if token and event_id are set in POST request
if (isset($_POST['token']) && isset($_POST['event_id'])) {
    $token = $_POST['token'];
    $event_id = $_POST['event_id'];
} else {
    die('Token or Event ID is missing!');
}

// Validate the token
$stmt = $conn->prepare("SELECT event_id, student_id FROM event_tokens WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($valid_event_id, $student_id);
    $stmt->fetch();

    // Check if the event ID matches the token's event ID
    if ($valid_event_id == $event_id) {
        // Process registration
        // Make sure to validate the student and event details
        $stmt = $conn->prepare("INSERT INTO registrations (event_id, student_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $event_id, $student_id);

        if ($stmt->execute()) {
            echo "Registration successful!";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Invalid event for this token!";
    }
} else {
    echo "Invalid token!";
}

$conn->close();
?>
