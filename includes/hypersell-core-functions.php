<?php
/**
 * HyperSell Core Functions
 *
 * General core functions available on both the front-end and admin.
 *
 * @author      HyperSell
 * @category    Core
 * @package     HyperSell/Functions
 * @version     0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function hypersell_maybe_define_constant( $name, $value ) {
    if ( ! defined( $name ) ) {
        define( $name, $value );
    }
}

add_action('admin_menu', 'hypersell_dashboard');
function hypersell_dashboard() {
    global $submenu;

    if (get_option( 'hypersell_token') != "") {
        $hypersell_token =  get_option( 'hypersell_token');
        $hypersellDashboardUrl = HYPERSELL_APP_URL."?key=".$hypersell_token;
        $submenu['woocommerce'][] = array(
            '<div id="hypersellDashboard">HyperSell</div>', 'manage_options', $hypersellDashboardUrl);
    }
}

add_action( 'admin_footer', 'hypersell_dashboard_blank' );
function hypersell_dashboard_blank()
{
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#hypersellDashboard').parent().attr('target','_blank');

        });
    </script>
    <?php
}

add_action( 'rest_api_init', function ( $server ) {
    $server->register_route( 'hypersell', '/hypersell', array(
        'methods'  => 'GET',
        'callback' => function () {
            return '813';
        },
    ) );
} );


add_action( 'wp_ajax_hypersell_create_order', 'hypersell_create_order' );
add_action( 'wp_ajax_nopriv_hypersell_create_order', 'hypersell_create_order' );

function hypersell_create_order() {
    global $HyperSellOrder;

    $order=json_decode(stripslashes(wp_kses_post($_POST['order'])), true);
    $result=$HyperSellOrder->insert_order($order);

    echo json_encode($result);
    exit();
}