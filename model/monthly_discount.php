<?php
include "../function.php";

$user_action = "create";
if(isset($_REQUEST['cash_discount_id']) && is_numeric($_REQUEST['cash_discount_id'])){
    $user_action = "edit";

    $sql = "SELECT * FROM `hr_salon_cashdiscount` WHERE `id`='".$_REQUEST['cash_discount_id']."'";
    $user = select_row($sql);
    extract($user);

}

?>

<form class="form-horizontal" id="salon_form" method="post">
    <?php
    if($user_action == "create"){
        echo '<input name="method" type="hidden" value="create_cash_discount">';
        $required = "required";
    }else{
        echo '<input name="method" type="hidden" value="update_cash_discount">';
        echo '<input name="cash_discount_id" type="hidden" value="'.$id.'">';
        $required = "";
    }
    echo '<input name="salon_id" type="hidden" value="'.$salon_id.'">';



    ?>
    <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Cash Monthly Discount </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                <div class="card ">
                    <div class="card-body">
                        <div class=" row">

                            <div class="col-md-12">
                                <div class="form-group ">
                                    <label for="month_discount" class=" text-right control-label col-form-label">Month</label>
                                    <div class="">
                                        <input required name="month_discount" type="date" class="form-control" id="month_discount" placeholder="Month" value="<?php echo $month_discount; ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group ">
                                    <label for="cash_discount" class=" text-right control-label col-form-label">Discount</label>
                                    <div class="">
                                        <input required name="cash_discount" type="number" min="0" mix="99" class="form-control" id="cash_discount" placeholder="Cash Discount" value="<?php echo $cash_discount; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- row -->
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" class="update_user btn btn-primary">Save changes</button>
    </div>
</form>
