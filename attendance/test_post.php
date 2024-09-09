<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo json_encode(['message' => 'POST request successful']);
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Method not allowed']);
}
?>
