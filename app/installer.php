<?php 

namespace AIO_WooDiscount;

use AIO_WooDiscount\Traits\SingletonTrait;
use AIO_WooDiscount\Admin\Menu;
use AIO_WooDiscount\Assets;

/**
 * Plugin Functions Installer Class
 */
class Installer{
    

    use SingletonTrait;
    
    /**
     * Class Constructor 
     */
    public function __construct(){
        
        if( is_admin() ){
            Menu::instance();
            Assets::instance();
            
        }

        
    }

}