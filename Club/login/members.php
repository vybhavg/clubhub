<?php
// Start session and include the database connection file
session_start();
include('db_connect.php'); // Include your database connection file here

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the form data
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];

    // Insert the data into the database
    $sql = "INSERT INTO updates (title, description, category) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $title, $description, $category);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Update submitted successfully!";
    } else {
        $_SESSION['error'] = "There was an error submitting the update.";
    }
    
    $stmt->close();
    $conn->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Update</title>
</head>
<body>
    <h1>Submit a Recruitment Update</h1>

    <?php
    if (isset($_SESSION['success'])) {
        echo "<p style='color: green;'>" . $_SESSION['success'] . "</p>";
        unset($_SESSION['success']);
    }

    if (isset($_SESSION['error'])) {
        echo "<p style='color: red;'>" . $_SESSION['error'] . "</p>";
        unset($_SESSION['error']);
    }
    ?>

    <form action="members.php" method="post">
        <label for="title">Title:</label><br>
        <input type="text" id="title" name="title" required><br><br>

        <label for="description">Description:</label><br>
        <textarea id="description" name="description" rows="4" required></textarea><br><br>

        <label for="category">Category:</label><br>
        <select id="category" name="category" required>
            <option value="events">Events</option>
            <option value="recruitment">Recruitments</option>
        </select><br><br>

        <input type="submit" value="Submit">
    </form>
</body>
</html>
