<?php
/**
 * Installation related functions and actions.
 *
 * @author   HyperSell
 * @category Admin
 * @package  HyperSell/Classes
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * WC_Install Class.
 */
class HyperSell_Install {
    /**
     * Install HyperSell.
     */

    public static function init() {
        add_action( 'admin_init', array( __CLASS__, 'install_actions' ) );
    }

    public static function install_actions() {
    }

    public static function install() {
        set_transient( 'hypersell_installing', 'yes', 5 );

        self::maybe_enable_setup_wizard();
    }

    /**
     * See if we need the wizard or not.
     *
     * @since 3.2.0
     */
    private static function maybe_enable_setup_wizard() {

            $user = wp_get_current_user();

            $email = $user->user_email;
            $blog_details = get_bloginfo(1);
            $admin_email = get_option( 'admin_email' );
            $user = get_user_by( 'email', $admin_email );
            $name = $user->display_name;

            $url = HYPERSELL_APP_URL.'authUser';

            $data['owner'] = $name;
            $data['email'] = $admin_email;
            $data['domain'] = get_option( 'siteurl' );//$_SERVER['HTTP_HOST'];

            $response = wp_remote_post( $url, array(
                    'method' => 'POST',
                    'body'   => $data,
                )
            );

            if ( !is_wp_error( $response ) ) {
                $hypersellReply = json_decode($response['body'],true);
                if ($hypersellReply['mode']) {
                    $hypersellToken = $hypersellReply['token'];
                    update_option('hypersell_token',$hypersellToken, true);
                }
            }
    }
}

HyperSell_Install::init();
