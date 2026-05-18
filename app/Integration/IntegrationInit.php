<?php
namespace GiantWP_Discount_Rules\Integration;

defined('ABSPATH') || exit;

use GiantWP_Discount_Rules\Traits\SingletonTrait;

// Themes
use GiantWP_Discount_Rules\Integration\Theme\DiviTheme;
// Plugins
use GiantWP_Discount_Rules\Integration\Plugin\LiteSpeedCache;
use GiantWP_Discount_Rules\Integration\Plugin\WPML;

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
        if ( defined('LSCWP_V') || class_exists('LiteSpeed_Cache') ) {
            LiteSpeedCache::instance();
        }

        if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
            WPML::instance();
        }
    }
}
