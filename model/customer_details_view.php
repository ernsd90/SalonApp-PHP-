<?php
include "../config.php";
include "../function.php";

extract($_REQUEST);

$sql = "SELECT * FROM `hr_customer` where  `cust_id`='".$cust_id."'";
$user = select_row($sql);
$query = "SELECT cust_id,credit,debit,balance,created_date FROM `hr_customer_wallet` where  `cust_id`='".$cust_id."' order by created_date DESC";
$result = mysqli_query($conn, $query);

foreach($user as $var => $value){
    $$var = $value;
}
?>


    <?php
    echo '<input name="cust_id" type="hidden" value="'.$cust_id.'">';
    $required = "";
    echo '<input name="cust_id" type="hidden" value="'.$cust_id.'">';
    ?>
    <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; border-bottom: 1px solid var(--border-color);">
        <h3 style="font-size: 18px; font-weight: 600; margin: 0;"><?php echo $cust_name; ?> Details</h3>
        <button type="button" class="close-modal" style="background: none; border: none; font-size: 20px; color: var(--text-muted); cursor: pointer;"><i class="ph ph-x"></i></button>
    </div>
    <div class="modal-body" style="padding: 24px;">
        <div class="row">
            <div class="col-sm-12">
                <div class="card ">
                    <div class="card-body">
                        <div class="row">
                            <input name="cust_id" type="hidden" value="<?php echo $cust_id; ?>">

                            <table id="get_customer_details" class="table table-striped table-bordered" style="width:100%;">   
                            </table>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div style="display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid var(--border-color); padding: 20px 24px;">
        <button type="button" class="close-modal form-control" style="width: auto; background: white;">Close</button>
    </div>
    <script>
       $('#get_customer_details').DataTable({
        "processing": true,
        "serverSide": true,
        scrollX: true,
        responsive: true,
        iDisplayLength:100,
        "ajax": {
            "url": "ajax/customer_ajax.php",
            "type": "POST",
            "data":  function(data) {
                data.method = "get_customer_details"
                data.cust_id = '<?php echo $cust_id; ?>';
            }
        },
        "order": [
            [0, "desc"]
        ],
        "columns": [
            { "title": "Date","data": "created_date" },
            { "title": "Credit","data": "credit" },
            { "title": "Debit","data": "debit" },
            { "title": "Balance","data": "balance" },
            { "title": "Action","data": "action" }
        ]
    });
    </script>
