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
$active_tab = 'sign-in'; // Default active tab

// Handle registration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register_username'], $_POST['register_pass'], $_POST['club_name'], $_POST['branch_id'])) {
    $register_username = $_POST['register_username'];
    $register_password = password_hash($_POST['register_pass'], PASSWORD_DEFAULT); // Hash the password
    $club_name = $_POST['club_name'];
    $branch_id = $_POST['branch_id'];

    // Check if all required fields are filled
    if (empty($register_username) || empty($register_password) || empty($club_name) || empty($branch_id)) {
        $error_message = "All fields are required!";
        $active_tab = 'sign-up';
    } else {
        // Prepare the SQL statement to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO clubs (club_name, username, password, branch_id) VALUES (?, ?, ?, ?)");
        if ($stmt === false) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("sssi", $club_name, $register_username, $register_password, $branch_id);

        if ($stmt->execute()) {
            $success_message = "Registration successful!";
            $active_tab = 'sign-in'; // Redirect to sign-in after successful registration
        } else {
            $error_message = "Registration failed: " . $stmt->error;
            $active_tab = 'sign-up';
        }

        $stmt->close();
    }
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['username'], $_POST['pass'])) {
    $username = $_POST['username'];
    $password = $_POST['pass'];

    // Check if username and password are filled
    if (empty($username) || empty($password)) {
        $error_message = "Username and password are required!";
        $active_tab = 'sign-in';
    } else {
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
                $active_tab = 'sign-in';
            }
        } else {
            $error_message = "Invalid username or password";
            $active_tab = 'sign-in';
        }

        $stmt->close();
    }
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
    <title>Login/Register</title>
    <link rel="stylesheet" href="login.css">
    <style>
        /* Add your CSS styles */
    </style>
</head>
<body>
    <div class="container" id="container">
        <div class="form-container sign-up <?php echo $active_tab == 'sign-up' ? 'active' : ''; ?>">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <h1>Create Account</h1>
                <div class="social-icons">
                    <a href="#" class="icon"><i class="fa-brands fa-google-plus-g"></i></a>
                    <a href="#" class="icon"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" class="icon"><i class="fa-brands fa-github"></i></a>
                    <a href="#" class="icon"><i class="fa-brands fa-linkedin-in"></i></a>
                </div>
                <span>or use your email for registration</span>
                <input type="text" name="club_name" placeholder="Club Name">
                <input type="text" name="register_username" placeholder="Username">
                <input type="password" name="register_pass" placeholder="Password">
                <div class="branch">
                    <select name="branch_id" class="branch_id">
                        <option value="">Select Branch</option>
                        <?php echo $branch_options; ?>
                    </select>
                </div>
                <button type="submit">Sign Up</button>
                <div id="register-success-message" style="color: green;"><?php echo isset($success_message) ? htmlspecialchars($success_message) : ''; ?></div>
                <div id="register-error-message" style="color: red;"><?php echo $active_tab == 'sign-up' ? htmlspecialchars($error_message) : ''; ?></div>
            </form>
        </div>
        <div class="form-container sign-in <?php echo $active_tab == 'sign-in' ? 'active' : ''; ?>">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <h1>Sign in</h1>
                <div class="social-icons">
                    <a href="#" class="icon"><i class="fa-brands fa-google-plus-g"></i></a>
                    <a href="#" class="icon"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" class="icon"><i class="fa-brands fa-github"></i></a>
                    <a href="#" class="icon"><i class="fa-brands fa-linkedin-in"></i></a>
                </div>
                <span>or use your account</span>
                <input type="text" name="username" placeholder="Username">
                <input type="password" name="pass" placeholder="Password">
                <button type="submit">Sign In</button>
                <div id="login-error-message" style="color: red;"><?php echo $active_tab == 'sign-in' ? htmlspecialchars($error_message) : ''; ?></div>
            </form>
        </div>
    </div>

    <script>
        // Ensure the active tab remains active after form submission
        document.addEventListener("DOMContentLoaded", function() {
            const activeTab = "<?php echo $active_tab; ?>";
            const container = document.getElementById('container');
            if (activeTab === 'sign-up') {
                container.classList.add('right-panel-active');
            } else {
                container.classList.remove('right-panel-active');
            }
        });
    </script>
</body>
</html>
