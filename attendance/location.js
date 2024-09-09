<!DOCTYPE html>
<html>
<head>
    <title>Event Tracking</title>
    <script>
        function sendLocationUpdate() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    var xhr = new XMLHttpRequest();
                    xhr.open("POST", "update_location.php", true);
                    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                    var params = "latitude=" + position.coords.latitude + "&longitude=" + position.coords.longitude;
                    xhr.send(params);
                }, function(error) {
                    console.error("Geolocation error: " + error.message);
                });
            } else {
                console.error("Geolocation is not supported by this browser.");
            }
        }

        // Send location updates every 5 minutes (300000 milliseconds)
        setInterval(sendLocationUpdate, 300000); // Adjust interval as needed
    </script>
</head>
<body>
    <h1>Event Tracking</h1>
    <!-- Your event content goes here -->
</body>
</html>
