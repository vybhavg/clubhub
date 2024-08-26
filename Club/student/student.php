<?php
// Include database connection
require '/var/www/html/db_connect.php';

// Start session
session_start();

// Display errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if club_id is provided in the URL
if (isset($_GET['club_id']) && !empty($_GET['club_id'])) {
    $club_id = intval($_GET['club_id']);
    $_SESSION['selected_club'] = $club_id; // Store it in session for later use
} elseif (isset($_SESSION['selected_club']) && !empty($_SESSION['selected_club'])) {
    $club_id = $_SESSION['selected_club'];
} else {
    echo "No club selected.";
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['apply'])) {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $resume = $_FILES['resume'];

    // Directory where resume will be uploaded
    $target_dir = "/var/www/html/Club/student/uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    
    // Unique filename to avoid conflicts
    $unique_name = uniqid() . "_" . basename($resume["name"]);
    $target_file = $target_dir . $unique_name;
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if file is a PDF
    if ($fileType != "pdf") {
        $_SESSION['message'] = "Sorry, only PDF files are allowed.";
        $uploadOk = 0;
    }

    // Check file size (optional, max 5MB)
    if ($resume["size"] > 5000000) {
        $_SESSION['message'] = "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Upload file if all checks are passed
    if ($uploadOk == 1) {
        if (move_uploaded_file($resume["tmp_name"], $target_file)) {
            // Check if student already exists
            $stmt = $conn->prepare("SELECT id FROM students WHERE name = ? AND email = ?");
            if ($stmt === false) {
                die('Prepare failed: ' . htmlspecialchars($conn->error));
            }
            $stmt->bind_param("ss", $name, $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Student exists, get the student ID
                $student = $result->fetch_assoc();
                $student_id = $student['id'];

                // Update the student's record (optional)
                $stmt = $conn->prepare("UPDATE students SET name = ?, email = ? WHERE id = ?");
                if ($stmt === false) {
                    die('Prepare failed: ' . htmlspecialchars($conn->error));
                }
                $stmt->bind_param("ssi", $name, $email, $student_id);
                if (!$stmt->execute()) {
                    die('Execute failed: ' . htmlspecialchars($stmt->error));
                }
                $stmt->close();
            } else {
                // Insert new student record
                $stmt = $conn->prepare("INSERT INTO students (name, email) VALUES (?, ?)");
                if ($stmt === false) {
                    die('Prepare failed: ' . htmlspecialchars($conn->error));
                }
                $stmt->bind_param("ss", $name, $email);
                if (!$stmt->execute()) {
                    die('Execute failed: ' . htmlspecialchars($stmt->error));
                }
                $student_id = $stmt->insert_id;
                $stmt->close();
            }

            // Insert or update the application record
            $stmt = $conn->prepare("INSERT INTO applications (student_id, club_id, resume_path) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE resume_path = ?");
            if ($stmt === false) {
                die('Prepare failed: ' . htmlspecialchars($conn->error));
            }
            $stmt->bind_param("iiss", $student_id, $club_id, $target_file, $target_file);
            if (!$stmt->execute()) {
                die('Execute failed: ' . htmlspecialchars($stmt->error));
            }
            $_SESSION['message'] = "The file " . htmlspecialchars(basename($resume["name"])) . " has been uploaded and your application has been submitted.";
            $stmt->close();
        } else {
            // Improved error logging
            $uploadError = $_FILES['resume']['error'];
            $_SESSION['message'] = "Sorry, there was an error uploading your file. Error code: $uploadError";
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
