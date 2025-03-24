<?php
  /**
 * BOGO Discount Trigger AJAX Handler.
 *
 * @package DealBuilder_Discount_Rules
 */

namespace DealBuilder_Discount_Rules\Ajax;

use DealBuilder_Discount_Rules\Traits\SingletonTrait;

defined( 'ABSPATH' ) || exit;

/**
 * Class TriggerBogo
 *
 * Handles AJAX calls to manually trigger BOGO discount logic.
 */
class TriggerBogo {
    
    use SingletonTrait;
     
    /**
     * Constructor to register AJAX actions.
     */
    public function __construct() {
        add_action( 'wp_ajax_db_check_bogo_discounts', [ $this, 'db_check_bogo_discounts' ] );
        add_action( 'wp_ajax_nopriv_db_check_bogo_discounts', [ $this, 'db_check_bogo_discounts'] );
    }

    /**
     * AJAX callback: Triggers BOGO discount recalculation.
     *
     * @return void
     */
    public function db_check_bogo_discounts() {
        check_ajax_referer( 'db_triggerBogo_nonce', 'nonce' );

        // 🔥 Safe trigger
        do_action( 'db_run_bogo_discount', WC()->cart );
        WC()->cart->calculate_totals();

        wp_send_json_success( ['message' => '[DB BOGO] Applied!'] );
    }
}
