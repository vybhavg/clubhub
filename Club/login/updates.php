<?php
// update_updates.php

include('/var/www/html/db_connect.php'); // Ensure this path is correct

// Clear existing updates
$conn->query("TRUNCATE TABLE updates");

// Insert events into updates
$sql = "INSERT INTO updates (title, description, category) 
        SELECT title, description, 'events' AS category 
        FROM events";
$conn->query($sql);

// Insert recruitments into updates
$sql = "INSERT INTO updates (title, description, category) 
        SELECT title, description, 'recruitments' AS category 
        FROM recruitments";
$conn->query($sql);

$conn->close();
?>
