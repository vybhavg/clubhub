<?php
include('/var/www/html/db_connect.php'); // Include your database connection

// Function to calculate distance between two points using Haversine formula
function haversineGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000) {
    $latFrom = deg2rad($latitudeFrom);
    $lonFrom = deg2rad($longitudeFrom);
    $latTo = deg2rad($latitudeTo);
    $lonTo = deg2rad($longitudeTo);

    $latDelta = $latTo - $latFrom;
    $lonDelta = $lonTo - $lonFrom;

    $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
      cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
    return $angle * $earthRadius;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['event_name'])) {
        // Handling event location input
        $event_name = $_POST['event_name'];
        $latitude = $_POST['latitude'];
        $longitude = $_POST['longitude'];

        if ($latitude && $longitude && $event_name) {
            $stmt = $conn->prepare("INSERT INTO forms (title, latitude, longitude) VALUES (?, ?, ?)");
            $stmt->bind_param("sdd", $event_name, $latitude, $longitude);

            if ($stmt->execute()) {
                echo "Event location saved successfully!";
            } else {
                echo "Error: " . $stmt->error;
            }

            $stmt->close();
        } else {
            echo "Invalid data!";
        }
    } elseif (isset($_POST['name']) && isset($_POST['email'])) {
        // Handling student registration
        $name = $_POST['name'];
        $email = $_POST['email'];
        $latitude = $_POST['latitude'];
        $longitude = $_POST['longitude'];

        if ($latitude && $longitude && $name && $email) {
            $stmt = $conn->prepare("SELECT latitude, longitude FROM forms");
            $stmt->execute();
            $stmt->bind_result($eventLatitude, $eventLongitude);

            $isWithinGeofence = false;
            $geofenceRadius = 1000; // Geofence radius in meters

            while ($stmt->fetch()) {
                $distance = haversineGreatCircleDistance($latitude, $longitude, $eventLatitude, $eventLongitude);
                if ($distance <= $geofenceRadius) {
                    $isWithinGeofence = true;
                    break;
                }
            }

            $stmt->close();

            if ($isWithinGeofence) {
                $stmt = $conn->prepare("INSERT INTO registrations (name, email, latitude, longitude, ip_address) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("ssdds", $name, $email, $latitude, $longitude, $_SERVER['REMOTE_ADDR']);

                if ($stmt->execute()) {
                    echo "Registration successful!";
                } else {
                    echo "Error: " . $stmt->error;
                }

                $stmt->close();
            } else {
                echo "You are not within the geofenced area for any event.";
            }
        } else {
            echo "Invalid data!";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club Member & Student Registration</title>
    <style>
        .form-container {
            margin: 50px;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 300px;
        }
        #map {
            height: 400px;
            width: 100%;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>

    <!-- Event Location Input Form for Club Members -->
    <div class="form-container">
        <h2>Input Event Location</h2>
        <form id="locationForm" action="" method="POST">
            <label for="event_name">Event Name:</label>
            <input type="text" id="event_name" name="event_name" required><br><br>

            <div id="map"></div><br>

            <input type="hidden" id="latitude" name="latitude">
            <input type="hidden" id="longitude" name="longitude">

            <button type="submit">Save Location</button>
        </form>
    </div>

    <!-- Student Registration Form -->
    <div class="form-container">
        <h2>Student Registration</h2>
        <form id="registrationForm" action="" method="POST">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required><br><br>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br><br>

            <button type="button" onclick="getLocation()">Check Location & Register</button>

            <input type="hidden" id="latitude" name="latitude">
            <input type="hidden" id="longitude" name="longitude">

            <p id="locationStatus" class="error"></p>
        </form>
    </div>

    <script>
        function initMap() {
            var defaultLocation = {lat: 40.7128, lng: -74.0060}; // Default location (New York)
            var map = new google.maps.Map(document.getElementById('map'), {
                zoom: 10,
                center: defaultLocation
            });

            var marker = new google.maps.Marker({
                position: defaultLocation,
                map: map,
                draggable: true // Allow marker to be dragged
            });

            google.maps.event.addListener(marker, 'dragend', function(evt){
                document.getElementById('latitude').value = evt.latLng.lat().toFixed(8);
                document.getElementById('longitude').value = evt.latLng.lng().toFixed(8);
            });
        }

        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(showPosition, showError);
            } else {
                document.getElementById('locationStatus').innerHTML = "Geolocation is not supported by this browser.";
            }
        }

        function showPosition(position) {
            document.getElementById('latitude').value = position.coords.latitude;
            document.getElementById('longitude').value = position.coords.longitude;
            document.getElementById('registrationForm').submit();
        }

        function showError(error) {
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    document.getElementById('locationStatus').innerHTML = "User denied the request for Geolocation.";
                    break;
                case error.POSITION_UNAVAILABLE:
                    document.getElementById('locationStatus').innerHTML = "Location information is unavailable.";
                    break;
                case error.TIMEOUT:
                    document.getElementById('locationStatus').innerHTML = "The request to get user location timed out.";
                    break;
                case error.UNKNOWN_ERROR:
                    document.getElementById('locationStatus').innerHTML = "An unknown error occurred.";
                    break;
            }
        }
    </script>
    <script async defer
        src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap">
    </script>
</body>
</html>
