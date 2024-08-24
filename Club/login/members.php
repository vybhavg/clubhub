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
$updateType = isset($_GET['update_type']) ? $_GET['update_type'] : $_SESSION['update_type'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_event'])) {
        // Handle adding events
        $title = $_POST['event_title'];
        $description = $_POST['event_description'];
        $club_id = $_POST['club_id'];

        $sql = "INSERT INTO events (title, description, club_id) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $title, $description, $club_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Event added successfully.";
        } else {
            $_SESSION['message'] = "Error adding event.";
        }
        $stmt->close();
    } elseif (isset($_POST['add_recruitment'])) {
        // Handle adding recruitments
        $role = $_POST['role'];
        $description = $_POST['recruitment_description'];
        $deadline = $_POST['deadline'];
        $club_id = $_POST['club_id'];

        $sql = "INSERT INTO recruitments (role, description, deadline, club_id) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $role, $description, $deadline, $club_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Recruitment added successfully.";
        } else {
            $_SESSION['message'] = "Error adding recruitment.";
        }
        $stmt->close();
    } elseif (isset($_POST['delete_event'])) {
        // Handle deleting events
        $event_id = $_POST['event_id'];
        $sql = "DELETE FROM events WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $event_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Event deleted successfully.";
        } else {
            $_SESSION['message'] = "Error deleting event.";
        }
        $stmt->close();
    } elseif (isset($_POST['delete_recruitment'])) {
        // Handle deleting recruitments
        $recruitment_id = $_POST['recruitment_id'];
        $sql = "DELETE FROM recruitments WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $recruitment_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Recruitment deleted successfully.";
        } else {
            $_SESSION['message'] = "Error deleting recruitment.";
        }
        $stmt->close();
    } elseif (isset($_POST['select_branch'])) {
        $_SESSION['selected_branch'] = $_POST['branch_id'];
        $selectedBranch = $_POST['branch_id'];
    } elseif (isset($_POST['select_club'])) {
        $_SESSION['selected_club'] = $_POST['club_id'];
        $selectedClub = $_POST['club_id'];
    }
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

// Fetch applications based on selected club
$applicationsResult = $selectedClub ? $conn->prepare("SELECT s.name as student_name, s.email as email, a.resume_path as resume_path FROM applications a INNER JOIN students s ON a.student_id = s.id WHERE a.club_id = ?") : null;
if ($applicationsResult) {
    $applicationsResult->bind_param("i", $selectedClub);
    $applicationsResult->execute();
    $applicationsResult = $applicationsResult->get_result();
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club Management System</title>
    <link rel="stylesheet" type="text/css" href="members.css">
</head>
<body>
    <header>
        <div class="navbar">
            <div class="navbar-logo">
                <h1>Club Management System</h1>
            </div>
            <nav class="navbar-menu">
                <a href="index.php">Home</a>
                <a href="?update_type=events">Events</a>
                <a href="?update_type=recruitments">Recruitments</a>
                <a href="?update_type=applications">Applications</a>
            </nav>
            <div class="navbar-mobile-menu">
                <button class="navbar-toggle" id="navbar-toggle">
                    <span class="navbar-toggle-icon">&#9776;</span>
                </button>
                <nav class="navbar-mobile-links" id="navbar-mobile-links">
                    <a href="index.php">Home</a>
                    <a href="?update_type=events">Events</a>
                    <a href="?update_type=recruitments">Recruitments</a>
                    <a href="?update_type=applications">Applications</a>
                </nav>
            </div>
        </div>
    </header>

    <section class="welcome-section">
        <div class="welcome-background">
            <div class="welcome-text">
                <h2>Welcome, Club Members!</h2>
                <p>Manage your events, recruitments, and applications efficiently.</p>
            </div>
        </div>
    </section>


    <form method="post">
        <label for="branch_id">Select Branch:</label>
        <select name="branch_id" id="branch_id">
            <option value="">Select Branch</option>
            <?php while ($branch = $branchesResult->fetch_assoc()) { ?>
                <option value="<?php echo $branch['id']; ?>" <?php if ($selectedBranch == $branch['id']) echo 'selected'; ?>><?php echo $branch['branch_name']; ?></option>
            <?php } ?>
        </select>
        <input type="submit" name="select_branch" value="Select Branch">
    </form>

    <form method="post">
        <label for="club_id">Select Club:</label>
        <select name="club_id" id="club_id">
            <option value="">Select Club</option>
            <?php if ($clubsResult) { 
                while ($club = $clubsResult->fetch_assoc()) { ?>
                    <option value="<?php echo $club['id']; ?>" <?php if ($selectedClub == $club['id']) echo 'selected'; ?>><?php echo $club['club_name']; ?></option>
            <?php } } ?>
        </select>
        <input type="submit" name="select_club" value="Select Club">
    </form>

    <?php if ($updateType == 'events') { ?>
        <h2>Events</h2>
        <form method="post">
            <label for="event_title">Event Title:</label>
            <input type="text" name="event_title" id="event_title"><br><br>
            <label for="event_description">Event Description:</label>
            <textarea name="event_description" id="event_description"></textarea><br><br>
            <input type="hidden" name="club_id" value="<?php echo $selectedClub; ?>">
            <input type="submit" name="add_event" value="Add Event">
        </form>

        <h2>Existing Events</h2>
        <ul>
            <?php while ($event = $eventsResult->fetch_assoc()) { ?>
                <li>
                    <?php echo $event['title']; ?> (<?php echo $event['description']; ?>)
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                        <input type="submit" name="delete_event" value="Delete">
                    </form>
                </li>
            <?php } ?>
        </ul>

    <?php } elseif ($updateType == 'recruitments') { ?>
        <h2>Recruitments</h2>
        <form method="post">
            <label for="role">Role:</label>
            <input type="text" name="role" id="role"><br><br>
            <label for="recruitment_description">Description:</label>
            <textarea name="recruitment_description" id="recruitment_description"></textarea><br><br>
            <label for="deadline">Deadline:</label>
            <input type="date" name="deadline" id="deadline"><br><br>
            <input type="hidden" name="club_id" value="<?php echo $selectedClub; ?>">
            <input type="submit" name="add_recruitment" value="Add Recruitment">
        </form>

        <h2>Existing Recruitments</h2>
        <ul>
            <?php while ($recruitment = $recruitmentsResult->fetch_assoc()) { ?>
                <li>
                    <?php echo $recruitment['role']; ?> (<?php echo $recruitment['description']; ?>, Deadline: <?php echo $recruitment['deadline']; ?>)
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="recruitment_id" value="<?php echo $recruitment['id']; ?>">
                        <input type="submit" name="delete_recruitment" value="Delete">
                    </form>
                </li>
            <?php } ?>
        </ul>

    <?php } elseif ($updateType == 'applications') { ?>
        <h2>Applications</h2>
        <ul>
            <?php while ($application = $applicationsResult->fetch_assoc()) { ?>
                <li>
                    <?php echo $application['student_name']; ?> (<?php echo $application['email']; ?>)
                    <a href="<?php echo $application['resume_path']; ?>" target="_blank">View Resume</a>
                </li>
            <?php } ?>
        </ul>

    <?php } ?>
</body>
</html>
