<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$test_file = 'uploads/test.txt';
$file = fopen($test_file, 'w');
if ($file) {
    fwrite($file, 'Test content');
    fclose($file);
    echo 'File written successfully';
} else {
    echo 'Unable to write file';
}
?>
