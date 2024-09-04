<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Registration</title>
    <style>
        .form-container {
            margin: 50px;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 300px;
        }
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Event Registration</h2>
        <form id="registrationForm" action="register_student.php" method="POST">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required><br><br>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br><br>

            <label for="event_id">Select Event:</label>
            <select id="event_id" name="event_id" required>
                <!-- Event options will be populated by PHP -->
                <?php
                include('/var/www/html/db_connect.php');
                $result = $conn->query("SELECT id, title FROM events");

                while ($row = $result->fetch_assoc()) {
                    echo "<option value='{$row['id']}'>{$row['title']}</option>";
                }

                $result->close();
                $conn->close();
                ?>
            </select><br><br>

            <input type="hidden" id="latitude" name="latitude">
            <input type="hidden" id="longitude" name="longitude">

            <button type="submit">Register</button>
        </form>
    </div>

    <script>
        // Optional: You can include a map or other JavaScript for geolocation here
    </script>
</body>
</html>
