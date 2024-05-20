<div class="col-md-6 p-4">
        <div class="card">

                <input type="hidden" value="" name="cust_id" class="cust_id" />
                <div class="form-group">
                    <label for="phone">Mobile Number *</label>

                    <input value="<?php echo $customer['cust_mobile'] ?>" autocomplete="off" type="text" name="cust_mob" required pattern="\d*" minlength="10" maxlength="10" class="form-control cust_mob" id="cust_mob" placeholder="Enter Phone Number">
                </div>
                <div class="form-group">
                    <label for="Customer">Customer Name *</label>
                    <input type="text"  value="<?php echo $customer['cust_name'] ?>" name="cust_name" required class="form-control required cust_name" id="cust_name" placeholder="Enter Customer Name">
                </div>

                <?php 
                if(!$job_card_id)
                {
                ?>
                   
                    <div class="form-group gender_check">
                        <label for="date">Gender</label>
                        <select name="cust_gender" class="cust_gender form-control" >
                            <option value="Female">Female</option>
                            <option value="Male">Male</option>
                        </select>
                    </div>

                    <div class="form-group reffer_check">
                        <label for="date">How did you reach us?</label>
                        <select name="cust_reffer" class="cust_reffer form-control" >
                            <option value="0">Select Ref</option>
                            <option value="WalkIn">WalkIn</option>
                            <option value="Instagram">Instagram</option>
                            <option value="Facebook">Facebook</option>
                            <option value="Google Ads">Google</option>
                            <?php
                            $ref = select_array("SELECT * FROM `hr_user_owner` where salon_id=".$salon_id." and ref_enable=1");
                            foreach ($ref as $ref_name){
                                ?>
                                <option value="<?php echo $ref_name['user_name']; ?>"><?php echo $ref_name['user_name']; ?></option>
                            <?php } ?>
                            <option value="Mr. Munish Bajaj">Mr Bajaj</option>
                            <option value="Staff">Staff Ref</option>
                        </select>
                    </div>
                <?php } ?>
        </div>
    </div>


    <div class="col-md-6 p-4">
            <div class="card-body">
                <blockquote class="blockquote blockquote-primary customer_detail" style="display:none">
                    <h5>Customer Detail</h5>
                    <address class="text-primary">
                        <p class="font-weight-bold"> Customer Name </p>
                        <p class="mb-2 cust_name"></p>
                        <p class="font-weight-bold"> Customer Wallet </p>
                        <p class="mb-2 cust_wallet"></p>
                        <input type="hidden" name="check_wallet" id="check_wallet" />
                        <p class="font-weight-bold"> Customer Outstanding </p>
                        <p class="mb-2 cust_outstanding"></p>
                        <p class="font-weight-bold"> Remark </p>
                        <p class="mb-2 billing_remark"></p>

                    </address>
                </blockquote>
            </div>
    </div>