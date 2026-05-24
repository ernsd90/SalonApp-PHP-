<?php
session_start();
session_destroy();
setcookie('userdata', '', time() - 3600, "/");
header("Location: login.php");
exit;
?>
