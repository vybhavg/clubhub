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
        die('Required data is missing.');
    }

    // Insert student data into the students table
    $stmt = $conn->prepare("INSERT INTO students (name, email) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $email);
    if ($stmt->execute()) {
        // Get the ID of the newly inserted student
        $student_id = $stmt->insert_id;
        $stmt->close();

        // Redirect to location.html with student_id, event_id, and email
        $url = "location.html?" . http_build_query([
            'student_id' => $student_id,
            'event_id' => $event_id,
            'email' => $email
        ]);
        header("Location: $url");
        exit();
    } else {
        echo "Error inserting data: " . $stmt->error;
    }

    $conn->close();
    exit();
}

// Fetch events from the database
$stmt = $conn->prepare("SELECT id, title FROM forms");
$stmt->execute();
$stmt->bind_result($event_id, $event_title);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Registration</title>
    <script>
        // Handle form submission
        function handleSubmit(event) {
            event.preventDefault(); // Prevent default form submission

            var form = document.querySelector('form');
            var formData = new FormData(form);

            // Send form data to the server
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "register.php", true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    var response = xhr.responseText;
                    // Check if response indicates a successful redirect
                    if (response.indexOf('Redirecting') !== -1) {
                        var url = response.match(/href="([^"]*)"/)[1];
                        window.location.href = url;
                    } else {
                        alert("Error: " + response);
                    }
                } else {
                    alert("Error submitting form. Please try again.");
                }
            };
            xhr.send(formData);
        }

        window.onload = function() {
            var form = document.querySelector('form');
            form.addEventListener('submit', handleSubmit);
        };
    </script>
</head>
<body>

    <h2>Register for an Event</h2>

    <form>
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

        <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($_SESSION['student_id'] ?? ''); ?>">

        <input type="submit" value="Proceed to Location">
    </form>

<?php
$stmt->close();
$conn->close();
?>

</body>
</html>
