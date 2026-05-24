<?php

ini_set('display_errors', 'off');

date_default_timezone_set('Asia/Kolkata');


$conn = mysqli_connect("srv834.hstgr.io","u883623029_salonDev","Salon@2011","u883623029_salonDev");

// Check connection
if (mysqli_connect_errno())
{
	echo "Failed to connect to MySQL: " . mysqli_connect_error();
}


define("DOMAIN_SOFTWARE", "http://localhost/salonapp/");



$payment_method =  array("cash" => "Cash",
						"cc" => "Card/UPI",
						"pkg" => "Package",
						"split" => "Part Payment");


$tax_value = 5;  //in percentage




/*
"upi" => "UPI",
"paytm" => "Paytm",
"google_pay" => "GPay",
"near_buy" => "Near Buy",
*/

?>
