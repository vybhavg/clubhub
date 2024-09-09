<?php
// Start session to capture student_id, event_id, and email from the previous form
session_start();

// Ensure the session has the required data (e.g., student_id, event_id, email)
if (!isset($_SESSION['student_id']) || !isset($_SESSION['event_id']) || !isset($_SESSION['email'])) {
    die('Required data not found. Please go back and complete registration.');
}

// Retrieve data from session
$student_id = $_SESSION['student_id'];
$event_id = $_SESSION['event_id'];
$email = $_SESSION['email'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Location Tracker</title>
    <script>
        // Function to send location data to the server using a form POST
        function sendLocationData(student_id, event_id, email, latitude, longitude) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'track_location.php';

            // Create input elements for the form data
            const studentInput = document.createElement('input');
            studentInput.type = 'hidden';
            studentInput.name = 'student_id';
            studentInput.value = student_id;

            const eventInput = document.createElement('input');
            eventInput.type = 'hidden';
            eventInput.name = 'event_id';
            eventInput.value = event_id;

            const emailInput = document.createElement('input');
            emailInput.type = 'hidden';
            emailInput.name = 'email';
            emailInput.value = email;

            const latitudeInput = document.createElement('input');
            latitudeInput.type = 'hidden';
            latitudeInput.name = 'latitude';
            latitudeInput.value = latitude;

            const longitudeInput = document.createElement('input');
            longitudeInput.type = 'hidden';
            longitudeInput.name = 'longitude';
            longitudeInput.value = longitude;

            // Append inputs to the form
            form.appendChild(studentInput);
            form.appendChild(eventInput);
            form.appendChild(emailInput);
            form.appendChild(latitudeInput);
            form.appendChild(longitudeInput);

            // Append form to the body and submit
            document.body.appendChild(form);
            form.submit();
        }

        // Function to track location and send it along with the student_id, event_id, and email
        function trackLocation() {
            const student_id = '<?php echo $student_id; ?>';
            const event_id = '<?php echo $event_id; ?>';
            const email = '<?php echo $email; ?>';

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (position) {
                    const latitude = position.coords.latitude;
                    const longitude = position.coords.longitude;

                    // Send the location data via form POST
                    sendLocationData(student_id, event_id, email, latitude, longitude);
                }, function (error) {
                    alert('Error fetching location: ' + error.message);
                });
            } else {
                alert('Geolocation is not supported by this browser.');
            }
        }

        // Execute trackLocation on page load
        window.onload = function () {
            trackLocation();
        };
    </script>
</head>
<body>
    <h1>Tracking Location</h1>
    <p>Your location is being tracked and submitted.</p>
</body>
</html>
