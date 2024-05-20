    <?php 
    include "../function.php";


    $salon_id = get_session_data('salon_id');

    $sql = "SELECT * FROM `hr_vendor`";
    $allvendor = select_array($sql);
    ?>

        <form class="form-horizontal" id="salon_form" method="post">
            <?php
                echo '<input name="method" type="hidden" value="vendor_payment">';
                $required = "required";
                echo '<input name="salon_id" type="hidden" value="'.$salon_id.'">';
            ?>
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Vendor Payment</h5>
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
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-sm-12 col-form-label">Vendor </label>
                                        <div class="col-sm-12">

                                            <select name="vendor_id" class="form-control vendor_id">
                                                <option>Select Vendor</option>
                                                <?php foreach($allvendor as $name){ ?>
                                                    <option value="<?php echo $name['id']; ?>"><?php echo $name['vendor_name'] ;?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-sm-12 col-form-label">Payment Mode </label>
                                        <div class="col-sm-12">
                                            <select name="payment_mode" class="form-control">
                                                <option value="bank_transfer">Bank Transfer</option>
                                                <option value="cash_salon">Cash By Salon</option>
                                                <option value="cash_owner">Cash By Owner</option>
                                                <option value="credit_note">Credit Note</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group ">
                                        <label for="amt_out" class=" text-right control-label col-form-label">Paid Amount</label>
                                        <div class="">
                                            <input required name="amt_out" type="text" class="form-control amt_out" id="amt_out" placeholder="Paid Amount">
                                            Pending Amount:<span class="pending_payment"></span>
                                        </div>
                                    </div>
                                </div>


                                <div class="col-md-6">
                                    <div class="form-group ">
                                        <label for="vendor_remark" class=" text-right control-label col-form-label">Remark</label>
                                        <div class="">
                                            <input required name="vendor_remark" type="text" class="form-control vendor_remark" id="vendor_remark" placeholder="Remark">

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

    <script>

        $(document).ready(function () {
            $('.vendor_id').change(function() {
                var vendor_id = $(this).val();
                $.ajax({
                    type: "POST",
                    url: "ajax/salon_ajax.php",
                    data: "method=get_vendor_payment&vendor_id=" + vendor_id + "&salon_id=" + salon_id,
                    success: function(res) {
                        var obj = jQuery.parseJSON(res);
                        if (obj.error == 1) {

                        } else {
                            $(".pending_payment").text(obj.pending_payment);
                            $(".amt_out").val(obj.pending_payment);
                        }
                    },
                    error: function() {
                        alert("Error");
                    }
                });
            });
        })

    </script>