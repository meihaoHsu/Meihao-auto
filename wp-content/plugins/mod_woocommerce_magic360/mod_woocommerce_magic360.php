<?php
/*

Copyright 2008 MagicToolbox (email : support@magictoolbox.com)
Plugin Name: Magic 360 for WooCommerce
Plugin URI: http://www.magictoolbox.com/magic360/?utm_source=TrialVersion&utm_medium=WooCommerce&utm_content=plugins-page-plugin-url-link&utm_campaign=Magic360
Description: Wow your customers with real, interactive 360 spins of your products. Immediately improve your conversions with this proven sales tool. Refine the look with <a href="admin.php?page=WooCommerceMagic360-config-page">23 easy customisation options</a>.
Version: 6.9.9
Author: Magic Toolbox
Author URI: http://www.magictoolbox.com/?utm_source=TrialVersion&utm_medium=WooCommerce&utm_content=plugins-page-author-url-link&utm_campaign=Magic360


*/

/*
    WARNING: DO NOT MODIFY THIS FILE!

    NOTE: If you want change Magic 360 settings
            please go to plugin page
            and click 'Magic 360 Configuration' link in top navigation sub-menu.
*/

if(!function_exists('magictoolbox_WooCommerce_Magic360_init')) {
    /* Include MagicToolbox plugins core funtions */
    require_once(dirname(__FILE__)."/magic360-woocommerce/plugin.php");
}

//MagicToolboxPluginInit_WooCommerce_Magic360 ();
register_activation_hook( __FILE__, 'WooCommerce_Magic360_activate');

register_deactivation_hook( __FILE__, 'WooCommerce_Magic360_deactivate');

register_uninstall_hook(__FILE__, 'WooCommerce_Magic360_uninstall');

magictoolbox_WooCommerce_Magic360_init();
?>