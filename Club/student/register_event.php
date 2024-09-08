<?php
session_start();
include('/var/www/html/db_connect.php'); // Database connection file
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if student is logged in
$student_id = isset($_SESSION['student_id']) ? $_SESSION['student_id'] : 0;
$event_id = isset($_GET['event_id']) ? $_GET['event_id'] : 0;

// Ensure both event_id and student_id are valid
if ($student_id && $event_id) {
    // Fetch student name and email from student_login_details table
    $stmt_fetch_student = $conn->prepare("SELECT student_name, college_email FROM student_login_details WHERE id = ?");
    $stmt_fetch_student->bind_param("i", $student_id);
    
    if ($stmt_fetch_student) {
        $stmt_fetch_student->execute();
        $stmt_fetch_student->bind_result($student_name, $college_email);
        $stmt_fetch_student->fetch();
        $stmt_fetch_student->close();
        
        if ($student_name && $college_email) {
            // Fetch club_id from the events table
            $stmt_fetch_club = $conn->prepare("SELECT club_id FROM events WHERE id = ?");
            $stmt_fetch_club->bind_param("i", $event_id);
            if ($stmt_fetch_club) {
                $stmt_fetch_club->execute();
                $stmt_fetch_club->bind_result($club_id);
                $stmt_fetch_club->fetch();
                $stmt_fetch_club->close();

                if ($club_id) {
                    // Insert registration into event_registrations table without ip_address, latitude, and longitude for now
                    $stmt_insert_registration = $conn->prepare("
                        INSERT INTO event_registrations (event_id, student_id, club_id, name, email, registration_date)
                        VALUES (?, ?, ?, ?, ?, NOW())
                    ");
                    $stmt_insert_registration->bind_param("iisss", $event_id, $student_id, $club_id, $student_name, $college_email);

                    if ($stmt_insert_registration->execute()) {
                        // Redirect or show success message
                        $_SESSION['message'] = "You have successfully registered for the event!";
                    } else {
                        $_SESSION['message'] = "Error during registration: " . $conn->error;
                    }
                    $stmt_insert_registration->close();
                } else {
                    $_SESSION['message'] = "Invalid event or club.";
                }
            } else {
                $_SESSION['message'] = "Error fetching club information.";
            }
        } else {
            $_SESSION['message'] = "Invalid student information.";
        }
    } else {
        $_SESSION['message'] = "Error fetching student information.";
    }
} else {
    $_SESSION['message'] = "Invalid event or student information.";
}

// Close database connection
$conn->close();
// Redirect or display a message
header("Location: student.php?update_type=events#events"); // Update with your events page
exit();
?>
