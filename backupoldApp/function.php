<?php
session_start();


if(isset($_SESSION['userdata'])){
  $userdata = json_decode($_SESSION['userdata'],true);
  extract($userdata);
  $user_per_id = $user_id;
 }

include "config.php";


$salon_id = get_session_data('salon_id');
$gst_enable = get_session_data('gst_enable');
$role_id = get_session_data('role_id');
$whatsapp_api = get_session_data('whatsapp_api');


/******** SQL Data Start ****************/

//

function getMonthsInRange($startDate, $endDate)
{
    $months = array();
    while (strtotime($startDate) <= strtotime($endDate)) {

        $last_day = date('t', strtotime($startDate));
        if(date('m', strtotime($startDate)) == date('m', strtotime($endDate))){
            $last_day = date('d', strtotime($endDate));
        }


        $months[] = array(
            'fromdate' => date('Y', strtotime($startDate))."-".date('m', strtotime($startDate))."-".date('d', strtotime($startDate)),
            'todate' => date('Y', strtotime($startDate))."-".date('m', strtotime($startDate))."-".$last_day,
        );

        // Set date to 1 so that new month is returned as the month changes.
        $startDate = date('01 M Y', strtotime($startDate . '+ 1 month'));
    }

    return $months;
}

function get_cash_discount($all_discount,$month){
    $cash_discount = 0;
    $curr_month = date('m-Y',strtotime($month));
    foreach($all_discount as $single_discount){
        $discount_month = date('m-Y',strtotime($single_discount['month_discount']));
        if($curr_month == $discount_month){
             $cash_discount = $single_discount['cash_discount'];
             break;
        }
    }
    return $cash_discount;
}
function sendapisms($number,$message,$senderid="TresPB")
{

    /* 'user' => 'tress',
      'password' => '296',*/
      $requestParams = array(
        'APIKey' => 'AF4iDB0HRUmKNXTtKBt9vA',
        'senderid' => $senderid,
        'channel' => 'Trans',
        'DCS' => '0',
        'number' => $number,
        'text' => $message,
        'flashsms' => '0',
        'peid' => '1201160267708593150',
        'route' =>  '02'  
      );


$apiUrl = "http://smslogin.pcexpert.in/api/mt/SendSMS?";
  foreach($requestParams as $key => $val){
      $apiUrl .= $key.'='.urlencode($val).'&';
  }
  $apiUrl = rtrim($apiUrl, "&");

  //API call
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $apiUrl);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

  $data = curl_exec($ch);
  curl_close($ch);
  $checkerror = json_decode($data,true);
  if($checkerror['ErrorCode'] == 006){
      echo "<br><br>".$apiUrl;
      echo "<br>".$checkerror['ErrorMessage'];
  }
  return $data;		
}


function checkphonenumber($phoneNumber) {
  // Remove any non-numeric characters from the phone number
  $phoneNumber = preg_replace("/[^0-9]/", "", $phoneNumber);

  // Check if the number starts with optional +91, followed by 10 digits
  if (preg_match("/^(?:\+?91)?([789]\d{9})$/", $phoneNumber, $matches)) {
      return '+91' . $matches[1];
  } else {
      return false;
  }
}


//echo ">>>".SendWhatsAppTress("9914500270","Test Message");
 

function SendWhatsAppTress($number,$message){

  $number = checkphonenumber($number);
  if($number == false){
    return false;
  }
  $requestParams = array(
    'appkey' => '5bd7d600-43b1-4985-82e6-856fefe6ca99',
    'authkey' => '5r7iufBRY23cyFEkAh0jncZKNhxfgGxiHvj2wBktCD0Ryheuwy',
    'to' => $number,
    'message' => $message,
    'sandbox' => 'false',
  );

  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://grinchrestobar.net/api/create-message',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => $requestParams,
  ));

  $response = curl_exec($curl);

  curl_close($curl);
  //echo $response;
}
function SendWhatsAppSms($number,$message,$whatsapp_api)
{
    if($whatsapp_api == "7cf5a69702554b9bb99c3f848cfb63b3"){
      //SendWhatsAppTress($number,$message);
      //return true;
    }

    //pdf=URL?qr=true.pdf

      $requestParams = array(
        'apikey' => $whatsapp_api,
        'mobile' => $number,
        'msg' => $message,
      );


$apiUrl = "http://148.251.129.118/wapp/api/send?";
  foreach($requestParams as $key => $val){
      $apiUrl .= $key.'='.urlencode($val).'&';
  }
   $apiUrl = rtrim($apiUrl, "&");

  //echo $apiUrl;
  //API call
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $apiUrl);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

  $data = curl_exec($ch);
  curl_close($ch);
  $checkerror = json_decode($data,true);
  //print_R($checkerror);
  if($checkerror['ErrorCode'] == 006){
      echo "<br><br>".$apiUrl;
      echo "<br>".$checkerror['ErrorMessage'];
  }
  return $data;		
}

function sendsmstoowner($message){

    global $whatsapp_api,$salon_id;
    $all_user = select_array("SELECT mobile_no FROM `hr_user_owner` where salon_id='".$salon_id."'  and is_active=1");

    foreach($all_user as $user)
    {
        $mobile_no = $user['mobile_no'];
        //$mobile_no = "9914500270";
        SendWhatsAppSms($mobile_no,$message,$whatsapp_api);
    }
}


function sendapisms_old($mobileNumber,$message)
{
  
  
    //Your authentication key
    $authKey = "264835AQzdFcZCCfu5c74e6b6";
    //Sender ID,While using route4 sender id should be 6 characters long.
    $senderId = "HRslon";

    //Your message to send, Add URL encoding here.
    //$message = urlencode("Test message");

    //Define route 
    $route = "1";
    //Prepare you post parameters
    $postData = array(
        'authkey' => $authKey,
        'mobiles' => $mobileNumber,
        'message' => $message,
        'sender' => $senderId,
        'route' => $route
    );

    //API URL
    $url="http://api.msg91.com/api/sendhttp.php";

    // init the resource
    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postData
        //,CURLOPT_FOLLOWLOCATION => true
    ));


    //Ignore SSL certificate verification
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);


  //get response
  $output = curl_exec($ch);

  //Print error if any
  if(curl_errno($ch))
  {
      echo 'error:' . curl_error($ch);
  }

  curl_close($ch);

  return $output;		
}

function insert_query($query){
	
	global $conn;
	if (mysqli_query($conn, $query)) {
		$last_id = mysqli_insert_id($conn);
		return $last_id;
	} else {
		return false;//"Error: " . $sql . "<br>" . mysqli_error($conn);
		return "Error: " . $sql . "<br>" . mysqli_error($conn);
	}
	
}

function update_query($query){
	global $conn;
	if (mysqli_query($conn, $query)) {
		return true;
	} else {
		return false;// "Error updating record: " . mysqli_error($conn);
	}
	
}

function delete_query($query){
	global $conn;
	if (mysqli_query($conn, $query)) {
		return true;
	} else {
		return false;// "Error updating record: " . mysqli_error($conn);
	}
	
} 


function select_array($query){
	global $conn;
	$sql = mysqli_query($conn, $query) or die(mysqli_error($conn));
	if (mysqli_num_rows($sql) > 0) {
		$data = array();
		while($row = mysqli_fetch_assoc($sql)) {
			$data[] = $row;
		}
		return $data;
	} else {
		return false;
	}
}

function select_row($query){
	global $conn;
	$sql = mysqli_query($conn, $query);
	if (mysqli_num_rows($sql) > 0) {
		$row = mysqli_fetch_assoc($sql);
		return $row;
	}else{
		return false;
	}
}

function num_rows($query){
	global $conn;
	$sql = mysqli_query($conn, $query);
	return mysqli_num_rows($sql);
}


/******** SQL Data EnD ****************/

function get_session_data($type){
  $userdata = json_decode($_SESSION['userdata'],true);
  //extract($userdata);
  return $userdata[$type];
}

if(!function_exists("check_user_permission")){
  function check_user_permission($type,$permission,$user_id)
  {


      $sql = select_row("SELECT role_id FROM `hr_user` WHERE `user_id`='".$user_id."'");
      extract($sql);
      $sql = "SELECT role_permission FROM `hr_user_role` WHERE role_id='".$role_id."'";
      $site_user_role = select_row($sql);
      extract($site_user_role);
      $role_permission = json_decode($role_permission,true);
      if($role_permission[$type][$permission] == 1){
        return true;
      }else{

          if($user_id == 1 || $user_id == 8){
              return true;
          }else{
              return false;
          }
      }
  }
}

function clean_string($string) {
  $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
  $string = preg_replace('/[^A-Za-z0-9.\-]/', '', $string); // Removes special chars.

   $string = preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
   return str_replace('-', ' ', $string);
}



function minutes_formater($minutes){

  return floor($minutes / 60).' hrs '.($minutes -   floor($minutes / 60) * 60)." mins";
  

}

if(!function_exists("format_date_sort")){
  function format_date_sort($movie_rel_date){
    
    $movie_rel_date_space = preg_replace("/[^a-zA-Z0-9]+/", "", $movie_rel_date);
      
      
      /** Year Start ***/
      
        $year = substr($movie_rel_date_space, -4);
        if($year == ''){
          $year = "2015";
        }
      
      /** Year End ***/
      
      
      /** Day Start **/
        $movie_rel_date_day = preg_replace("/[^0-9]+/", "", $movie_rel_date_space);
        $day = str_replace($year,"",$movie_rel_date_day);
        if($day == ''){
          $day = "01";
        }
      /** Day End **/
      
      /** Month **/
      $movie_rel_date_month = preg_replace("/[^a-zA-Z]+/", "", $movie_rel_date_space);
      if($movie_rel_date_month == ''){
        $movie_rel_date_month = "Jan";
      }
      $month = date("m", strtotime($movie_rel_date_month));
      
      /** Month End **/
      
      $final_date = $year.$month.$day;//preg_replace("/[^a-zA-Z0-9]+/", "", $date);
    
    return $final_date;
  }
}


function create_slug($name){
  $slug = clean_string(strtolower($name));
  return str_replace(' ','-', $slug);
}



function create_actor_slug($actor_name,$actor_id){

  if($actor_id > 0){
      $where = " and actor_id !=".$actor_id." ";
  }
  $actor_slug = trim(parse_for_url(replace_all($actor_name)));
  $actor_slug = str_ireplace(array("--","---","----"),"-",$actor_slug);

  $data = num_rows("SELECT * FROM `go_actor` WHERE actor_slug='".$actor_slug."' ".$where." ");
  if($data > 0){
      $actor_slug = $actor_slug."-".$data;
  }
  $update_sql = "UPDATE `go_actor` SET `actor_slug` = '".$actor_slug."' WHERE `actor_id` = '".$actor_id."'";
  update($update_sql);
  return  $actor_slug;

}


function get_actor_id($actor_name){

  $query = "SELECT * FROM `artist`  where artist_name='".$actor_name."'";
  $artist_data = select_row($query);
  if ($artist_data != "error") {
    $artist_id = $artist_data['artist_id'];
  } else {
    $query = "INSERT INTO `artist`(`artist_name`) VALUES ('".$actor_name.")";
    $artist_id = insert($query);
  }
  return  $artist_id;

}

function get_genre_id($genre_name){

  $query = "SELECT * FROM `genre`  where genres_name='".$genre_name."'";
  $genre_data = select_row($query);
  if ($genre_data != "error") {
    $genre_id = $genre_data['genres_id'];
  } else {
    $slug = create_slug($genre_name." movies");
    $query = "INSERT INTO `genre`(`genres_name`,`genres_slug`) VALUES ('".($genre_name)."','".$slug."')";
    $genre_id = insert($query);
  }
  return  $genre_id;

}



function replace_all($string)
{
    $unwanted_array = array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
        'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
        'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
        'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
        'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
    $string = strtr( $string, $unwanted_array );
    return trim(str_replace(array("-", "&", "!", "@", "#", ",", ":", "'", '"', ']','[','(',')','+','.','*','%','^','?','/'), "", $string));
}


function movie_link_hdfriday($movie,$domain){

  if($movie['movie_name_prefix'] != ''){
      $prefix = str_ireplace(" ","-",$movie['movie_name_prefix']);
  }else{
      $prefix = '';
  }
  
  if($movie['movie_lang'] == 'Dubbed In Hindi'){
      $dubbed = '-dubbed-in-hindi';
  }else{
      $dubbed = '';
  }
  $print ='';
  if($movie['movie_print'] !=''){
      $print = strtolower(str_ireplace(" ","-",$movie['movie_print']));
      $print ='-'.$print;
  }
  
  if($movie['movie_type'] =='ep'){
      $text ='episode';
      $episode =$movie['episode_no'].'-';
  }else{
      $text ='movie';
      $episode ='';
  }
  
  $link =  $domain.'movie/'.$movie['movie_id'].'/download-full-'.$text.'-'.$episode.parse_for_url_friday(replace_all_friday(unserialize($movie['movie_name']))).'-'.$prefix.'-'.movie_year($movie).$print.$dubbed.'.html'; 
  
  return str_ireplace("--","-",$link);
}

function movie_link_ipagal($movie,$domain){

$movie_name = unserialize($movie['movie_name']);
$movie_id = $movie['movie_id'];


 if($movie['movie_name_prefix'] != ''){
  $prefix = "-".str_ireplace(" ","-",$movie['movie_name_prefix']);
}else{
  $prefix = '';
} 

if($movie['movie_print'] !=''){
  $print = strtolower(str_ireplace(" ","-",$movie['movie_print']));
  $print ='-'.$print;
}

  $movie_name = str_replace("/"," ",$movie_name);
  
$movie_url = $movie_id."v/download-".replace_all($movie_name)."-full-movie-in-".$movie['movie_lang'].$print.".html";
if($movie['cat_id'] =='7'){
  $movie_url = $movie_id."v/download-".replace_all($movie_name)."-full-movie-in-dubbed-in-hindi".$print.".html";
}

return strtolower($domain.str_replace(" ","-",$movie_url));

}

function movie_link_south($movie,$domain){

$movie_name = unserialize($movie['movie_name']);
$movie_id = $movie['movie_id'];


if($movie['movie_name_prefix'] != ''){
  $prefix = "-".str_ireplace(" ","-",$movie['movie_name_prefix']);
}else{
  $prefix = '';
}

if($movie['movie_print'] !=''){
  $print = strtolower(str_ireplace(" ","-",$movie['movie_print']));
  $print ='-'.$print;
}

$movie_name = str_replace("/"," ",$movie_name);
$movie_url = $movie_id."v/".replace_all($movie_name).$prefix.$print."-full-movie-download-filmywapsouth.html";
return strtolower($domain.str_replace(" ","-",($movie_url)));
}



?>