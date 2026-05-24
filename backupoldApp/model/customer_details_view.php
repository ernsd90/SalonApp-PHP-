<?php
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
    <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"><b><?php echo $cust_name; ?> Details</b></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-sm-12">
                <div class="card ">
                    <div class="card-body">
                        <div class="row">
                            <input name="cust_id" type="hidden" value="<?php echo $cust_id; ?>">

                            <table id="get_customer_details" class="table table-striped table-bordered">   
                            </table>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
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
