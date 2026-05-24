<?php
$f = 'ajax/report_ajax.php';
$d = file_get_contents($f);

// Target 1: Add a Tip Row/Column (Wait, "add Tip row in datatable") 
// Let's add extra_fee to the result array if it exists.
$s1 = "\$data_record['grand_total'] = \$all_total_cost;";
$r1 = "\$data_record['grand_total'] = \$all_total_cost;\n        \$data_record['tips'] = \$sale['extra_fee'];";
if(strpos($d, $s1) !== false) $d = str_replace($s1, $r1, $d);

file_put_contents($f, $d);
echo "Tip added properly\n";
?>
