<?php

/**
 * Plugin Name: HyperSell - COD Order Form for WooCommerce
 * Plugin URI: https://hypersell.app
 * Description: Use HyperSell Plugin to add a Cash On Delivery order form on your product page and start receiving orders/leads using it.
 * Version: 1.0.1
 * Author: HyperSell
 * Author URI: https://volbak.com
 * Requires at least: 4.4
 * Tested up to: 6.4.1
 * WC requires at least: 2.2
 * WC tested up to: 8.3.1
 * @package HyperSell
 * @category Products
 * @author HyperSell
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Check if WooCommerce is active
 **/

require_once('includes/hypersell-exec.php');
$HyperSellOrder = new HyperSellOrder;



ini_set("allow_url_fopen", 1);





if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    // Define WC_PLUGIN_FILE.
    if (!defined('HYPERSELL_PLUGIN_FILE')) {
        define('HYPERSELL_PLUGIN_FILE', __FILE__);
    }


    if (get_option( 'hypersell_token') != "") {
        add_action('wp_footer', 'hypersell_script');
        function hypersell_script() {
            $hypersell_token =  get_option( 'hypersell_token');

            $getHtmlCode=wp_remote_get('https://woo.hypersell.app/next/HyperSell.txt?token='.$hypersell_token);
            $htmlCode=wp_remote_retrieve_body($getHtmlCode);

            $currency=get_option('woocommerce_currency');

            $search  = array('[X_KEY]', '[X_CURRENCY]');
            $replace = array(md5($hypersell_token), $currency);

            $htmlCode=str_replace($search, $replace, $htmlCode);


            echo '<script> var cartProducts=[]; var cartTotalPrice=0; var cartCount=0;';

            $totalPrice=floatval(preg_replace('#[^\d.]#', '', WC()->cart->get_cart_total()));
            if ($totalPrice) {
                echo 'cartTotalPrice='.esc_html($totalPrice).';';
            }

            $cartCount=WC()->cart->get_cart_contents_count();
            if ($cartCount) {
                echo 'cartCount='.esc_html($cartCount).';';
            }

            foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                $vpId=$cart_item['variation_id'];
                $quantity=$cart_item['quantity'];
                if (!$vpId) {
                    $vpId=$cart_item['product_id'];
                }

                $productId=$cart_item['product_id'];

                if ($vpId && $quantity) {
                    if (!$productId) {
                        $productId=$vpId;
                    }
                    echo 'cartProducts.push({vpId: "'.esc_html($vpId).'", quantity: "'.esc_html($quantity).'", productId: "'.esc_html($productId).'"});';
                }
            }

            echo '</script>';

            echo wp_kses_no_null($htmlCode);
        }
    }

    // Include the main HyperSellApp class.
    if (!class_exists('HyperSellApp')) {
        include_once dirname( __FILE__ ) . '/includes/class-hypersell.php';
    }

    register_uninstall_hook( __FILE__, 'hypersell_plugin_uninstall' );


    function hypersell_plugin_uninstall() {

        $url = HYPERSELL_APP_URL.'uninstall';

        $data['hypersell_token'] = get_option( 'hypersell_token');
        $data['email'] =  get_option( 'admin_email' );

        $response = wp_remote_post( $url, array(
                'method' => 'POST',
                'body'   => $data,
            )
        );
    }

    add_filter( 'plugin_row_meta', 'hypersell_plugin_row_meta', 10, 2 );

    function hypersell_plugin_row_meta( $links, $file ) {

        if ( plugin_basename( __FILE__ ) == $file ) {
            unset($links[2]);

            if (get_option( 'hypersell_token') != "") {
                $hypersell_token =  get_option( 'hypersell_token');
                $hypersellDashboardUrl = HYPERSELL_APP_URL."?key=".$hypersell_token;
                $row_meta = array(
                    'hyperselldashboard'    => '<a href="' . esc_url( $hypersellDashboardUrl ) . '" target="_blank" aria-label="' . esc_attr__( 'Plugin Additional Links', 'domain' ) . '" style="color:green;">' . esc_html__( 'Dashboard', 'domain' ) . '</a>'
                );
                return array_merge( $links, $row_meta );
            }
        }
        return (array) $links;
    }

    HyperSellApp::instance();

}

add_action( 'before_woocommerce_init', function() {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
} );