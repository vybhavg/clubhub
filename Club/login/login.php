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

$error_message = "";
$success_message = "";

// Handle registration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register_username'], $_POST['register_pass'], $_POST['club_name'], $_POST['branch_id'])) {
    $register_username = $_POST['register_username'];
    $register_password = password_hash($_POST['register_pass'], PASSWORD_DEFAULT); // Hash the password
    $club_name = $_POST['club_name'];
    $branch_id = $_POST['branch_id'];

    // Check if all required fields are filled
    if (empty($register_username) || empty($register_password) || empty($club_name) || empty($branch_id)) {
        $error_message = "All fields are required!";
    } else {
        // Prepare the SQL statement to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO clubs (club_name, username, password, branch_id) VALUES (?, ?, ?, ?)");
        if ($stmt === false) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("sssi", $club_name, $register_username, $register_password, $branch_id);

        if ($stmt->execute()) {
            $success_message = "Registration successful!";
        } else {
            $error_message = "Registration failed: " . $stmt->error;
        }

        $stmt->close();
    }
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['username'], $_POST['pass'])) {
    $username = $_POST['username'];
    $password = $_POST['pass'];

    // Prepare the SQL statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, club_name, password, branch_id FROM clubs WHERE username = ?");
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $club = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $club['password'])) {
            // Login successful, start session and set session variables
            session_start();
            $_SESSION['club_id'] = $club['id'];
            $_SESSION['club_name'] = $club['club_name'];
            $_SESSION['branch_id'] = $club['branch_id'];
            header('Location: members/members.php');
            exit;
        } else {
            $error_message = "Invalid username or password";
        }
    } else {
        $error_message = "Invalid username or password";
    }

    $stmt->close();
}

// Fetch branch options
$branch_options = '';
$branches = ['Visakhapatnam', 'Hyderabad', 'Bangalore'];
foreach ($branches as $key => $branch) {
    $branch_id = $key + 1; // Assuming IDs start from 1
    $branch_options .= "<option value=\"$branch_id\">$branch</option>";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login and Register</title>
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
        <div class="form-container sign-up" >
            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <h1>Create Account</h1>
                <br>
                <input type="text" name="club_name" placeholder="Club Name">
                <input type="text" name="register_username" placeholder="Username">
                <input type="password" name="register_pass" placeholder="Password">
                <div class="branch">
                <select name="branch_id" class="branch_id">
                    <option value="">Select Branch</option>
                    <?php echo $branch_options; ?>
                </select></div>
                <button type="submit">Sign Up</button>
                <div id="register-success-message" style="color: green;"><?php echo isset($success_message) ? htmlspecialchars($success_message) : ''; ?></div>
                <div id="register-error-message" style="color: red;"><?php echo htmlspecialchars($error_message); ?></div>
            </form>
        </div>

        <div class="form-container sign-in">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <h1>Sign In</h1>
                <br>
                <input type="text" name="username" placeholder="Username">
                <input type="password" name="pass" placeholder="Password">
                <a href="#">Forget Your Password?</a>
                <button type="submit">Sign In</button>
                <div id="login-error-message" style="color: red;"><?php echo htmlspecialchars($error_message); ?></div>
            </form>
        </div>

        <div class="toggle-container">
            <div class="toggle">
                <div class="toggle-panel toggle-left">
                    <h1>Hello, Club Members!</h1>
                    <p>Register with your club details</p>
                   <br>
                       <p>Already have an account?</p> 
                    <button class="hidden" id="login-toggle">Sign In</button>
                </div>
                <div class="toggle-panel toggle-right">
                    <h1>Welcome Back!</h1>
                    <p>Enter your Club details</p>
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
    <script>
       document.getElementById('register-toggle').addEventListener('click', function() {
    document.getElementById('container').classList.add('active');
});

document.getElementById('login-toggle').addEventListener('click', function() {
    document.getElementById('container').classList.remove('active');
});

    </script>
</body>
</html>
