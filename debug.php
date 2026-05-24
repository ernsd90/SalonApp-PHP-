<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// setup session
$_SERVER['DOCUMENT_ROOT'] = '/Applications/XAMPP/xamppfiles/htdocs';
include "config.php";
include "function.php";

echo "DB connection done\n";
?>
