<?php
include "../function.php";
$product_name = base64_decode($_GET['product_name']);


?>
<div class="row">
    <div class="col-lg-12">
        <div class="card px-2">
            <form method="post" id="salon_form">
                <input type="hidden" name= "method" value="inventory_inuse" />

                <input type="hidden" name="product_name" value="<?php echo $product_name; ?>" />

                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Update Inventory</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="content">

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Quantity</label>
                                                <input type="number" min="1" max="20" name="qty_out" class="form-control" required value="1">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>

                    <button type="submit" name="update_mov" class="btn btn-primary">Save Changes</button>
                </div>
            </form>

        </div>
    </div>

</div>