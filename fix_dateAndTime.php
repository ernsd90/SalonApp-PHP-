<?php
$content = file_get_contents('invoice.php');

$search = "    \$date_invoice = new DateTime(\$_POST['invoice_date']);
    \$date_now = \$date_invoice->format('Y-m-d H:i:s');";

$replace = "    \$date_invoice = new DateTime(\$_POST['invoice_date']);
    \$date_now = \$date_invoice->format('Y-m-d').' '.date('H:i:s');";

$content = str_replace($search, $replace, $content);
file_put_contents('invoice.php', $content);
echo "Done!\n";
?>
