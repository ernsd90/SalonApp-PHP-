<?php include 'header.php';?>
<?php



if(isset($_POST['create_bill'])){
    foreach($_POST as $var => $val){
        $$var = $val;
    }

    $invoice_date = date("Y-m-d",strtotime($invoice_date));
    $sql = "INSERT INTO `hr_bill` SET `salon_id`='".$salon_id."',`user_id`='".$user_id."',`invoice_no`='".$invoice_no."',`vendor`='".$vendor."',`invoice_date`='".$invoice_date."',`discount`='".$discount."',`gst`='".$gst."',`total`='".$total."',`created_date`=NOW()";
    $bill_id = insert_query($sql);

    if($bill_id > 0){
        foreach($product as $key => $myproduct){

            $product_type = $protype[$key];
            $myquantity = $quantity[$key];
            $mymrp = $mrp[$key];
            $mytotalprice = $totalprice[$key];
            $myproduct = trim(ucwords(strtolower($myproduct)));
            $sql = "INSERT INTO `hr_bill_product` SET `salon_id`='".$salon_id."',`bill_id`='".$bill_id."',`product_type`='".$product_type."',`product_name`='".$myproduct."',`qty`='".$myquantity."',`mrp`='".$mymrp."',`grand_total`='".$mytotalprice."'";
            insert_query($sql);
        }
    }

    $sql = "INSERT INTO `hr_vendor_payment` SET `bill_id`='".$bill_id."',`salon_id`='".$salon_id."',`vendor_id`='".$vendor."',`amt_in`='".$total."'";
    insert_query($sql);

    $sql = "SELECT vendor_name FROM `hr_vendor` where `id`='".$vendor."'";
    extract(select_row($sql));

    $message = "This is to inform you that we have received a bill from *".$vendor_name."* in the amount of *".$total."*.";
    sendsmstoowner($message);
}

$sql = "SELECT * FROM `hr_vendor`";
$allvendor = select_array($sql);
?>
<!-- partial -->
<div class="main-panel">
    <div class="content-wrapper">

        <div class="row">
            <form class="inventoryform" method="post" autocomplete="off">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Generate Bill</h4>

                            <p class="card-description"> Bill Info </p>
                            <div class="row">
                                <div class="col-6">

                                </div>
                                <div class="col-6">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Date</label>
                                            <input type="text" readonly id="datepicker-popup" name="invoice_date" class="form-control form-control-sm" placeholder="Date" value="<?php echo date("d-m-Y"); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Vendor</label>
                                            <select name="vendor" id="vendor" class="search_selectvendor form-control select2-selection--single">
                                                <option value="">Select Vendor</option>
                                                <?php foreach($allvendor as $vendor){   ?>
                                                    <option value="<?php echo $vendor['id']; ?>">
                                                        <?php echo $vendor['vendor_name']; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="row">

                                <div class="col-md-12 column">
                                    <table class="table table-bordered table-hover" id="tab_logic">
                                        <thead>
                                        <tr >
                                            <th class="text-center">
                                                #
                                            </th>
                                            <th class="text-center">
                                                Product Type
                                            </th>
                                            <th class="text-center" width="400px">
                                                Product
                                            </th>
                                            <th class="text-center">
                                                Quantity
                                            </th>
                                            <th class="text-center">
                                                MRP
                                            </th>
                                            <th class="text-center">
                                                Total Price
                                            </th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr id='addr0'>
                                            <td>
                                                1
                                            </td>
                                            <td>
                                                <select name="protype[]"  class="form-control">
                                                    <option value="store">Store</option>
                                                    <option value="retail">Retail</option>
                                                </select>
                                            </td>
                                            <td>
                                                <div class="ui-widget">
                                                    <input type="text" name='product[]' placeholder='Product' class="form-control myproductinventory"/>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="text" name='quantity[]' placeholder='Quantity' class="form-control" value="1"/>
                                            </td>
                                            <td>
                                                <input type="text" name='mrp[]' placeholder='MRP' class="form-control"/>
                                            </td>
                                            <td>
                                                <input type="text" name='totalprice[]' placeholder='Total Price' class="form-control totalprice"/>
                                            </td>
                                        </tr>
                                        <tr id='addr1'></tr>
                                        </tbody>
                                    </table>

                                </div>
                                <a id="add_row" class="btn btn-default pull-left">Add Row</a>
                                <a id='delete_row' class="pull-right btn btn-default">Delete Row</a>
                            </div>

                            <div class="row">
                            <div class="col-8">

                            </div>
                            <div class="col-4">

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Discount</label>
                                        <input type="number" min="0" name="discount" value="0" class="form-control form-control-sm discount" placeholder="Discount">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>GST(total amount)</label>
                                        <input type="number" min="0" name="gst" value="0"  class="form-control form-control-sm ttl_gst" placeholder="GST">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Total Amount</label>
                                        <input type="number" min="1" name="total"  class="form-control form-control-sm grand_total" placeholder="Total Amount">
                                    </div>
                                </div>
                            </div>
                        </div>

                            <div class="row">
                                <div class="col-8">

                                </div>
                                <div class="col-4">
                                    <button type="submit" name="create_bill" class="update_user btn btn-primary">Create Bill</button>
                                </div>
                            </div>
                    </div>
                </div>
            </div>
            </form>
        </div>
    </div>

    <!-- content-wrapper ends -->

    <?php include 'footer.php';?>

    <script>
        $(document).ready(function() {
            $(".search_selectvendor").select2();

            if ($("#datepicker-popup").length) {
                $('#datepicker-popup').datepicker({
                    enableOnReadonly: true,
                    todayHighlight: true,
                    autoclose: true,
                    format: 'dd-mm-yyyy'
                });
            }

        });
    </script>

    <script>


    </script>


