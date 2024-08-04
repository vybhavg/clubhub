<?php
// Include database connection and start session
require '/var/www/html/db_connect.php';
session_start(); // Make sure session is started

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if form fields are set
    if (isset($_POST['club_id']) && isset($_FILES['resume'])) {
        $name = htmlspecialchars($_POST['name']);
        $email = htmlspecialchars($_POST['email']);
        $club_id = intval($_POST['club_id']);
        $resume = $_FILES['resume'];

        // Ensure student ID is set in the session
        if (!isset($_SESSION['student_id'])) {
            die("Student ID is not set in the session.");
        }
        $student_id = $_SESSION['student_id'];

        // Directory where resume will be uploaded
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($resume["name"]);
        $uploadOk = 1;
        $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if file is a real PDF
        if ($fileType != "pdf") {
            echo "Sorry, only PDF files are allowed.";
            $uploadOk = 0;
        }

        // Check file size (optional, e.g., max 5MB)
        if ($resume["size"] > 5000000) {
            echo "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        // Upload file if all checks are passed
        if ($uploadOk == 1) {
            if (move_uploaded_file($resume["tmp_name"], $target_file)) {
                // Prepare an insert statement
                $stmt = $conn->prepare("INSERT INTO applications (student_id, club_id, resume_path) VALUES (?, ?, ?)");
                $stmt->bind_param("iis", $student_id, $club_id, $target_file);

                // Execute the statement
                if ($stmt->execute()) {
                    echo "The file " . htmlspecialchars(basename($resume["name"])) . " has been uploaded and your application has been submitted.";
                } else {
                    echo "Error: " . $stmt->error;
                }

                // Close statement
                $stmt->close();
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        }
    } else {
        echo "Please fill out all fields and upload a resume.";
    }
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
    <link rel="stylesheet" href="path_to_your_css_file.css">
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
                <label for="club_id">Club ID:</label>
                <input type="number" id="club_id" name="club_id" required>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Submit Application</button>
            </div>
        </form>
    </div>
</body>
</html>
