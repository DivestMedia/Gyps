<?php
/**
 * Plugin Name: Gyps
 * Version: 2.0-beta
 * Description: Google Yellow Page System
 * Author: Jenner F. Alagao
 *
 * License: GPLv2 or later
 *
*/
$dir_path = plugin_dir_path( __FILE__ );
$url_path = plugin_dir_url( __FILE__ );

define('GMINER_VERSION','1.00');
define('GMINER_DB_VERSION','1.00');

define('GMINER_PATH_DIR',$dir_path);
define('GMINER_PATH_URL',$url_path);


$_api_key_private = get_option('gminer_api_key_private','',true);
$_api_key_private_1 = get_option('gminer_api_key_private_1','',true);

//define('GMINER_API_SECRET','AIzaSyD2sT2Udch-pxB4-MBCfeX-LdOW694WhxE');
define('GMINER_API_SECRET', trim($_api_key_private));

global $wpdb;

define('GMINER_TBL_COMPANY', $wpdb->prefix .'gminer_companies');
define('GMINER_TBL_TASK', $wpdb->prefix .'gminer_task');


include_once( GMINER_PATH_DIR .'_inc/menu.php');
include_once( GMINER_PATH_DIR .'_inc/class.table.company.php');
include_once( GMINER_PATH_DIR .'_inc/class.company.php');
include_once( GMINER_PATH_DIR .'_inc/ajax.functions.php');
include_once( GMINER_PATH_DIR .'_inc/class.task.php');
include_once( GMINER_PATH_DIR .'_inc/functions.php');

register_activation_hook( __FILE__, 'gminer_install' );
