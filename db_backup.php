<?php


$filename='hrsalon_'.date('d_m_Y').'.sql';

$result=exec('mysqldump admin_hrsalon --password=navjot@123 --user=admin_hrsalon --single-transaction >/var/www/vhosts/ipagal.biz/salonapp/db/'.$filename,$output);

if(empty($output)){/* no output is good */}
else {/* we have something to log the output here*/}


?>