<?php
// Start session and include the database connection file
session_start();
include('/var/www/html/db_connect.php'); // Include your database connection file here
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set default values for the first visit after login
if (!isset($_SESSION['selected_branch'])) {
    $_SESSION['selected_branch'] = null;
}
if (!isset($_SESSION['selected_club'])) {
    $_SESSION['selected_club'] = null;
}
if (!isset($_SESSION['update_type'])) {
    $_SESSION['update_type'] = 'events'; // Default to 'events'
}

$selectedBranch = $_SESSION['selected_branch'];
$selectedClub = $_SESSION['selected_club'];
$updateType = $_SESSION['update_type'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ... (rest of your code remains the same)
}

// Fetch branches and clubs based on selected branch
$branchesResult = $conn->query("SELECT * FROM branches");
$clubsResult = $selectedBranch ? $conn->prepare("SELECT * FROM clubs WHERE branch_id = ?") : null;
if ($clubsResult) {
    $clubsResult->bind_param("i", $selectedBranch);
    $clubsResult->execute();
    $clubsResult = $clubsResult->get_result();
}

// Fetch events and recruitments based on selected club
$eventsResult = $selectedClub ? $conn->prepare("SELECT * FROM events WHERE club_id = ?") : null;
$recruitmentsResult = $selectedClub ? $conn->prepare("SELECT * FROM recruitments WHERE club_id = ?") : null;
if ($eventsResult) {
    $eventsResult->bind_param("i", $selectedClub);
    $eventsResult->execute();
    $eventsResult = $eventsResult->get_result();
}
if ($recruitmentsResult) {
    $recruitmentsResult->bind_param("i", $selectedClub);
    $recruitmentsResult->execute();
    $recruitmentsResult = $recruitmentsResult->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Members Area</title>
</head>
<body>
    <h1>Members Area</h1>

    <?php
    if (isset($_SESSION['message'])) {
        echo "<p>" . $_SESSION['message'] . "</p>";
        unset($_SESSION['message']);
    }
    ?>

    <?php if (!$selectedBranch): ?>
        <form method="post">
            <h2>Select Branch</h2>
            <select name="branch_id" onchange="this.form.submit()">
                <option value="">Select Branch</option>
                <?php while ($branch = $branchesResult->fetch_assoc()): ?>
                    <option value="<?php echo $branch['id']; ?>">
                        <?php echo $branch['branch_name']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <input type="hidden" name="select_branch" value="1">
        </form>
    <?php elseif (!$selectedClub): ?>
        <form method="post">
            <h2>Select Club</h2>
            <select name="club_id" onchange="this.form.submit()">
                <option value="">Select Club</option>
                <?php while ($club = $clubsResult->fetch_assoc()): ?>
                    <option value="<?php echo $club['id']; ?>">
                        <?php echo $club['club_name']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <input type="hidden" name="select_club" value="1">
        </form>
    <?php elseif (!$updateType): ?>
        <form method="post">
            <h2>Select Update Type</h2>
            <select name="update_type" onchange="this.form.submit()">
                <option value="events">Events</option>
                <option value="recruitments">Recruitments</option>
            </select>
            <input type="hidden" name="select_update_type" value="1">
        </form>
    <?php else: ?>
        <?php if ($updateType == 'events'): ?>
            <h2>Events</h2>
            <form method="post">
                <h3>Add Event</h3>
                <label>Title:</label>
                <input type="text" name="event_title" required>
                <label>Description:</label>
                <textarea name="event_description" required></textarea>
                <input type="hidden" name="club_id" value="<?php echo $selectedClub; ?>">
                <
