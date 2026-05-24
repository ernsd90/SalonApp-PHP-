<?php 
include "function.php";

$salon_action = "create";
if(isset($_REQUEST['salon_id']) && is_numeric($_REQUEST['salon_id'])){
    $salon_action = "edit";
    $sql = "SELECT * FROM `hr_salon` WHERE `salon_id`='".$_REQUEST['salon_id']."'";
    $salon = select_row($sql);
    extract($salon);
} else {
    // Defaults
    $salon_name = $salon_address = $salon_contact = $gst_enable = $salon_gst = $firm_name = $whatsapp_enable = $whatsapp_api = "";
    $include_gst = 0;
}
?>
<form class="form-horizontal" id="salon_form" method="post">
    <?php 
        if($salon_action == "create"){
            echo '<input name="method" type="hidden" value="create_salon">';
        }else{
            echo '<input name="method" type="hidden" value="update_salon">';
            echo '<input name="salon_id" type="hidden" value="'.$salon_id.'">';
        }
    ?>
    <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Salon Form</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                <div class=" card">
                    <div class=" card-body">
                        <div class=" row">
                            <div class="col-md-6">
                                <div class="form-group ">
                                    <label class="control-label col-form-label">Salon Name</label>
                                    <input required name="salon_name" type="text" class="form-control" placeholder="Salon Name" value="<?php echo htmlspecialchars($salon_name); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group ">
                                    <label class="control-label col-form-label">Contact</label>
                                    <input required name="salon_contact" type="text" class="form-control" placeholder="Contact Number" value="<?php echo htmlspecialchars($salon_contact); ?>">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group ">
                                    <label class="control-label col-form-label">Address</label>
                                    <input required name="salon_address" type="text" class="form-control" placeholder="Address" value="<?php echo htmlspecialchars($salon_address); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group ">
                                    <label class="control-label col-form-label">Firm Name</label>
                                    <input name="firm_name" type="text" class="form-control" placeholder="Firm/Company Name" value="<?php echo htmlspecialchars($firm_name); ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group ">
                                    <label class="control-label col-form-label">GST Enable</label>
                                    <select name="gst_enable" class="form-control custom-select">
                                        <option value="0" <?php echo ($gst_enable == 0 ? 'selected' : ''); ?>>No</option>
                                        <option value="1" <?php echo ($gst_enable == 1 ? 'selected' : ''); ?>>Yes</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group ">
                                    <label class="control-label col-form-label">Include GST in Price</label>
                                    <select name="include_gst" class="form-control custom-select">
                                        <option value="0" <?php echo ($include_gst == 0 ? 'selected' : ''); ?>>No</option>
                                        <option value="1" <?php echo ($include_gst == 1 ? 'selected' : ''); ?>>Yes</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group ">
                                    <label class="control-label col-form-label">GST Number</label>
                                    <input name="salon_gst" type="text" class="form-control" placeholder="GST Number" value="<?php echo htmlspecialchars($salon_gst); ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group ">
                                    <label class="control-label col-form-label">WhatsApp Enable</label>
                                    <select name="whatsapp_enable" class="form-control custom-select">
                                        <option value="0" <?php echo ($whatsapp_enable == 0 ? 'selected' : ''); ?>>No</option>
                                        <option value="1" <?php echo ($whatsapp_enable == 1 ? 'selected' : ''); ?>>Yes</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="form-group ">
                                    <label class="control-label col-form-label">WhatsApp API Key</label>
                                    <input name="whatsapp_api" type="text" class="form-control" placeholder="API Key" value="<?php echo htmlspecialchars($whatsapp_api); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save changes</button>
    </div>    
</form>
