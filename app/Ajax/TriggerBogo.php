<?php
  /**
 * BOGO Discount Trigger AJAX Handler.
 *
 * @package GiantWP_Discount_Rules
 */

namespace GiantWP_Discount_Rules\Ajax;

use GiantWP_Discount_Rules\Traits\SingletonTrait;

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
        add_action( 'wp_ajax_gwpdr_check_bogo_discounts', [ $this, 'gwpdr_check_bogo_discounts' ] );
        add_action( 'wp_ajax_nopriv_gwpdr_check_bogo_discounts', [ $this, 'gwpdr_check_bogo_discounts'] );
    }

    /**
     * AJAX callback: Triggers BOGO discount recalculation.
     *
     * @return void
     */
    public function gwpdr_check_bogo_discounts() {
        check_ajax_referer( 'gwpdr_triggerBogo_nonce', 'nonce' );

        // 🔥 Safe trigger
        do_action( 'gwpdr_run_bogo_discount', WC()->cart );
        WC()->cart->calculate_totals();

        wp_send_json_success( ['message' => '[gwpdr BOGO] Applied!'] );
    }
}
