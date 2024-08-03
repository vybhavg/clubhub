<?php
// Start session and include the database connection file
session_start();
include('/var/www/html/db_connect.php'); // Update path as needed

// Fetch branches
$branches_sql = "SELECT id, branch_name FROM branches";
$branches_result = $conn->query($branches_sql);

// Handle branch selection and fetch corresponding clubs
$selected_branch_id = isset($_POST['branch_id']) ? $_POST['branch_id'] : null;
$clubs_result = null;
if ($selected_branch_id) {
    $clubs_sql = "SELECT id, club_name FROM clubs WHERE branch_id = ?";
    $stmt = $conn->prepare($clubs_sql);
    $stmt->bind_param("i", $selected_branch_id);
    $stmt->execute();
    $clubs_result = $stmt->get_result();
    $stmt->close();
}

// Handle club selection and fetch corresponding events
$selected_club_id = isset($_POST['club_id']) ? $_POST['club_id'] : null;
$events_result = null;
if ($selected_club_id) {
    $events_sql = "SELECT id, event_name FROM events WHERE club_id = ?";
    $stmt = $conn->prepare($events_sql);
    $stmt->bind_param("i", $selected_club_id);
    $stmt->execute();
    $events_result = $stmt->get_result();
    $stmt->close();
}

// Handle adding an event
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_event'])) {
    $event_name = $_POST['event_name'];
    $add_event_sql = "INSERT INTO events (event_name, club_id) VALUES (?, ?)";
    $stmt = $conn->prepare($add_event_sql);
    $stmt->bind_param("si", $event_name, $selected_club_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Event added successfully!";
    } else {
        $_SESSION['error'] = "There was an error adding the event.";
    }

    $stmt->close();
    header("Location: members.php"); // Redirect to avoid form resubmission
    exit();
}

// Handle deleting an event
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_event'])) {
    $event_id = $_POST['event_id'];
    $delete_event_sql = "DELETE FROM events WHERE id = ?";
    $stmt = $conn->prepare($delete_event_sql);
    $stmt->bind_param("i", $event_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Event deleted successfully!";
    } else {
        $_SESSION['error'] = "There was an error deleting the event.";
    }

    $stmt->close();
    header("Location: members.php"); // Redirect to avoid form resubmission
    exit();
}
?>

<!DOCTYPE html>
	@@ -52,77 +78,75 @@
<body>
    <h1>Manage Club Events</h1>

    <!-- Display success or error messages -->
    <?php
    if (isset($_SESSION['success'])) {
        echo "<p style='color: green;'>" . $_SESSION['success'] . "</p>";
        unset($_SESSION['success']);
    }
    if (isset($_SESSION['error'])) {
        echo "<p style='color: red;'>" . $_SESSION['error'] . "</p>";
        unset($_SESSION['error']);
    }
    ?>

    <!-- Branch Selection Form -->
    <form method="post" action="members.php">
        <label for="branch_id">Select Branch:</label>
        <select name="branch_id" id="branch_id" onchange="this.form.submit()">
            <option value="">--Select Branch--</option>
            <?php while ($row = $branches_result->fetch_assoc()): ?>
                <option value="<?php echo $row['id']; ?>" <?php echo ($selected_branch_id == $row['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($row['branch_name']); ?>
                </option>
            <?php endwhile; ?>
        </select>
    </form>

    <?php if ($clubs_result): ?>
        <!-- Club Selection Form -->
        <form method="post" action="members.php">
            <input type="hidden" name="branch_id" value="<?php echo $selected_branch_id; ?>">
            <label for="club_id">Select Club:</label>
            <select name="club_id" id="club_id" onchange="this.form.submit()">
                <option value="">--Select Club--</option>
                <?php while ($row = $clubs_result->fetch_assoc()): ?>
                    <option value="<?php echo $row['id']; ?>" <?php echo ($selected_club_id == $row['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($row['club_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </form>
    <?php endif; ?>
