<?php
// Set the content type to JSON
header('Content-Type: application/json');

// Get the raw POST data
$input = file_get_contents('php://input');

// Log the received data for debugging (optional)
error_log('Received data: ' . $input);

// Decode the JSON input
$data = json_decode($input, true);

// Check if JSON decoding was successful
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON format']);
    exit;
}

// Validate the required fields
if (!isset($data['student_id'], $data['event_id'], $data['latitude'], $data['longitude'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required data']);
    exit;
}

$student_id = $data['student_id'];
$event_id = $data['event_id'];
$latitude = $data['latitude'];
$longitude = $data['longitude'];

// Perform any additional processing or database insertion here
// For demonstration, we'll just return a success message with the data

// Respond with success
http_response_code(200);
echo json_encode([
    'message' => 'Location data received successfully',
    'student_id' => $student_id,
    'event_id' => $event_id,
    'latitude' => $latitude,
    'longitude' => $longitude
]);
?>
