<?php
// Start session and include the database connection file
session_start();
include('/var/www/html/db_connect.php'); // Include your database connection file here
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Handle deletion
if (isset($_POST['delete'])) {
    $event_id = $_POST['event_id'];
    $delete_sql = "DELETE FROM events WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $event_id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Event deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting the event.";
    }
    $stmt->close();
}

// Handle addition
if (isset($_POST['add'])) {
    $club_id = $_POST['club_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];

    $add_sql = "INSERT INTO events (club_id, title, description, category) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($add_sql);
    $stmt->bind_param("isss", $club_id, $title, $description, $category);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Event added successfully!";
    } else {
        $_SESSION['error'] = "Error adding the event.";
    }
    $stmt->close();
}

// Fetch clubs
$clubs_sql = "SELECT id, club_name FROM clubs";
$clubs_result = $conn->query($clubs_sql);
?>

<!DOCTYPE html>
	@@ -52,77 +78,75 @@
<body>
    <h1>Manage Club Events</h1>

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

    <!-- Club Selection -->
    <form method="GET" action="members.php">
        <label for="club">Select Club:</label>
        <select id="club" name="club_id" onchange="this.form.submit()" required>
            <option value="">--Select Club--</option>
            <?php while ($club = $clubs_result->fetch_assoc()): ?>
                <option value="<?php echo $club['id']; ?>" <?php echo isset($_GET['club_id']) && $_GET['club_id'] == $club['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($club['club_name']); ?>
                </option>
            <?php endwhile; ?>
        </select>
    </form>

    <?php
    if (isset($_GET['club_id'])):
        $club_id = $_GET['club_id'];
        $events_sql = "SELECT id, title, description, category FROM events WHERE club_id = ?";
        $stmt = $conn->prepare($events_sql);
        $stmt->bind_param("i", $club_id);
        $stmt->execute();
        $events_result = $stmt->get_result();
    ?>
    <!-- Display Events -->
    <h2>Events for <?php echo htmlspecialchars($clubs_result->fetch_assoc()['club_name']); ?></h2>
    <?php while ($event = $events_result->fetch_assoc()): ?>
        <div class="event">
            <h3><?php echo htmlspecialchars($event['title']); ?></h3>
            <p><?php echo htmlspecialchars($event['description']); ?></p>
            <p>Category: <?php echo htmlspecialchars($event['category']); ?></p>
            <form method="POST" action="members.php">
                <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                <button type="submit" name="delete">Delete</button>
            </form>
        </div>
    <?php endwhile; ?>

    <!-- Add Event Form -->
    <h3>Add New Event</h3>
    <form method="POST" action="members.php">
        <input type="hidden" name="club_id" value="<?php echo $club_id; ?>">
        <label for="title">Title:</label><br>
        <input type="text" id="title" name="title" required><br><br>

        <label for="description">Description:</label><br>
        <textarea id="description" name="description" rows="4" required></textarea><br><br>

        <label for="category">Category:</label><br>
        <select id="category" name="category" required>
            <option value="events">Events</option>
            <option value="recruitment">Recruitments</option>
        </select><br><br>

        <button type="submit" name="add">Add Event</button>
    </form>

    <?php
    endif;
    ?>

</body>
</html>
