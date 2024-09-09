<?php
include('/var/www/html/db_connect.php');

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

            // Redirect to location.html with form data
            var url = "location.html?" + new URLSearchParams(formData).toString();
            window.location.href = url;
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
