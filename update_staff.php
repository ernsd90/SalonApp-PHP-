<?php
$file = 'invoice.php';
$content = file_get_contents($file);

// Replace the foreach to include a check
$old = 'foreach($service_staff[$key] as $staff_id){';
$new = 'if (!isset($service_staff[$key]) || !is_array($service_staff[$key])) { $service_staff[$key] = []; }' . "\n" . '                foreach($service_staff[$key] as $staff_id){';

if (strpos($content, $old) !== false) {
    $content = str_replace($old, $new, $content);
    file_put_contents($file, $content);
    echo "Fixed line 85!\n";
} else {
    echo "Could not find sequence\n";
}
?>
