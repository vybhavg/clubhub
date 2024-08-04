<?php
// Start session and include the database connection file
session_start();
include('/var/www/html/db_connect.php'); // Include your database connection file here
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Handle adding new students
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_student'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];

    // Prepare and execute the SQL statement
    $sql = "INSERT INTO students (name, email) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $name, $email);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Student added successfully.";
    } else {
        $_SESSION['message'] = "Error adding student.";
    }
    $stmt->close();
}

// Fetch the list of students
$studentsResult = $conn->query("SELECT * FROM students");

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students</title>
    <link rel="stylesheet" type="text/css" href="students.css">
</head>
<body>
    <h1>Manage Students</h1>
    
    <!-- Form to add a new student -->
    <form method="post">
        <label for="name">Name:</label>
        <input type="text" name="name" id="name" required><br><br>
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" required><br><br>
        <input type="submit" name="add_student" value="Add Student">
    </form>

    <h2>Existing Students</h2>
    <ul>
        <?php while ($student = $studentsResult->fetch_assoc()) { ?>
            <li>
                <?php echo $student['name']; ?> (<?php echo $student['email']; ?>)
            </li>
        <?php } ?>
    </ul>

    <?php if (isset($_SESSION['message'])) { ?>
        <p><?php echo $_SESSION['message']; ?></p>
        <?php unset($_SESSION['message']); ?>
    <?php } ?>
</body>
</html>
