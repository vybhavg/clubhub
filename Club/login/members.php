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

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_event'])) {
        // Handle adding events
        $title = $_POST['event_title'];
        $description = $_POST['event_description'];
        $club_id = $_POST['club_id'];

        $sql = "INSERT INTO events (title, description, club_id) VALUES (?,?,?)";
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

        $sql = "INSERT INTO recruitments (role, description, deadline, club_id) VALUES (?,?,?,?)";
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
        $sql = "DELETE FROM events WHERE id =?";
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
        $sql = "DELETE FROM recruitments WHERE id =?";
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
    } elseif (isset($_POST['select_update_type'])) {
        $_SESSION['update_type'] = $_POST['update_type'];
        $updateType = $_POST['update_type'];
    }
}

// Fetch branches and clubs based on selected branch
$branchesResult = $conn->query("SELECT * FROM branches");
$clubsResult = $selectedBranch? $conn->prepare("SELECT * FROM clubs WHERE branch_id =?") : null;
if ($clubsResult) {
    $clubsResult->bind_param("i", $selectedBranch);
    $clubsResult->execute();
    $clubsResult = $clubsResult->get_result();
}

// Fetch events and recruitments based on selected club
$eventsResult = $selectedClub? $conn->prepare("SELECT * FROM events WHERE club_id =?") : null;
$recruitmentsResult = $selectedClub? $conn->prepare("SELECT * FROM recruitments WHERE club_id =?") : null;
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

// Close the database connection
$conn->close();
?>

<!-- HTML code here -->
<html>
<head>
    <title>Club Management System</title>
</head>
<body>
    <h1>Club Management System</h1>
    <form method="post">
        <label for="branch_id">Select Branch:</label>
        <select name="branch_id" id="branch_id" onchange="this.form.submit()">
            <option value="">Select Branch</option>
            <?php while ($branch = $branchesResult->fetch_assoc()) {?>
                <option value="<?php echo $branch['id'];?>" <?php if ($selectedBranch == $branch['id']) echo 'elected';?>><?php echo $branch['name'];?></option>
            <?php }?>
        </select>
    </form>

    <form method="post">
        <label for="club_id">Select Club:</label>
        <select name="club_id" id="club_id" onchange="this.form.submit()">
            <option value="">Select Club</option>
            <?php while ($club = $clubsResult->fetch_assoc()) {?>
                <option value="<?php echo $club['id'];?>" <?php if ($selectedClub == $club['id']) echo 'elected';?>><?php echo $club['name'];?></option>
            <?php }?>
        </select>
    </form>

    <?php if ($selectedClub) {?>
        <h2>Events</h2>
        <ul>
            <?php while ($event = $eventsResult->fetch_assoc()) {?>
                <li>
                    <?php echo $event['title'];?> (<?php echo $event['description'];?>)
                </li>
            <?php }?>
        </ul>

        <h2>Recruitments</h2>
        <ul>
            <?php while ($recruitment = $recruitmentsResult->fetch_assoc()) {?>
                <li>
                    <?php echo $recruitment['role'];?> (<?php echo $recruitment['description'];?>) - Deadline: <?php echo $recruitment['deadline'];?>
                </li>
            <?php }?>
        </ul>
    <?php }?>
    
</body>
</html>
