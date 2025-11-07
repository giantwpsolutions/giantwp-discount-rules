<?php
/**
 * LiteSpeed Cache integration for GiantWP Discount Rules.
 *
 * Purges cache when our plugin signals changes (e.g., rules updated).
 *
 * @package GiantWP_Discount_Rules\Integration
 */

namespace GiantWP_Discount_Rules\Integration\Plugin;

defined('ABSPATH') || exit;

use GiantWP_Discount_Rules\Traits\SingletonTrait;

class LiteSpeedCache
{
    use SingletonTrait;

    /**
     * Bootstrap hooks.
     *
     * We listen to our own internal "gwpdr/clear_cache" event, and also
     * hook option updates that are likely to change front-end pricing/promo.
     */
    protected function __construct()
    {
        // Generic purge trigger you can call from anywhere in your plugin:
        add_action('gwpdr/clear_cache', [$this, 'purgeAll']);

        // Option changes that affect output (add more if needed).
        add_action('update_option_giantwp_bogo_discount', [$this, 'purgeAll'], 10, 0);
        add_action('update_option_giantwp_bxgy_discount', [$this, 'purgeAll'], 10, 0);
        add_action('update_option_giantwp_bulk_discount', [$this, 'purgeAll'], 10, 0);
        add_action('update_option_giantwp_shipping_discount', [$this, 'purgeAll'], 10, 0);
        add_action('update_option_giantwp_flatpercentage_discount', [$this, 'purgeAll'], 10, 0);
        add_action('update_option_giantwp_discountrules_settings',[$this, 'purgeAll'], 10, 0);


    }

    /**
     * Purge all LiteSpeed cache (no-op if LiteSpeed isn't installed).
     *
     * Calling do_action on a non-registered tag is safe; it'll just do nothing.
     */
    public function purgeAll(): void
    {
        /**
         * LiteSpeed Cache plugin listens to this tag:
         * do_action( 'litespeed_purge_all' );
         */
        do_action('litespeed_purge_all');
    }

    /**
     * Optional: purge a specific URL (e.g., a product page) instead of global.
     */
    public function purgeUrl(string $url): void
    {
        if (! empty($url)) {
            /**
             * LiteSpeed supports per-URL purge:
             * do_action( 'litespeed_purge_url', $url );
             */
            do_action('litespeed_purge_url', $url);
        }
    }
}
