<?php
// Let's modify ajax/report_ajax.php to output split modes effectively
$data = file_get_contents('ajax/report_ajax.php'); 

$search = "        \$mode = strtolower(\$sale['payment_mode']);
        \$mode = isset(\$payment_method) && isset(\$payment_method[\$mode]) ? \$payment_method[\$mode] : ucfirst(\$mode);
";

$replace = "        \$mode = strtolower(\$sale['payment_mode']);
        if(\$mode == 'split'){
            // get splits
            \$split_qry = \"SELECT payment_mode FROM `hr_invoice_payment` WHERE invoice_id='\".\$sale['invoice_id'].\"'\";
            \$split_res = select_array(\$split_qry);
            \$split_modes = [];
            if(\$split_res){
                foreach(\$split_res as \$s){
                    \$m = strtolower(\$s['payment_mode']);
                    \$split_modes[] = isset(\$payment_method) && isset(\$payment_method[\$m]) ? \$payment_method[\$m] : ucfirst(\$m);
                }
            }
            \$mode = 'Split<br><small class=\"text-muted\">('.implode(' + ', \$split_modes).')</small>';
        }else{
            \$mode = isset(\$payment_method) && isset(\$payment_method[\$mode]) ? \$payment_method[\$mode] : ucfirst(\$mode);
        }
";
if (strpos($data, $search) !== false) {
    $data = str_replace($search, $replace, $data);
    file_put_contents('ajax/report_ajax.php', $data);
    echo "Splits payment details patched!\n";
} else {
    echo "Could not find sequence\n";
}
?>
