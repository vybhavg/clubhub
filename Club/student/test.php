<?php
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
