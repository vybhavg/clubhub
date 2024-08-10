<?php
// Include database connection
require '/var/www/html/db_connect.php';

// Start session
session_start();

// Display errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ensure club_id is set in session or request
if (!isset($_SESSION['selected_club']) || empty($_SESSION['selected_club'])) {
    echo "No club selected.";
    exit();
}

// Get the club_id from session
$club_id = $_SESSION['selected_club'];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['apply'])) {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $resume = $_FILES['resume'];

    // Directory where resume will be uploaded
    $target_dir = "uploads/";
    // Ensure directory exists and is writable
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0755, true); // Create directory if it doesn't exist
    }
    $target_file = $target_dir . basename($resume["name"]);
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if file is a real PDF
    if ($fileType != "pdf") {
        $_SESSION['message'] = "Sorry, only PDF files are allowed.";
        $uploadOk = 0;
    }

    // Check file size (optional, e.g., max 5MB)
    if ($resume["size"] > 5000000) {
        $_SESSION['message'] = "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Upload file if all checks are passed
    if ($uploadOk == 1) {
        if (move_uploaded_file($resume["tmp_name"], $target_file)) {
            // Check if student already exists
            $stmt = $conn->prepare("SELECT id FROM students WHERE name = ? AND email = ?");
            $stmt->bind_param("ss", $name, $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Student exists, get the student ID
                $student = $result->fetch_assoc();
                $student_id = $student['id'];

                // Update the student's record if needed
                $stmt = $conn->prepare("UPDATE students SET name = ?, email = ? WHERE id = ?");
                $stmt->bind_param("ssi", $name, $email, $student_id);
                $stmt->execute();
                $stmt->close();
            } else {
                // Insert new student record
                $stmt = $conn->prepare("INSERT INTO students (name, email) VALUES (?, ?)");
                $stmt->bind_param("ss", $name, $email);
                $stmt->execute();
                $student_id = $stmt->insert_id; // Get the newly inserted student ID
                $stmt->close();
            }

            // Insert or update the application record
            $stmt = $conn->prepare("INSERT INTO applications (student_id, club_id, resume_path) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE resume_path = ?");
            $stmt->bind_param("iiss", $student_id, $club_id, $target_file, $target_file);

            // Execute the statement
            if ($stmt->execute()) {
                $_SESSION['message'] = "The file " . htmlspecialchars(basename($resume["name"])) . " has been uploaded and your application has been submitted.";
            } else {
                $_SESSION['message'] = "Error: " . $stmt->error;
            }

            // Close statement
            $stmt->close();
        } else {
            $_SESSION['message'] = "Sorry, there was an error uploading your file.";
            $_SESSION['message'] .= " Temporary file path: " . $resume["tmp_name"] . "<br>";
            $_SESSION['message'] .= " Target path: " . $target_file;
        }
    } else {
        $_SESSION['message'] = "File upload failed.";
    }

    header("Location: student.php"); // Redirect to avoid resubmission on refresh
    exit();
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Recruitment</title>
    <link rel="stylesheet" href="student.css">
</head>
<body>
    <div class="container">
        <h2>Apply for Recruitment</h2>
        <form action="student.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="resume">Upload Resume (PDF only):</label>
                <input type="file" id="resume" name="resume" accept=".pdf" required>
            </div>
            <div class="form-group">
                <button type="submit" name="apply" class="btn btn-primary">Submit Application</button>
            </div>
        </form>
        <?php if (isset($_SESSION['message'])) { ?>
            <p><?php echo $_SESSION['message']; ?></p>
            <?php unset($_SESSION['message']); ?>
        <?php } ?>
    </div>
</body>
</html>
