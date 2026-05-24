
<?php


include "../function.php";

$invoice_id = $_REQUEST['inventorybill_id'];

$salon_id = get_session_data('salon_id');

$salon = select_row("SELECT salon_name,salon_address,salon_contact,salon_gst FROM `hr_salon`  WHERE `salon_id` = $salon_id");
extract($salon);



$invoice = select_row("SELECT b.*,c.vendor_name FROM `hr_bill` as b join hr_vendor as c on c.id=b.vendor  WHERE b.`bill_id` = $invoice_id");
extract($invoice);
$invoice_service = select_array("SELECT * FROM `hr_bill_product` WHERE `bill_id` = '".$invoice_id."'");


?>
<div class="row">
    <div class="col-lg-12">
        <div class="card px-2">
            <div class="card-body">
                <div class="container-fluid">
                    <h3 class="text-right my-5">Invoice&nbsp;&nbsp;#<?php echo $invoice_id; ?></h3>
                    <hr>
                </div>
                <div class="container-fluid d-flex justify-content-between">
                    <div class="col-lg-3 pl-0">
                        <p class="mt-5 mb-2"><b><?php echo $salon_name; ?></b></p>
                        <p><?php echo $salon_address; ?></p>
                        <p><?php echo $salon_contact; ?></p>
                    </div>
                    <div class="col-lg-3 pr-0">
                        <p class="mt-5 mb-2 text-right"><b>Invoice From</b></p>
                        <p class="text-right"><?php echo $vendor_name; ?><br> <?php echo $cust_mob; ?></p>
                    </div>
                </div>
                <div class="container-fluid d-flex justify-content-between">
                    <div class="col-lg-3 pl-0">
                        <p class="mb-0 mt-5">Invoice Date : <?php echo date('d-m-y h:i A',strtotime($invoice_date)); ?></p>
                    </div>
                </div>
                <div class="container-fluid mt-5 d-flex justify-content-center w-100">
                    <div class="table-responsive w-100">
                        <table class="table">
                            <thead>
                            <tr class="bg-dark text-white">
                                <th>#</th>
                                <th class="text-right">Type</th>
                                <th>Product Name</th>
                                <th class="text-right">Quantity</th>
                                <th class="text-right">MRP/Pc</th>
                                <th class="text-right">Total</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $i=1; foreach($invoice_service as $services){ extract($services); ?>
                                <tr class="text-right">
                                    <td class="text-left"><?php echo $i++; ?></td>
                                    <td><?php echo $product_type ?></td>
                                    <td class="text-left"><?php echo $product_name ?></td>
                                    <td><?php echo $qty; ?></td>
                                    <td><?php echo number_format($mrp); ?></td>
                                    <td><?php echo $grand_total; ?></td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="container-fluid mt-5 w-100">
                    <?php if($discount > 0){ ?>
                    <p class="text-right">Tax  : <?php echo $discount; ?></p>
                    <?php } ?>
                    <?php if($gst > 0){ ?>
                        <p class="text-right">Discount  : <?php echo $gst; ?></p>
                    <?php } ?>
                    <h4 class="text-right mb-5">Total : <?php echo $total; ?></h4>
                    <hr>
                </div>

                <!--
                <div class="container-fluid w-100 print_button_hide">
                    <a href="/print_invoice.php?invoice_id=<?php echo $invoice_id; ?>&type=close" target="_blank" class="btn btn-primary float-right mt-4 ml-2"><i class="mdi mdi-printer mr-1"></i>Print</a>
                    <a href="#" class="btn btn-success float-right mt-4" ><i class="mdi mdi-telegram mr-1"></i>Send Invoice</a>
                </div>
                -->
            </div>
        </div>
    </div>
</div>