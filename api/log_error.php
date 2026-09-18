<?php
$data = file_get_contents("php://input");
file_put_contents("error_log.txt", $data . "\n", FILE_APPEND);
?>
