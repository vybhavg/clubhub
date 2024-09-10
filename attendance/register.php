<?php
include('/var/www/html/db_connect.php'); // Include your database connection

// Start the session
session_start();

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve POST data
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
    $event_id = isset($_POST['event_id']) ? (int) $_POST['event_id'] : null;

    // Validate the input
    if (empty($name) || empty($email) || $event_id === null) {
        echo "Required data is missing.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email format.";
    } else {
        // Prepare the SQL statement
        if ($stmt = $conn->prepare("INSERT INTO students (name, email) VALUES (?, ?)")) {
            // Bind the parameters
            $stmt->bind_param("ss", $name, $email);

            // Execute the statement
            if ($stmt->execute()) {
                // Get the ID of the newly inserted student
                $student_id = $stmt->insert_id;

                // Store student_id, event_id, and email in session
                $_SESSION['student_id'] = $student_id;
                $_SESSION['event_id'] = $event_id;
                $_SESSION['email'] = $email;

                // Close the statement
                $stmt->close();

                // Redirect to location.html and pass student_id and event_id as query parameters
                header("Location: location.html?student_id=$student_id&event_id=$event_id");
                exit();
            } else {
                echo "Error inserting data: " . $stmt->error;
            }
        } else {
            echo "Error preparing statement: " . $conn->error;
        }
    }

    // Close the connection
    $conn->close();
    exit();
}

// Fetch events from the database
$stmt = $conn->prepare("SELECT id, title FROM forms");
if ($stmt) {
    $stmt->execute();
    $stmt->bind_result($event_id, $event_title);
} else {
    echo "Error fetching events: " . $conn->error;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Registration</title>
</head>
<body>

    <h2>Register for an Event</h2>

    <form action="register.php" method="POST">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required><br><br>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br><br>

        <label for="event">Select Event:</label>
        <select id="event" name="event_id" required>
            <option value="">Select an event</option>
            <?php
            while ($stmt->fetch()) {
                echo "<option value=\"$event_id\">$event_title</option>";
            }
            ?>
        </select><br><br>

        <input type="submit" value="Proceed to Location">
    </form>

<?php
$stmt->close();
$conn->close();
?>

</body>
</html>
