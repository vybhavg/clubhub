<?php
// deploy.php

// Define the path to your repository
$repoPath = '/var/www/html'; // Repository is in /var/www/html

// Define the log file path
$logFile = '/var/www/html/deploy_log.txt';

// Ensure the log file is writable
if (!is_writable(dirname($logFile))) {
    echo "Log directory is not writable.";
    exit;
}

// Navigate to the repository directory and execute git pull
$cmd = "cd $repoPath && sudo -u ec2-user git pull origin main 2>&1"; // Replace 'main' with your default branch if different

// Execute the command
$output = shell_exec($cmd);

// Log the output for debugging
file_put_contents($logFile, date('Y-m-d H:i:s') . "\n" . $output . "\n", FILE_APPEND);

// Optional: Output for web response
echo "<pre>";
echo "GitHub Webhook Response:\n";
echo htmlspecialchars($output);
echo "</pre>";
?>
