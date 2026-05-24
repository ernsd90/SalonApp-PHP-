<?php 
session_start();
session_destroy();
setcookie('userdata', '', time() - (86400 * 90), "/");
header("location:/");
exit;

?>