<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['file'])) {
        $file = $_FILES['file'];
        if ($file['error'] == UPLOAD_ERR_OK) {
            $target_file = '/var/www/html/Club/student/uploads/' . basename($file['name']);
            if (move_uploaded_file($file['tmp_name'], $target_file)) {
                echo "File uploaded successfully.";
            } else {
                echo "Error moving file.";
            }
        } else {
            echo "Error uploading file: " . $file['error'];
        }
    } else {
        echo "No file uploaded.";
    }
}
?>

<form method="post" enctype="multipart/form-data">
    <input type="file" name="file">
    <button type="submit">Upload</button>
</form>
