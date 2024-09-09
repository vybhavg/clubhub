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
        var studentId = <?php echo json_encode($_SESSION['student_id']); ?>;

        // Function to get user's GPS location
        function getLocation(callback) {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    var latitude = position.coords.latitude;
                    var longitude = position.coords.longitude;
                    callback(latitude, longitude);
                }, showError);
            } else {
                alert("Geolocation is not supported by this browser.");
            }
        }

        function showError(error) {
            switch (error.code) {
                case error.PERMISSION_DENIED:
                    alert("User denied the request for Geolocation.");
                    break;
                case error.POSITION_UNAVAILABLE:
                    alert("Location information is unavailable.");
                    break;
                case error.TIMEOUT:
                    alert("The request to get user location timed out.");
                    break;
                case error.UNKNOWN_ERROR:
                    alert("An unknown error occurred.");
                    break;
            }
        }

        // Handle form submission
        function handleSubmit(event) {
            event.preventDefault(); // Prevent the default form submission

            var form = document.querySelector('form');
            var formData = new FormData(form);
            var event_id = formData.get('event_id');
            var email = formData.get('email');

            getLocation(function(latitude, longitude) {
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "/path/to/track_location.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

                var data = "student_id=" + encodeURIComponent(studentId) +
                           "&event_id=" + encodeURIComponent(event_id) +
                           "&latitude=" + encodeURIComponent(latitude) +
                           "&longitude=" + encodeURIComponent(longitude) +
                           "&student_email=" + encodeURIComponent(email);

                xhr.send(data);

                // Redirect to location.js after data is sent
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        window.location.href = "/path/to/location.js"; // Adjust the path as needed
                    } else {
                        alert("Error sending location data.");
                    }
                };
            });
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
        <input type="hidden" id="latitude" name="latitude" required>
        <input type="hidden" id="longitude" name="longitude" required>
    </form>

<?php
$stmt->close();
$conn->close();
?>

</body>
</html>
