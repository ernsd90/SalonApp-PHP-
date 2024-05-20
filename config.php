<?php

ini_set('display_errors', 'off');

date_default_timezone_set('Asia/Kolkata');


$conn = mysqli_connect("localhost","root","","salonapp");

// Check connection
if (mysqli_connect_errno())
{
	echo "Failed to connect to MySQL: " . mysqli_connect_error();
}


define("DOMAIN_SOFTWARE","http://localhost/salonapp/");


$payment_method =  array("cash" => "Cash",
						"cc" => "Card/UPI",
						"pkg" => "Package",
						"split" => "Part Payment");
?>