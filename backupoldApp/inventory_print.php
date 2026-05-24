<?php include "function.php"; 

$salon_id = get_session_data('salon_id');

if(is_numeric($salon_id)){

    $data = select_array("SELECT product_name,(sum(qty)-sum(qty_out)) as ttl_qty,sum(qty_out) as qty_out,product_type FROM `hr_bill_product` where salon_id='".$salon_id."'  GROUP by product_name order by product_type,product_name asc");
    
}else{
    die("Invalid Invoice!!!");
}
?>

<!doctype html>
<html>
<head>

<meta charset="utf-8">
<title>Hair Raiserz</title>
    
    <style>
    .invoice-box{
    max-width: 85%;
    margin: auto;
    padding: 3px;
    font-size: 12px;
    font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
    color: #010101;
    font-weight: normal;
    }
    
    .invoice-box table{
        width:100%;
        line-height:inherit;
        text-align:left;
    }
    
    .invoice-box table td{
        padding:3px;
        vertical-align:top;
    }
    
    .invoice-box table tr td:nth-child(2){
        text-align:right;
    }
    
    .invoice-box table tr.top table td{
        padding-bottom:20px;
    }
    
   
    
    .invoice-box table tr.information table td{
        padding-bottom:40px;
    }
    
    .invoice-box table tr.heading td{
        font-weight:bold;
    }
    
    .invoice-box table tr.details td{
        padding-bottom:20px;
    }
    
    .invoice-box table tr.item td{
        border-bottom:1px solid #eee;
    }
    
    .invoice-box table tr.item.last td{
        border-bottom:none;
    }
    
    .invoice-box table tr.total td:nth-child(2){
        border-top:2px solid #eee;
        font-weight:bold;
    }
    
    @media only screen and (max-width: 600px) {
        .invoice-box table tr.top table td{
            width:100%;
            display:block;
            text-align:center;
        }
        
        .invoice-box table tr.information table td{
            width:100%;
            display:block;
            text-align:center;
        }
    }
    </style>
</head>
<body onload="window.print()">

<div class="invoice-box">
      <center>
          <img src="https://www.tresslounge.com/hairraiserz/wp-content/uploads/2015/11/HR-LOGO.png" style="width:50%; max-width:200px;">
      </center>

        <p> <right>
            <strong>
            Created: <?php print date("j F Y");?></strong>
            </right>
        </p>

        <table width="100%" border="1">
            <tr class="heading">
                <td colspan="3">
                    Product Name
                </td>
                <td>
                    In Store
                </td>
                <td>
                    In Use/sold
                </td>
                <td width="24%" style="text-align:right">
                    Product Type
                </td>
                <td width="24%" style="text-align:right">
                    Remark
                </td>
            </tr>

            <?php 
            foreach($data as $alldata){
                foreach($alldata as $var => $value){
                    $$var = $value;
                }    
            
            ?>
            <tr class="item">
                <td colspan="3"  style="font-size: 15px"><?php echo ucwords(strtolower($product_name)); ?></td>
                <td>
                    <?php echo $ttl_qty; ?>
                </td>
                <td>
                    <?php echo $qty_out; ?>
                </td>
                <td style="text-align:right">
                    <?php echo ucwords(strtolower($product_type)); ?>
                </td>
                <td>

                </td>
            </tr>
            <?php } ?>

        </table>

            <?php if($_GET['type'] == 'close') { ?>
            <script type="text/javascript">
                window.onfocus=function(){ window.close();}
            </script>
            <?php } else { ?>
                 <meta http-equiv="refresh" content="0; url=/inventory.php" >
            <?php } ?>
        

</body>
</html>