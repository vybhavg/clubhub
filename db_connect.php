<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection settings
$db_host = '127.0.0.1'; // Localhost for the same EC2 instance
$db_username = 'root'; // Replace with your database username
$db_password = 'Vybhav@123ABC!'; // Replace with your database password
$db_name = 'mydatabase'; // Replace with your database name

// Create a connection to the database
$conn = new mysqli($db_host, $db_username, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
