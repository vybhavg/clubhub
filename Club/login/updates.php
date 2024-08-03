<?php

include('/var/www/html/db_connect.php'); // Ensure this path is correct


// Clear existing updates
if ($conn->query("TRUNCATE TABLE updates") === FALSE) {
    error_log("Error truncating table: " . $conn->error);
}

// Insert events into updates
$sql = "INSERT INTO updates (title, description, category) 
        SELECT title, description, 'events' AS category 
        FROM events";
if ($conn->query($sql) === FALSE) {
    error_log("Error inserting events: " . $conn->error);
}

// Insert recruitments into updates
$sql = "INSERT INTO updates (title, description, category) 
        SELECT title, description, 'recruitments' AS category 
        FROM recruitments";
if ($conn->query($sql) === FALSE) {
    error_log("Error inserting recruitments: " . $conn->error);
}

$conn->close();
?>
