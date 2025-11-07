<?php
namespace GiantWP_Discount_Rules\Integration;

defined('ABSPATH') || exit;

use GiantWP_Discount_Rules\Traits\SingletonTrait;

// Themes
use GiantWP_Discount_Rules\Integration\Theme\DiviTheme;
// Plugins
use GiantWP_Discount_Rules\Integration\Plugin\LiteSpeedCache;

class IntegrationInit {
    use SingletonTrait;

    // 🔒 keep protected when using SingletonTrait
    protected function __construct() {
        $this->boot();
    }

    public function boot(): void {
        // ——— Themes ———
        if ( class_exists('ET_Builder_Element') ) {
            DiviTheme::instance();
        }

        // ——— Plugins ———
        // LiteSpeed Cache — you can either guard or always load (safe no-op)
        if ( defined('LSCWP_V') || class_exists('LiteSpeed_Cache') ) {
            LiteSpeedCache::instance();
        }
        // Or, simply: LiteSpeedCache::instance(); // also fine (do_action is harmless)
    }
}
