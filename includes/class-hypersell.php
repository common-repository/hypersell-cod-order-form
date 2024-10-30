<?php

/**
 * HyperSell setup
 *
 * @author   HyperSell
 * @category API
 * @package  HyperSell
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Main HyperSell Class.
 *
 * @class HyperSellApp
 * @version 0.0.1
 */
final class HyperSellApp {

    protected static $_instance = null;
    /**
     * HyperSellApp version.
     *
     * @var string
     */
    public $version = '0.0.1';

    /**
     * HyperSellApp Constructor.
     */
    public function __construct() {
        $this->define_constants();
        $this->includes();
        $this->init_hooks();
    }


    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Hook into actions and filters.
     *
     */
    private function init_hooks() {
        register_activation_hook( HYPERSELL_PLUGIN_FILE, array( 'HyperSell_Install', 'install' ) );
    }

    /**
     * Include required core files used in admin and on the frontend.
     */
    public function includes() {
        include_once( HYPERSELL_ABSPATH . 'includes/hypersell-core-functions.php' );
        include_once( HYPERSELL_ABSPATH . 'includes/class-hypersell-install.php' );
    }

    private function define_constants() {
        $this->define( 'HYPERSELL_ABSPATH', dirname( HYPERSELL_PLUGIN_FILE ) . '/' );
        $this->define( 'HYPERSELL_APP_URL', 'https://woo.hypersell.app/' );
    }

    private function define( $name, $value ) {
        if ( ! defined( $name ) ) {
            define( $name, $value );
        }
    }

    private function is_request( $type ) {
        switch ( $type ) {
            case 'admin' :
                return is_admin();
            case 'ajax' :
                return defined( 'DOING_AJAX' );
            case 'cron' :
                return defined( 'DOING_CRON' );
            case 'frontend' :
                return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
        }
    }
}