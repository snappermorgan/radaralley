<?php defined('ABSPATH') or die; # Don't ever load this file directly

// For debug purposes only
// set_site_transient( 'update_plugins', null );

/**
 * ZOOM_Builder_Updater Class
 *
 * Handles everything related to updating the plugin.
 *
 * @package ZOOM_Builder
 * @subpackage Updater
 */

class ZOOM_Builder_Updater {
    protected $api_endpoint = 'http://reploy.wpzoom.com/api/v1/';
    protected $file;
    private $errors = array();
    private static $instance;

    public static function getInstance() {
        if (!self::$instance) {
            return null;
        }
        return self::$instance;
    }

    public static function init( $plugin_file ) {
        if (self::$instance) return;
        self::$instance = new self( $plugin_file );
    }

    private function __construct( $plugin_file ) {
        $this->file = plugin_basename( $plugin_file );
        $this->slug = 'zoom-builder';

        $this->hook();
    }

    private function hook() {
        add_action( 'pre_set_site_transient_update_plugins', array( $this, 'updateCheck' ) );
        add_action( 'plugins_api', array( $this, 'pluginInformation' ), 10, 3 );

        add_action( 'network_admin_notices', array( $this, 'license_notice' ) );
        add_action( 'network_admin_notices', array( $this, 'license_activate_notice' ) );
        if ( ! is_multisite() ) {
            add_action( 'admin_notices', array( $this, 'license_notice' ) );
            add_action( 'admin_notices', array( $this, 'license_activate_notice' ) );
        }
        add_action( 'admin_init', array( $this, 'license_notice_dismiss' ) );
    }

    /**
     * Check for updates from Reploy API.
     */
    public function updateCheck( $transient ) {
        if ( empty ( $transient->checked ) ) {
            return $transient;
        }

        $args = array(
            'slug' => $this->slug,
            'version' => $transient->checked[ $this->file ],
            'license_key' => get_site_option('wpzlb-settings-license-key')
        );

        $response = $this->request( 'plugin/update-check', $args );

        if ( $response === false ) {
            return $transient;
        }

        $transient->response[ $this->file ] = $response;

        return $transient;
    }

    /**
     * Retrieve plugin information from Reploy API.
     */
    public function pluginInformation( $false, $action, $args ) {
        if ( ! isset( $args->slug ) || $args->slug != $this->slug ) {
            return $false;
        }

        $args = array(
            'slug' => $args->slug,
            'license_key' => get_site_option('wpzlb-settings-license-key')
        );

        $response = $this->request( 'plugin/info', $args );

        if ( $response ) {
            $response->sections = (array) $response->sections;
        }

        return $response;
    }

    /**
     * Request helper for Reploy API.
     */
    private function request( $action, $args ) {
        global $wp_version;

        $info = array(
            'site_url'   => home_url( '/' ),
            'wp_version' => $wp_version,
        );

        $url = $this->api_endpoint . $action . '/?' . build_query( $info );

        $options = array(
            'timeout' => ( ( defined('DOING_CRON') && DOING_CRON ) ? 30 : 5 ),
            'user-agent' => 'WordPress/' . $wp_version . '; ' . home_url( '/' ),
            'body' => $args
        );

        $response = wp_remote_post( $url, $options );

        if ( is_wp_error( $response ) || 200 != wp_remote_retrieve_response_code( $response ) ) {
            $this->log_error( __( 'WPZOOM API Request Error. Please contact customer support if the problem persists.', 'zoom-builder' ) );
            return false;
        } else {
            $data = json_decode( wp_remote_retrieve_body( $response ) );
        }

        if ( isset( $data->error ) ) {
            $error = esc_html( $data->error );
            $this->log_error( $error );
        } else if ( empty( $data ) ) {
            $this->log_error( __( 'There was an error while making request, please try again in a few seconds.', 'zoom-builder' ) );
        }

        return $data;
    }

    public function license_activate_request() {
        if ( ! isset( $_POST['action'] ) ) return;
        if ( $_POST['action'] != 'activate-license-key' ) return;
        if ( ! check_admin_referer( 'activate-license-key' ) ) return;

        $license_key = $_POST['wpzlb-settings-license-key'];
        $status = 'false';

        if ( empty( $license_key ) ) {
            $this->log_error( __( 'No license key provided! Go and grab your license key from WPZOOM Members area.', 'zoom-builder' ) );
        } else {
            $args = array(
                'product_slug' => $this->slug,
                'license_key' => $license_key
            );
            $response = $this->request('license/activate', $args);

            if ($response != false && $response->success == true ) {
                $status = 'true';
                update_site_option( 'wpzlb-settings-license-key', $license_key );
            } else {
                update_site_option( 'wpzlb-settings-license-key', '' );
            }
        }

        $this->store_error_log();

        wp_safe_redirect( add_query_arg( 'status', $status, add_query_arg( 'page', 'wpzlb-settings-license', network_admin_url( 'admin.php' ) ) ) );
        exit;

    }

    public function license_deactivate_request() {
        if ( ! isset( $_POST['action'] ) ) return;
        if ( $_POST['action'] != 'deactivate-license-key' ) return;
        if ( ! check_admin_referer( 'deactivate-license-key' ) ) return;

        update_site_option( 'wpzlb-settings-license-key', '' );

        wp_safe_redirect( add_query_arg( 'page', 'wpzlb-settings-license', network_admin_url( 'admin.php' ) ) );
        exit;
    }

    public function license_activate_notice() {
        if ( ! isset( $_GET['status'] ) || ! in_array( $_GET['status'], array('true', 'false') ) ) return;

        $notification_class = 'update-nag wpzlb-update-nag ';
        if ( $_GET['status'] == 'true' && count( $this->get_error_log() ) == 0 ) {
            $status_message = __( 'Product activated successfully.', 'zoom-builder' );
            $notification_class .= 'wpzlb-update-nag-success ';
        } else {
            $status_message = __( 'There was an error during product activation.', 'zoom-builder' );
            $notification_class .= 'wpzlb-update-nag-error ';
        }


        $errors_list = '';
        $request_errors = $this->get_error_log();
        if ( is_array( $request_errors ) && count( $request_errors ) > 0 ) {
            foreach ( $request_errors as $error ) {
                $errors_list .= '<li>' . wpautop( $error ) . '</li>';
            }
        }

        printf( '<div class="%s">', $notification_class );
        printf( '<p class="status">%s</p>', $status_message );
        if ( ! empty( $errors_list ) ) printf( '<ul>%s</ul>', $errors_list );
        printf( '</div>' );

        $this->clear_error_log();
    }

    public function license_notice() {
        if ( isset($_GET['page'] ) && $_GET['page'] == 'wpzlb-settings-license' ) return;
        if ( ! current_user_can( 'manage_options') ) return;
        if ( is_multisite() && ! is_super_admin() ) return;
        if ( get_site_option( 'wpzlb_settings_dismiss_license_notice' ) > time() - 60 * 60 * 24 * 7  ) return;
        if ( get_site_option( 'wpzlb-settings-license-key' ) !== false  ) return;

        $url = add_query_arg( 'page', 'wpzlb-settings-license', network_admin_url( 'admin.php' ) );
        $dismiss_url = add_query_arg( 'action', 'wpzlb-settings-dismiss-license-notice', add_query_arg( 'nonce', wp_create_nonce( 'wpzlb-settings-dismiss-license-notice' ) ) );

        echo '<div class="updated fade"><p class="alignleft">' . sprintf( __( '<b>In order to recieve updates for ZOOM Builder plugin you need to</b> %sactivate your license%s first.', 'zoom-builder' ), '<a href="' . esc_url( $url ) . '">', '</a>' ) . '</p><p class="alignright"><a href="' . esc_url( $dismiss_url ) . '">' . __( 'Dismiss', 'zoom-builder' ) . '</a></p><div class="clear"></div></div>' . "\n";
    }

    public function license_notice_dismiss() {
        if ( isset($_GET['action']) && isset($_GET['nonce']) ) {
            if ($_GET['action'] == 'wpzlb-settings-dismiss-license-notice' && check_admin_referer( 'wpzlb-settings-dismiss-license-notice', 'nonce' ) ) {
                update_site_option( 'wpzlb_settings_dismiss_license_notice', time() );

                $redirect_url = remove_query_arg( 'action', remove_query_arg( 'nonce', $_SERVER['REQUEST_URI'] ) );

                wp_safe_redirect( $redirect_url );
                exit;
            }
        }
    }

    private function log_error( $error ) {
        $this->errors[] = $error;
    }

    private function store_error_log() {
        set_transient( 'wpzlb-request-error', $this->errors );
    }

    private function get_error_log() {
        return get_transient( 'wpzlb-request-error' );
    }

    private function clear_error_log() {
        return delete_transient( 'wpzlb-request-error' );
    }
}