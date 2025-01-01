<?php 

function WoocommerceDeactivationAlert(){
    ?>
            <div class="notice notice-error is-dismissible">
                <p>
                    <?php _e( 
                        'WooCommerce is deactivated! The "All-in-One WooDiscount" plugin requires WooCommerce to function properly. Please reactivate WooCommerce.', 
                        'aio-woodiscount' 
                    ); ?>
                </p>
            </div>
            <?php
}


function WoocommerceMissingAlert(){
    ?>
        <div class="notice notice-error is-dismissible">
            <p>
                <?php _e( 
                    'All-in-One WooDiscount requires WooCommerce to be installed and active.', 
                    'aio-woodiscount' 
                ); ?>
            </p>
        </div>
        <?php
}