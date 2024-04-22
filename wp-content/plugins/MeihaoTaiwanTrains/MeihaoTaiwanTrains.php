<?php
/*
 * Plugin Name: Meihao-台灣交通時刻表查詢
 * Description: Meihao-台灣交通時刻表查詢 by RHan
 * Author: RHan
 * Plugin URI:#
 * Version 1.2
 * Text Domain: Meihao-Taiwan-Trains
 * Domain Path: /languages/
 */

if(!defined('MTT_DIR')){
    define('MTT_DIR',dirname(__FILE__));
}

define('MTT_URL',plugin_dir_url(__FILE__));

function Load_MTT_Plugin_Class(){

    load_plugin_textdomain( 'Meihao-Taiwan-Trains',false, dirname(plugin_basename(__FILE__)) . '/languages/' );

    include MTT_DIR.'/includes/class_meihao_taiwan_trains-main.php';
    $GLOBALS['MTT']=MTT();

}

function MTT(){
    return MTT_Plugin::instance();
}

add_action('plugins_loaded','Load_MTT_Plugin_Class');
