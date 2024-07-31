<?php
// Database connection settings
$db_host = '127.0.0.1'; // Localhost for the same EC2 instance
$db_username = 'clubhub'; // Replace with your database username
$db_password = 'vybhav'; // Replace with your database password
$db_name = 'mydatabase'; // Replace with your database name

// Create a connection to the database
$conn = new mysqli($db_host, $db_username, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = "";

// Check if username and password are entered
if (isset($_POST['username']) && isset($_POST['pass'])) {
    $username = $_POST['username'];
    $password = $_POST['pass'];

    // Prepare the SQL statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify the password (assuming hashed passwords in the database)
        if (password_verify($password, $user['password'])) {
            // Login successful, redirect to dashboard page
            header('Location: https://github.com/vybhavg/clubhub.github.io/blob/main/members.html');
            exit;
        } else {
            $error_message = "Invalid username or password";
        }
    } else {
        $error_message = "Invalid username or password";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Login V16</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/png" href="images/icons/favicon.ico" />
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
  <div class="limiter">
    <div class="container-login100" style="background-image: url('loginbck.jpg');">
      <div class="wrap-login100 p-t-30 p-b-50">
        <span class="login100-form-title p-b-41">
          Member Login
        </span>
        <form class="login100-form validate-form p-b-33 p-t-5" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
          <div class="wrap-input100 validate-input" data-validate="Enter username">
            <input class="input100" type="text" name="username" placeholder="User name">
            <span class="focus-input100" data-placeholder="&#xe82a;"></span>
          </div>
          <div class="wrap-input100 validate-input" data-validate="Enter password">
            <input class="input100" type="password" name="pass" placeholder="Password">
            <span class="focus-input100" data-placeholder="&#xe80f;"></span>
          </div>
          <div id="error-message" style="color: red;"><?php echo htmlspecialchars($error_message); ?></div>
          <div class="container-login100-form-btn m-t-32">
            <button class="login100-form-btn">
              Login
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <div id="dropDownSelect1"></div>

  <script src="vendor/jquery/jquery-3.2.1.min.js"></script>
  <script src="vendor/animsition/js/animsition.min.js"></script>
  <script src="vendor/bootstrap/js/popper.js"></script>
  <script src="vendor/bootstrap/js/bootstrap.min.js"></script>
  <script src="vendor/select2/select2.min.js"></script>
  <script src="vendor/daterangepicker/moment.min.js"></script>
  <script src="vendor/daterangepicker/daterangepicker.js"></script>
  <script src="vendor/countdowntime/countdowntime.js"></script>
  <script src="login.js"></script>
  <script async src="https://www.googletagmanager.com/gtag/js?id=UA-23581568-13"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'UA-23581568-13');
  </script>
  <script defer src="https://static.cloudflareinsights.com/beacon.min.js/vcd15cbe7772f49c399c6a5babf22c1241717689176015" integrity="sha512-ZpsOmlRQV6y907TI0dKBHq9Md29nnaEIPlkf84rnaERnq6zvWvPUqr2ft8M1aS28oN72PdrCzSjY4U6VaAw1EQ==" data-cf-beacon='{"rayId":"8aad5a555d9e3fe1","serverTiming":{"name":{"cfL4":true}},"version":"2024.7.0","token":"cd0b4b3a733644fc843ef0b185f98241"}' crossorigin="anonymous"></script>
 </body>
</html>
