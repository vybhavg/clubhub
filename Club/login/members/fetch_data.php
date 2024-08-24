<?php
require_once '/var/www/html/db_connect.php'; // Include your database connection

// Get the selected branch, club, and update type from the POST request
$updateType = $_POST['updateType'];
$selectedBranch = $_POST['branch'];
$selectedClub = $_POST['club'];

$response = [];

if ($updateType == 'events') {
    $query = "SELECT * FROM events WHERE branch_id = ? AND club_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $selectedBranch, $selectedClub);
    $stmt->execute();
    $result = $stmt->get_result();
    $events = $result->fetch_all(MYSQLI_ASSOC);
    $response['events'] = $events;
} elseif ($updateType == 'recruitments') {
    $query = "SELECT * FROM recruitments WHERE branch_id = ? AND club_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $selectedBranch, $selectedClub);
    $stmt->execute();
    $result = $stmt->get_result();
    $recruitments = $result->fetch_all(MYSQLI_ASSOC);
    $response['recruitments'] = $recruitments;
} elseif ($updateType == 'applications') {
    $query = "SELECT * FROM applications WHERE branch_id = ? AND club_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $selectedBranch, $selectedClub);
    $stmt->execute();
    $result = $stmt->get_result();
    $applications = $result->fetch_all(MYSQLI_ASSOC);
    $response['applications'] = $applications;
}

echo json_encode($response); // Return data as JSON
?>
