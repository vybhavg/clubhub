<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection settings
$db_host = '127.0.0.1';
$db_username = 'root';
$db_password = 'Vybhav@123ABC!';
$db_name = 'mydatabase';

// Create a connection to the database
$conn = new mysqli($db_host, $db_username, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$register_error_message = "";
$register_success_message = "";
$login_error_message = "";

// Handle registration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register_name'], $_POST['register_college_email'], $_POST['register_roll_number'], $_POST['register_pass'])) {
    $register_name = $_POST['register_name'];
    $register_college_email = $_POST['register_college_email'];
    $register_roll_number = $_POST['register_roll_number'];
    $register_password = password_hash($_POST['register_pass'], PASSWORD_DEFAULT); // Hash the password

    // Check if all required fields are filled
    if (empty($register_name) || empty($register_college_email) || empty($register_roll_number) || empty($register_password)) {
        $register_error_message = "All fields are required!";
    } else {
        // Prepare the SQL statement to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO student_login_details (student_name, college_email, roll_number, password) VALUES (?, ?, ?, ?)");
        if ($stmt === false) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("ssss", $register_name, $register_college_email, $register_roll_number, $register_password);

        if ($stmt->execute()) {
            $register_success_message = "Registration successful!";
        } else {
            $register_error_message = "Registration failed: " . $stmt->error;
        }

        $stmt->close();
    }
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login_username'], $_POST['login_pass'])) {
    $login_username = $_POST['login_username'];
    $login_password = $_POST['login_pass'];

    // Prepare the SQL statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, student_name, password FROM student_login_details WHERE roll_number = ? OR college_email = ?");
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("ss", $login_username, $login_username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();

        // Verify the password
        if (password_verify($login_password, $student['password'])) {
            // Login successful, start session and set session variables
            session_start();
            $_SESSION['student_id'] = $student['id'];
            $_SESSION['student_name'] = $student['student_name'];
            header('Location: students/students.php'); // Change this to your student page
            exit;
        } else {
            $login_error_message = "Invalid roll number or password";
        }
    } else {
        $login_error_message = "Invalid roll number or password";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login and Register</title>
    <link rel="icon" type="image/png" href="images/icons/favicon.ico" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="fonts/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="fonts/Linearicons-Free-v1.0.0/icon-font.min.css">
    <link rel="stylesheet" type="text/css" href="vendor/animate/animate.css">
    <link rel="stylesheet" type="text/css" href="vendor/css-hamburgers/hamburgers.min.css">
    <link rel="stylesheet" type="text/css" href="vendor/animsition/css/animsition.min.css">
    <link rel="stylesheet" type="text/css" href="vendor/select2/select2.min.css">
    <link rel="stylesheet" type="text/css" href="vendor/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" type="text/css" href="util.css">
    <link rel="stylesheet" type="text/css" href="login.css">
    <meta name="robots" content="noindex, follow">
</head>
<body>
   
    <div class="container" id="container">
        <!-- Sign Up Form -->
        <div class="form-container sign-up">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <h1>Create Account</h1>
                <br>
                <input type="text" name="register_name" placeholder="Student Name">
                <input type="email" name="register_college_email" placeholder="College Email">
                <input type="text" name="register_roll_number" placeholder="Roll Number">
                <input type="password" name="register_pass" placeholder="Password">
                <button type="submit">Sign Up</button>
                <div id="register-success-message" style="color: green;"><?php echo htmlspecialchars($register_success_message); ?></div>
                <div id="register-error-message" style="color: red;"><?php echo htmlspecialchars($register_error_message); ?></div>
            </form>
        </div>

        <!-- Sign In Form -->
        <div class="form-container sign-in">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <h1>Sign In</h1>
                <br>
                <input type="text" name="login_username" placeholder="Roll Number or College Email">
                <input type="password" name="login_pass" placeholder="Password">
                <a href="#">Forget Your Password?</a>
                <button type="submit">Sign In</button>
                <div id="login-error-message" style="color: red;"><?php echo htmlspecialchars($login_error_message); ?></div>
            </form>
        </div>

        <!-- Toggle Panels -->
        <div class="toggle-container">
            <div class="toggle">
                <div class="toggle-panel toggle-left">
                    <h1>Hello, Students!</h1>
                    <p>Register with your student details</p>
                    <br>
                    <p>Already have an account?</p> 
                    <button class="hidden" id="login-toggle">Sign In</button>
                </div>
                <div class="toggle-panel toggle-right">
                    <h1>Welcome Back!</h1>
                    <p>Enter your student details</p>
                    <br>
                    <p>Don't have an account?</p> 
                    <button class="hidden" id="register-toggle">Sign Up</button>
                </div>
            </div>
        </div>
    </div>

    <script src="vendor/jquery/jquery-3.2.1.min.js"></script>
    <script src="vendor/animsition/js/animsition.min.js"></script>
    <script src="vendor/bootstrap/js/popper.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="vendor/select2/select2.min.js"></script>
    <script src="vendor/daterangepicker/moment.min.js"></script>
    <script src="vendor/daterangepicker/daterangepicker.js"></script>
    <script src="login.js"></script>
    <script>
       document.getElementById('register-toggle').addEventListener('click', function() {
           document.getElementById('container').classList.add('active');
       });

       document.getElementById('login-toggle').addEventListener('click', function() {
           document.getElementById('container').classList.remove('active');
       });

       // Check if there is a registration error or success message, and switch to the Sign Up section
       const registerError = "<?php echo $register_error_message; ?>";
       const registerSuccess = "<?php echo $register_success_message; ?>";

       if (registerError || registerSuccess) {
           document.getElementById('container').classList.add('active');
       }
    </script>
</body>
</html>
