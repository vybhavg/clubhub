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
    <title>Event Registration with Geofence</title>
    <script>
        // Handle form submission
        function handleSubmit(event) {
            event.preventDefault(); // Prevent the default form submission

            var form = document.querySelector('form');
            var formData = new FormData(form);

            // Serialize FormData into URL-encoded format
            var serializedData = new URLSearchParams(formData).toString();
            console.log("Serialized data being sent: ", serializedData); // Debugging

            var xhr = new XMLHttpRequest();
            xhr.open("POST", "register.php", true); // Adjust this as needed
            xhr.onload = function() {
                if (xhr.status === 200) {
                    // Pass the data through URL query parameters
                    var params = new URLSearchParams(formData).toString();
                    window.location.href = "location.html?" + params; // Redirect with form data in URL
                } else {
                    alert("Error registering. Please try again.");
                }
            };
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.send(serializedData);
        }

        // Attach submit event listener to the form
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

        <!-- Hidden latitude and longitude fields automatically filled by GPS -->
        <input type="hidden" id="latitude" name="latitude">
        <input type="hidden" id="longitude" name="longitude">

        <!-- Hidden fields for student_id and event_id -->
        <input type="hidden" name="student_id" id="student_id" value="<?php echo htmlspecialchars($_SESSION['student_id'] ?? ''); ?>">

        <!-- Submit button -->
        <input type="submit" value="Register">
    </form>

<?php
$stmt->close();
$conn->close();
?>

</body>
</html>
