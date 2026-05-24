<?php
$content = file_get_contents('invoice.php');

$searchDateObj = "\$date_now = \$date_invoice->format('Y-m-d H:i:s');";
$replaceDateObj = "\$date_now = \$date_invoice->format('Y-m-d').' '.date('H:i:s');";
$content = str_replace($searchDateObj, $replaceDateObj, $content);

file_put_contents('invoice.php', $content);
echo "Time successfully added to invoice\_date.\n";
?>
