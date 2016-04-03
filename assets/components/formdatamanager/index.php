<?php
$host  = $_SERVER['HTTP_HOST'];
header("Location: http://$host");
exit;
?>