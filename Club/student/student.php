<?php
// Include database connection
require '/var/www/html/db_connect.php';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $resume = $_FILES['resume'];

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
            $stmt = $conn->prepare("INSERT INTO applications (name, email, resume_path) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $target_file);

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
<link rel="stylesheet" href="path_to_your_css_file/students.css">
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
                <button type="submit" class="btn btn-primary">Submit Application</button>
            </div>
        </form>
    </div>
</body>
</html>
