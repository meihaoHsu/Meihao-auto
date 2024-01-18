<?php
if ( ! function_exists( 'add_action' ) ) {
    echo "Please enable this plugin from your wp-admin.";
    exit;
}

class WooCommerce_Magic360_autoupdate {
    private $changelogURL = 'https://www.magictoolbox.com/magic360/modules/woocommerce/';

    private $slug = 'magic360';

    public static function init() {
        static $instance;
        if ( empty( $instance ) )
            $instance = new WooCommerce_Magic360_autoupdate();
        return $instance;
    }
    function __construct() {
        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'checkForUpdates' ), 10, 1 );
        add_action( 'install_plugins_pre_plugin-information', array( $this, 'overrideUpdateInformation' ), 1 );
    }

    function mod_WooCommerce_Magic360_backup() {
        $fileContetns = file_get_contents(plugin_dir_path(__FILE__).'magic360.js');
        delete_option("WooCommerce_Magic360_backup");
        add_option("WooCommerce_Magic360_backup", $fileContetns,'','no');
    }


    function checkForUpdates( $value ) {
        global $update_plugin;
        if (!$update_plugin)
            return $value;

        $key = magictoolbox_WooCommerce_Magic360_get_data_from_db();
        if ($key) { $key = $key->license; }

        if (function_exists('mb_convert_encoding')) {
            $ver = json_decode(mb_convert_encoding(@file_get_contents('http://www.magictoolbox.com/api/platform/wordpress/version/'), 'HTML-ENTITIES', "UTF-8"));
        } else {
            $ver = json_decode(utf8_decode(htmlentities(@file_get_contents('http://www.magictoolbox.com/api/platform/wordpress/version/'), ENT_COMPAT, 'utf-8', false)));
        }

        if (empty($ver))
            return $value;

        $ver = str_replace('v', '', $ver->version);
        $oldVer = plugin_get_version_WooCommerce_Magic360();

        $this->mod_WooCommerce_Magic360_backup();

        if ($key) {
            $_url = 'https://www.magictoolbox.com/site/order/'.$key.'/';
            $_package = 'https://www.magictoolbox.com/site/order/'.$key.'/woocommerce/magic360.zip';
        } else {
            $_url = 'https://www.magictoolbox.com/static/';
            $_package = 'https://www.magictoolbox.com/static/mod_woocommerce_magic360.zip';
        }


        if (version_compare($oldVer, $ver, '<')) {
            $response = new stdClass();
            $response->id = 0;
            $response->slug = 'magic360';
            $response->new_version = $ver;
            $response->plugin = 'mod_woocommerce_magic360/mod_woocommerce_magic360.php';
            $response->url = $_url;
            $response->package = $_package;

            $value->response['mod_woocommerce_magic360/mod_woocommerce_magic360.php'] = $response;
        }

        return $value;
    }

    function overrideUpdateInformation() {
        if ( wp_unslash( $_REQUEST['plugin'] ) !== $this->slug )
            return;

        wp_redirect( $this->changelogURL );
        exit;
    }
}

add_action( 'current_screen', function () {
    $current_screen = get_current_screen();
    if( $current_screen->id === "plugins" ) {
        add_action( 'plugins_loaded', array( 'WooCommerce_Magic360_autoupdate', 'init' ) );
    }    
} );
?>
