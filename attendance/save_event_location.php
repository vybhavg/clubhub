<?php
include('/var/www/html/db_connect.php'); // Include your database connection

// Debugging: Check if the POST data is being received correctly
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $event_name = isset($_POST['event_name']) ? $_POST['event_name'] : '';
    $latitude = isset($_POST['latitude']) ? $_POST['latitude'] : '';
    $longitude = isset($_POST['longitude']) ? $_POST['longitude'] : '';

    // Debugging: Print received data
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";

    if ($latitude && $longitude && $event_name) {
        $stmt = $conn->prepare("INSERT INTO forms (title, latitude, longitude) VALUES (?, ?, ?)");
        $stmt->bind_param("sdd", $event_name, $latitude, $longitude);

        if ($stmt->execute()) {
            echo "Event location saved successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Invalid data!";
    }

    $conn->close();
} else {
    echo "Invalid request method!";
}
?>
