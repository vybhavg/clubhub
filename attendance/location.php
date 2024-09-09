<?php
// Start session to capture student_id, event_id, and email from the previous form
session_start();

if (!isset($_SESSION['student_id']) || !isset($_SESSION['event_id']) || !isset($_SESSION['email'])) {
    die('Required session data is missing.');
}

// Retrieve session data
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
        // Function to send location data to the server in JSON format
        function sendLocationData(student_id, event_id, email, latitude, longitude) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'track_location.php', true);
            xhr.setRequestHeader('Content-Type', 'application/json');

            // Create a JSON object
            const data = JSON.stringify({
                student_id: student_id,
                event_id: event_id,
                email: email,
                latitude: latitude,
                longitude: longitude
            });

            xhr.send(data);

            xhr.onload = function () {
                if (xhr.status === 200) {
                    console.log('Location submitted successfully');
                } else {
                    console.log('Error submitting location');
                }
            };
        }

        // Function to track and send location periodically
        function trackLocationPeriodically(student_id, event_id, email) {
            if (navigator.geolocation) {
                setInterval(function () {
                    navigator.geolocation.getCurrentPosition(function (position) {
                        const latitude = position.coords.latitude;
                        const longitude = position.coords.longitude;

                        // Send location data to the server
                        sendLocationData(student_id, event_id, email, latitude, longitude);
                    }, function (error) {
                        console.error('Error fetching location: ' + error.message);
                    });
                }, 10000); // Send location every 10 seconds
            } else {
                alert('Geolocation is not supported by this browser.');
            }
        }

        // Start tracking once the page loads
        window.onload = function () {
            const student_id = <?php echo json_encode($student_id); ?>;
            const event_id = <?php echo json_encode($event_id); ?>;
            const email = <?php echo json_encode($email); ?>;

            trackLocationPeriodically(student_id, event_id, email);
        };
    </script>
</head>
<body>
    <h1>Location Tracker</h1>
    <p>Tracking your location...</p>
</body>
</html>
 
