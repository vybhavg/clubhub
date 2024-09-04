<?php
include('/var/www/html/db_connect.php'); // Include your database connection

$token = $_POST['token']; // Get the token from the form

// Verify the token again before processing registration
$stmt = $conn->prepare("SELECT * FROM event_tokens WHERE token = ? AND used = 0");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Process the registration
    // Update token as used
    $stmt = $conn->prepare("UPDATE event_tokens SET used = 1 WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();

    // Insert registration into event_attendance table (as mentioned earlier)
    // ...

    echo "Registration successful!";
} else {
    // Invalid or used token
    echo "Invalid or expired registration link.";
}

$stmt->close();
$conn->close();
?>
