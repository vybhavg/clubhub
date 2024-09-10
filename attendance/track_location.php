<?php
include('/var/www/html/db_connect.php'); 
session_start();

header('Content-Type: application/json'); // Ensure the response is JSON

// Check if session data is set
if (!isset($_SESSION['student_id']) || !isset($_SESSION['event_id']) || !isset($_SESSION['email'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Required session data is missing.']);
    exit;
}

// Retrieve session data
$student_id = $_SESSION['student_id'];
$event_id = $_SESSION['event_id'];
$email = $_SESSION['email'];

// Handle POST request for location updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read the JSON input
    $data = json_decode(file_get_contents('php://input'), true);

    // Check if JSON data is valid
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON']);
        exit;
    }

    // Retrieve data from JSON
    $student_id = isset($data['student_id']) ? (int) $data['student_id'] : null;
    $event_id = isset($data['event_id']) ? (int) $data['event_id'] : null;
    $email = isset($data['email']) ? filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL) : '';
    $latitude = isset($data['latitude']) ? (float) $data['latitude'] : null;
    $longitude = isset($data['longitude']) ? (float) $data['longitude'] : null;

    // Log received data for debugging
    error_log('Received data: ' . print_r($data, true));

    if ($student_id === null || $event_id === null || $latitude === null || $longitude === null || empty($email)) {
        http_response_code(400);
        echo json_encode(['error' => 'Required data is missing']);
        exit;
    }

    // Prepare SQL query to insert data into the `locations` table
    $stmt = $conn->prepare("INSERT INTO locations (student_id, event_id, latitude, longitude, email) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iidds", $student_id, $event_id, $latitude, $longitude, $email);

    if ($stmt->execute()) {
        echo json_encode(['success' => 'Location updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
    exit();
}

http_response_code(405); // Method Not Allowed
echo json_encode(['error' => 'Invalid request method']);
?>
