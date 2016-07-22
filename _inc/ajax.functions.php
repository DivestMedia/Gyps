<?php



add_action( 'wp_ajax_search_company_reff', 'search_company_reff' );
add_action( 'wp_ajax_nopriv_search_company_reff', 'search_company_reff' );

function search_company_reff() {
	global $wpdb;
	
	$_data = $_POST;
	
	if(empty($_data['country']))
		$_data['country'] = 'US';
	
	//echo $_data['nextpage_token'];
	$_taskinfo = array(
		'keyw' => $_data['keywords'],
		'statecode' =>  ucwords($_data['state']),
		'country' => $_data['country'],
		'colorhex' => '#ffbbbb',
		'date_added' => date("Y-m-d H:i:s"),
	);
	
	
	if(empty($_data['task_id'])){
		$wpdb->insert(GMINER_TBL_TASK,$_taskinfo);
		$_taskID = $wpdb->insert_id;
	}else{
		$_taskID = $_data['task_id'];
	}
	
	if(!empty($_taskID)){
		// Handle request then generate response using WP_Ajax_Response
		$_nextToken = company::search_save($_data['keywords'], $_data['state'], $_data['country'], $_taskID , $_data['nextpage_token']);
		$_nextToken['taskID'] = $_taskID;
	}else{
		$_ret = array("status"=>"Error");
	}
	
	
	echo json_encode($_nextToken);
	
	
	exit;
}

add_action( 'wp_ajax_cleandb_reffKeys', 'cleandb_reffKeys' );
add_action( 'wp_ajax_nopriv_cleandb_reffKeys', 'cleandb_reffKeys' );

function cleandb_reffKeys() {
	global $wpdb;
	
	
	$_data = $_POST;
	$_results = $wpdb->get_results("SELECT * FROM ". GMINER_TBL_COMPANY ." WHERE location=''", ARRAY_A);
	foreach ( $_results as $result ){
		company::request_fullinfo( $result['reff_key']);
	}
	
	
	
	
	$_countCompany =  $wpdb->get_var( "SELECT COUNT(*) FROM " . GMINER_TBL_COMPANY. " WHERE taskID = " . $_data['task_id']);
	
	$_ret = array("status"=>"DONE" , "total" =>$_countCompany);
	echo json_encode($_ret);
	
	exit;
}

add_action( 'wp_ajax_delete_task', 'delete_task' );
add_action( 'wp_ajax_nopriv_delete_task', 'delete_task' );

function delete_task() {
	global $wpdb;
	
	$_data = $_POST;
	$wpdb->query("DELETE FROM ". GMINER_TBL_COMPANY ." WHERE taskID='".$_data['task_id']."'");
	
	$_resultsCompany = $wpdb->rows_affected;
	
	$_resultsTask = $wpdb->query("DELETE FROM ". GMINER_TBL_TASK ." WHERE ID='".$_data['task_id']."'");
	
	$_ret = array("status"=>"DONE" , "message" =>"$_resultsCompany Companies deleted to your mines list under current task request.");
	echo json_encode($_ret);
	
	exit;
}

add_action( 'wp_ajax_export_task', 'export_task' );
add_action( 'wp_ajax_nopriv_export_task', 'export_task' );

function export_task() {
	global $wpdb;
	
	$_data = $_POST;
	
	 
	$_resultsTask = $wpdb->get_results("SELECT * FROM ". GMINER_TBL_TASK ." WHERE ID=".$_data['task_id'], ARRAY_A);
	
	foreach ( $_resultsTask as $result ){
		$_filnameTitle = $result['keyw'].' in '.$result['statecode'].' '.$result['country'].'-'. date("F j, Y, g:i a"); 
	}
	
	$filename = sanitize_title($_filnameTitle);
		// output headers so that the file is downloaded rather than displayed
		header('Content-Type: application/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename='.$filename.'.csv');
		//header('Pragma: no-cache');
		header("Content-Transfer-Encoding: binary");
		//header('Pragma: public'); 

		
		@unlink($filename);
		// create a file pointer connected to the output stream
		$output = fopen( GMINER_PATH_DIR .'/cache/'.$filename .'.csv', 'w');

		// output the column headings
		fputcsv($output, array('Company Name', 'Address', 'Website','Contact Nos.','State'));

		
		$results = mysql_query('SELECT company_name,address,website,contact_no,state FROM '. GMINER_TBL_COMPANY .' WHERE taskID='.$_data['task_id']);
		
		$_results = $wpdb->get_results('SELECT company_name,address,website,contact_no,state FROM '. GMINER_TBL_COMPANY .' WHERE taskID='.$_data['task_id'], ARRAY_A);
		foreach ( $_results as $result ){
			fputcsv($output, $result);
		}
			
		//echo $wpdb->last_error;
		// loop over the rows, outputting them
		//while ($row = mysql_fetch_array($results,MYSQL_ASSOC)) fputcsv($output, $row);
		
		//echo $output;
	// ob_clean();
		//flush();
	
	
	
	$_ret = array("status"=>"DONE" , "url" => GMINER_PATH_URL .'/cache/'.$filename .'.csv');
	echo json_encode($_ret); 
	
	exit;
}

add_action( 'wp_ajax_request_states', 'request_states' );
add_action( 'wp_ajax_nopriv_request_states', 'request_states' );

function request_states() {
	global $wpdb;
	$_data = $_POST;
	
}





