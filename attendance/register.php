<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Registration</title>
</head>
<body>
    <h1>Register for Event</h1>

    <!-- Displaying the geofence status -->
    <p id="status">Tracking location...</p>

    <!-- Registration Form -->
    <form action="register_student.php" method="post" id="registrationForm">
        <input type="hidden" name="event_id" value="<?php echo $event_id; ?>"> <!-- Event ID -->
        <input type="hidden" name="geofence_status" id="geofenceStatus" value="0">
        <input type="hidden" name="total_time_in_geofence" id="totalTimeInGeofence" value="0">

        <!-- Student details fields -->
        <input type="text" name="student_name" placeholder="Your Name" required><br>
        <input type="text" name="student_id" placeholder="Your Student ID" required><br>
        
        <button type="submit">Register</button>
    </form>

    <!-- Geofence Tracking Script -->
    <script src="geofence.js"></script>
</body>
</html>
