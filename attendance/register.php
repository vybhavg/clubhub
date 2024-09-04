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
            <div>
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div>
                <label for="event_id">Select Event:</label>
                <select id="event_id" name="event_id" required>
                    <?php
                    include('/var/www/html/db_connect.php');

                    $stmt = $conn->prepare("SELECT id, title FROM forms");
                    $stmt->execute();
                    $stmt->bind_result($id, $title);

                    while ($stmt->fetch()) {
                        echo "<option value='{$id}'>{$title}</option>";
                    }

                    $stmt->close();
                    $conn->close();
                    ?>
                </select>
            </div>
            <div>
                <label for="latitude">Latitude:</label>
                <input type="text" id="latitude" name="latitude" required>
            </div>
            <div>
                <label for="longitude">Longitude:</label>
                <input type="text" id="longitude" name="longitude" required>
            </div>
            <button type="submit">Register</button>
        </form>
        <div id="event-link" class="hidden">
            <p>Event registration link: <a href="<!-- Your Link Here -->">Register Here</a></p>
        </div>
    </div>
</body>
</html>
