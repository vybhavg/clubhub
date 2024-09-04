<?php
include('/var/www/html/db_connect.php'); // Include your database connection

$token = $_GET['token']; // Get the token from the URL

// Verify the token in the database
$stmt = $conn->prepare("SELECT * FROM event_tokens WHERE token = ? AND used = 0");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Token is valid and not used
    // Display the final registration form
    echo "<form action='submit_registration.php' method='POST'>";
    echo "<input type='hidden' name='token' value='" . htmlspecialchars($token) . "'>";
    // Include other form fields like student ID, event ID, etc.
    echo "<button type='submit'>Complete Registration</button>";
    echo "</form>";
} else {
    // Invalid or used token
    echo "Invalid or expired registration link.";
}

$stmt->close();
$conn->close();
?>
