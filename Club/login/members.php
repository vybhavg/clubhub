<?php
// Connect to the database
include('/var/www/db_connect.php'); // Adjust the path as needed

// Fetch branches
$branches_sql = "SELECT id, branch_name FROM branches";
$branches_result = $conn->query($branches_sql);

// Handle branch selection and fetch corresponding clubs
$selected_branch_id = isset($_POST['branch_id']) ? $_POST['branch_id'] : null;
<?php
$selected_club_id = isset($_POST['club_id']) ? $_POST['club_id'] : null;

// Fetch clubs for the selected branch
$clubs_result = null;
if ($selected_branch_id) {
    $clubs_sql = "SELECT id, club_name FROM clubs WHERE branch_id = ?";
    $stmt = $conn->prepare($clubs_sql);
    $stmt->bind_param("i", $selected_branch_id);
    $stmt->execute();
    $clubs_result = $stmt->get_result();
    $stmt->close();
}

// Fetch events for the selected club
$events_result = null;
if ($selected_club_id) {
    $events_sql = "SELECT id, event_name, event_description FROM events WHERE club_id = ?";
    $stmt = $conn->prepare($events_sql);
    $stmt->bind_param("i", $selected_club_id);
    $stmt->execute();
    $events_result = $stmt->get_result();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Members Page</title>
</head>
<body>
    <h1>Select Branch and Club</h1>
    
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

    <!-- Club Selection Form -->
    <?php if ($selected_branch_id): ?>
        <form method="post" action="members.php">
            <input type="hidden" name="branch_id" value="<?php echo $selected_branch_id; ?>">
            <label for="club_id">Select Club:</label>
            <select name="club_id" id="club_id" onchange="this.form.submit()">
                <option value="">--Select Club--</option>
                <?php if ($clubs_result): ?>
                    <?php while ($row = $clubs_result->fetch_assoc()): ?>
                        <option value="<?php echo $row['id']; ?>" <?php echo ($selected_club_id == $row['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($row['club_name']); ?>
                        </option>
                    <?php endwhile; ?>
                <?php endif; ?>
            </select>
        </form>
    <?php endif; ?>

    <!-- Display Events for Selected Club -->
    <?php if ($selected_club_id): ?>
        <h2>Events for Selected Club</h2>
        <?php if ($events_result): ?>
            <ul>
                <?php while ($event = $events_result->fetch_assoc()): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($event['event_name']); ?></strong>
                        <p><?php echo htmlspecialchars($event['event_description']); ?></p>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>No events found for this club.</p>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Add Recruitment Form -->
    <h2>Add Recruitment</h2>
    <form method="post" action="add_recruitment.php">
        <input type="hidden" name="club_id" value="<?php echo $selected_club_id; ?>">
        <label for="role">Role:</label>
        <input type="text" name="role" id="role" required>
        <label for="deadline">Deadline:</label>
        <input type="date" name="deadline" id="deadline" required>
        <label for="description">Description:</label>
        <textarea name="description" id="description" required></textarea>
        <button type="submit">Add Recruitment</button>
    </form>
</body>
</html>

<?php
$conn->close();
?>
