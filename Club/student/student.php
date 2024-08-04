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
    $resume = $_FILES['resume'];

    // Directory where resumes will be uploaded
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($resume["name"]);
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if file is a real PDF
    if ($fileType != "pdf") {
        $_SESSION['message'] = "Sorry, only PDF files are allowed.";
        $uploadOk = 0;
    }

    // Check file size (e.g., max 5MB)
    if ($resume["size"] > 5000000) {
        $_SESSION['message'] = "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Upload file if all checks are passed
    if ($uploadOk == 1) {
        if (move_uploaded_file($resume["tmp_name"], $target_file)) {
            // Prepare and execute the SQL statement
            $sql = "INSERT INTO students (name, email, resume_path) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $name, $email, $target_file);

            if ($stmt->execute()) {
                $_SESSION['message'] = "Student added successfully.";
            } else {
                $_SESSION['message'] = "Error adding student.";
            }
            $stmt->close();
        } else {
            $_SESSION['message'] = "Sorry, there was an error uploading your file.";
        }
    }
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
    <form method="post" enctype="multipart/form-data">
        <label for="name">Name:</label>
        <input type="text" name="name" id="name" required><br><br>
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" required><br><br>
        <label for="resume">Upload Resume (PDF only):</label>
        <input type="file" name="resume" id="resume" accept=".pdf" required><br><br>
        <input type="submit" name="add_student" value="Add Student">
    </form>

    <h2>Existing Students</h2>
    <ul>
        <?php while ($student = $studentsResult->fetch_assoc()) { ?>
            <li>
                <?php echo $student['name']; ?> (<?php echo $student['email']; ?>) - 
                <a href="<?php echo $student['resume_path']; ?>" target="_blank">View Resume</a>
            </li>
        <?php } ?>
    </ul>

    <?php if (isset($_SESSION['message'])) { ?>
        <p><?php echo $_SESSION['message']; ?></p>
        <?php unset($_SESSION['message']); ?>
    <?php } ?>
</body>
</html>
