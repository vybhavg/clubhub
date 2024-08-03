<?php
session_start();
include('../db_connect.php'); // Adjust the path if needed

$selectedBranch = $_SESSION['selected_branch'] ?? null;
$selectedClub = $_SESSION['selected_club'] ?? null;
$updateType = $_SESSION['update_type'] ?? 'events'; // Default to 'events'

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_event'])) {
        // Handle adding events
        $title = $_POST['event_title'];
        $description = $_POST['event_description'];
        $club_id = $_POST['club_id'];

        $sql = "INSERT INTO events (title, description, club_id) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $title, $description, $club_id);
        $stmt->execute();
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
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['delete_event'])) {
        // Handle deleting events
        $event_id = $_POST['event_id'];
        $sql = "DELETE FROM events WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $event_id);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['delete_recruitment'])) {
        // Handle deleting recruitments
        $recruitment_id = $_POST['recruitment_id'];
        $sql = "DELETE FROM recruitments WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $recruitment_id);
        $stmt->execute();
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

$branchesResult = $conn->query("SELECT * FROM branches");
$clubsResult = $conn->query("SELECT * FROM clubs WHERE branch_id = $selectedBranch");
$eventsResult = $conn->query("SELECT * FROM events WHERE club_id = $selectedClub");
$recruitmentsResult = $conn->query("SELECT * FROM recruitments WHERE club_id = $selectedClub");

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

    <form method="post">
        <h2>Select Branch</h2>
        <select name="branch_id" onchange="this.form.submit()">
            <option value="">Select Branch</option>
            <?php while ($branch = $branchesResult->fetch_assoc()): ?>
                <option value="<?php echo $branch['id']; ?>" <?php echo ($branch['id'] == $selectedBranch) ? 'selected' : ''; ?>>
                    <?php echo $branch['name']; ?>
                </option>
            <?php endwhile; ?>
        </select>
        <input type="hidden" name="select_branch" value="1">
    </form>

    <?php if ($selectedBranch): ?>
        <form method="post">
            <h2>Select Club</h2>
            <select name="club_id" onchange="this.form.submit()">
                <option value="">Select Club</option>
                <?php while ($club = $clubsResult->fetch_assoc()): ?>
                    <option value="<?php echo $club['id']; ?>" <?php echo ($club['id'] == $selectedClub) ? 'selected' : ''; ?>>
                        <?php echo $club['club_name']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <input type="hidden" name="select_club" value="1">
        </form>
    <?php endif; ?>

    <?php if ($selectedClub): ?>
        <form method="post">
            <h2>Select Update Type</h2>
            <select name="update_type" onchange="this.form.submit()">
                <option value="events" <?php echo ($updateType == 'events') ? 'selected' : ''; ?>>Events</option>
                <option value="recruitments" <?php echo ($updateType == 'recruitments') ? 'selected' : ''; ?>>Recruitments</option>
            </select>
            <input type="hidden" name="select_update_type" value="1">
        </form>

        <?php if ($updateType == 'events'): ?>
            <h2>Events</h2>
            <form method="post">
                <h3>Add Event</h3>
                <label>Title:</label>
                <input type="text" name="event_title" required>
                <label>Description:</label>
                <textarea name="event_description" required></textarea>
                <input type="hidden" name="club_id" value="<?php echo $selectedClub; ?>">
                <input type="submit" name="add_event" value="Add Event">
            </form>
            <?php while ($event = $eventsResult->fetch_assoc()): ?>
                <div>
                    <h4><?php echo htmlspecialchars($event['title']); ?></h4>
                    <p><?php echo htmlspecialchars($event['description']); ?></p>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                        <input type="submit" name="delete_event" value="Delete Event">
                    </form>
                </div>
            <?php endwhile; ?>

        <?php elseif ($updateType == 'recruitments'): ?>
            <h2>Recruitments</h2>
            <form method="post">
                <h3>Add Recruitment</h3>
                <label>Role:</label>
                <input type="text" name="role" required>
                <label>Description:</label>
                <textarea name="recruitment_description" required></textarea>
                <label>Deadline:</label>
                <input type="date" name="deadline" required>
                <input type="hidden" name="club_id" value="<?php echo $selectedClub; ?>">
                <input type="submit" name="add_recruitment" value="Add Recruitment">
            </form>
            <?php while ($recruitment = $recruitmentsResult->fetch_assoc()): ?>
                <div>
                    <h4><?php echo htmlspecialchars($recruitment['role']); ?></h4>
                    <p><?php echo htmlspecialchars($recruitment['description']); ?></p>
                    <p>Deadline: <?php echo htmlspecialchars($recruitment['deadline']); ?></p>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="recruitment_id" value="<?php echo $recruitment['id']; ?>">
                        <input type="submit" name="delete_recruitment" value="Delete Recruitment">
                    </form>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    <?php endif; ?>

</body>
</html>
