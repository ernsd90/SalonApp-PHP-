<?php 

include "../function.php";

$invoice_id = base64_decode($_GET['invoice_id']);
extract(select_row("SELECT cust_name,cust_mob,salon_id FROM `hr_invoice` WHERE `invoice_id` = '".$invoice_id."'"));


extract(select_row("SELECT google_review_link FROM `hr_salon` WHERE `salon_id` = '".$salon_id."'"));


$feedbak_chk = num_rows("SELECT * FROM `hr_feedback` WHERE `invoice_id` = '".$invoice_id."'");


if(isset($_POST['feedback_submit'])){
extract($_POST);

    $sql = "Insert Into `hr_feedback` SET `salon_id`='".$salon_id."',`invoice_id`='".$invoice_id."',`cust_name`='".$cust_name."',`cust_mob`='".$cust_mob."',`experience`='".$experience."',`message`='".$message."'";
    $insert_id = insert_query($sql);

}



?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Hair Raizers</title>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
        <link href="https://fonts.googleapis.com/css?family=Montserrat" rel="stylesheet">
        <link rel="stylesheet" href="form.css" >

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" >
        <style>
            .row,#success_message,#error_message{
                color:#fff;
            }
            .review-btn {
                background:#ea4335;
                color:#fff;
                font-size:18px;
                padding:8px 20px;
                text-decoration:none;
                border-radius:4px;
                border:none;
                }
        </style>
    </head>

    <body >
        <div class="container">
            <div id="form-main">
                <div id="form-div">
                    <form class="montform" id="reused_form" action="" method="post">

                    <input type="hidden" name="cust_name" value="<?php echo $cust_name; ?>" />
                    <input type="hidden" name="cust_mob" value="<?php echo $cust_mob; ?>" />
                    <input type="hidden" name="invoice_id" value="<?php echo $invoice_id; ?>" />
                    <input type="hidden" name="salon_id" value="<?php echo $salon_id; ?>" />
                    
                    <?php if($insert_id > 0 || $feedbak_chk > 0){ ?>
                        <div id="success_message" style="width:100%; height:100%; "> <h2>Success! Your Feedback was Sent Successfully.</h2> </div>
                    <?php exit; } ?>
                        
                    <?php if(!is_numeric($invoice_id) || $cust_name == ''){ ?>
                    <div id="error_message" style="width:100%; height:100%; ">
                        <h4>
                            Error
                        </h4>
                        Sorry there was an error Contact at solon. 
                    </div>
                    <?php exit; } ?>
                        
                    <div class="row">
                        <div class="col-sm-12 form-group">
                        <label>How satisfied were you with our Staff/Service?</label>
                        <p>

                            <label class="radio-inline">
                                <input type="radio" name="experience" id="radio_experience" value="excelent">
                                <i class="fa fa-smile-o fa-2x" aria-hidden="true"></i> Excelent
                            </label>
                            <br><br>
                            

                            <label class="radio-inline">
                                <input type="radio" name="experience" id="radio_experience" value="good">
                                <i class="fa fa-smile-o fa-2x" aria-hidden="true"></i> Good
                            </label>
                            <br><br>

                            <label class="radio-inline">
                                <input type="radio" name="experience" id="radio_experience" value="average">
                                <i class="fa fa-meh-o fa-2x" aria-hidden="true"></i> Just OK
                            </label>
                            <br><br>

                            <label class="radio-inline">
                                <input type="radio" name="experience" id="radio_experience" value="bad">
                                <i class="fa fa-frown-o fa-2x" aria-hidden="true"></i> Bad
                            </label>
                        </p>
                        </div>
                        <br><BR>
                    </div>

                
                    <div class="row">
                        <div class="col-sm-12 form-group">
                           
                            <label class="radio-inline">
                            If you have specific feedback, please write to us...
                            </label>
                            <p>
                            <textarea name="message" class="feedback-input" id="comment" placeholder="Message"></textarea>
                            </p>
                        </div>
                    </div>
                        <div class="submit">
                            <button type="submit" name="feedback_submit" class="button-blue">SUBMIT</button>
                            <div class="ease"></div>
                        </div>
                    </form><br>
                    <a class="review-btn" href="<?php echo $google_review_link; ?>">Leave us a Review on Google</a>
                </div>
            </div>
        </div>
    </body>
</html>

