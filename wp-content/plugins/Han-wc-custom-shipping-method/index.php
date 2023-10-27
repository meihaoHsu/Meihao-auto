<?php
/*
 * Plugin Name: Han WC Custom Shipping Method
 * Description: Woocommerce - 自定義物流
 * Version: 1.0.0
 * Author: RHan
*/

 

$active_plugins2 = apply_filters('active_plugins', get_option('active_plugins'));

if (in_array('woocommerce/woocommerce.php', $active_plugins2)) {
    require_once __DIR__ . '/wc-first-shipping.php';
	require_once __DIR__ . '/wc-second-shipping.php';
	require_once __DIR__ . '/wc-third-shipping.php';
    

}
