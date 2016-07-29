<?php


if($_GET['page'] == 'google-miner-task' or $_GET['page'] == 'google-miner-task-create'){
	add_action( 'admin_enqueue_scripts', 'register_gminer_scripts');
	
}

function gminer_menu_item(){
	
	$hook = add_menu_page('Google Yellow Page System', 'GYP System', 'manage_options', 'google-miner', 'gminer_page_overview' );
    add_submenu_page('google-miner', 'Task Google Miner', 'Task', 'manage_options', 'google-miner-task' , 'gminer_page_task' );
    add_submenu_page('google-miner', 'Create Task Google Miner', 'Create New Task', 'manage_options', 'google-miner-task-create' , 'gminer_page_task_create' );
    add_submenu_page('google-miner', 'Settings - Google Miner', 'Settings', 'manage_options', 'google-miner-settings' ,  'gminer_page_settings');
	add_action( "load-$hook", 'add_minertable_options');
	
	
}
add_action("admin_menu", "gminer_menu_item", 20);

function add_minertable_options() {
	global $CompanyListTable, $screen_options_test;
	$option = 'per_page';
	$args = array(
		 'label' => 'Company per page',
		 'default' => 10,
		 'option' => 'company_per_page'
	);
	
	add_screen_option( $option, $args );
	
	
	if ( get_current_screen() ) {
		$CompanyListTable = new companies_table();
	}else{
		echo 'no screen'; exit;
	}
}


function gminer_page_overview(){
	include_once(GMINER_PATH_DIR .'_inc/page-overview.php');
	exit;
}
function gminer_page_settings(){
	global $xx;
	//$xx =  API_key_Screen::get_instance();
	include_once(GMINER_PATH_DIR .'_inc/page-settings.php');
	exit;
}

function gminer_page_task(){
	
	include_once(GMINER_PATH_DIR .'_inc/page-task.php');
	exit;
}
function gminer_page_task_create(){

	include_once(GMINER_PATH_DIR .'_inc/page-task-create.php');
	exit;
}

function register_gminer_scripts() {
	wp_register_style( 'fontawesome-css', GMINER_PATH_URL .'assets/font-awesome-4.6.3/css/font-awesome.min.css');
	wp_enqueue_style( 'fontawesome-css' );
	
	
}

