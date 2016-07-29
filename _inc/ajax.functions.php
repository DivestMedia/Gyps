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
	$_keyword = $wpdb->get_var("SELECT keyw FROM ". GMINER_TBL_TASK ." WHERE ID='".$_data['task_id']."'");
	
	if(!empty($_keyword)){
		//delete all matched keyword...
		$wpdb->query("DELETE FROM ". GMINER_TBL_TASK ." WHERE keyw='".$_keyword."'");
		
		
		//delete the company with that keyword/tag
		$wpdb->query("DELETE FROM ". GMINER_TBL_COMPANY ." WHERE tags='".$_keyword."'");
		$_resultsCompany = $wpdb->rows_affected;
		
		//SEARCH that keyword on exist/multi company keywords
		$_resultsTag = $wpdb->get_results("SELECT * FROM ". GMINER_TBL_COMPANY ." WHERE FIND_IN_SET('".$_keyword."', tags)", ARRAY_A);
		foreach ( $_resultsTag as $result ){
			$_tags = explode(',', $result['tags']);
			$_tags = array_diff($_tags, array($_keyword));
			$_tagsImplode = implode(',', $_tags);
			$_resultsTask = $wpdb->query("UPDATE ". GMINER_TBL_COMPANY ." SET tags='".$_tagsImplode."' WHERE ID='".$result['ID']."'");
		}
	}
	
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

function cleandbTags(){
	
	global $wpdb;
	$_results = $wpdb->get_results("SELECT * FROM ". GMINER_TBL_COMPANY, ARRAY_A);
	
	$_companyInfo = array();
	foreach ( $_results as $result ){
		
		$_tags = explode( ',',$result['tags']);
		$_newtags = array_unique($_tags);
		$_companyInfo['tags'] = implode(',', $_newtags);
		
		company::update($_companyInfo , $result['ID'] );
	}
	return true;
	
}



add_action( 'wp_ajax_export_xls_full', 'export_xls_full' );
add_action( 'wp_ajax_nopriv_export_xls_full', 'export_xls_full' );

function export_xls_full() {
	global $wpdb;
	
	//cleandbTags();
	
	// Create new PHPExcel object
	$objPHPExcel = new PHPExcel();

	// Set document properties
	$objPHPExcel->getProperties()->setCreator("Gyps ". GMINER_VERSION ." by: Jenner F. Alagao")
		 ->setTitle("Gyps ". GMINER_VERSION ." Exported Data")
		 ->setDescription("Gyps ". GMINER_VERSION ." Exported Data");
	
	$_x = 0;
	$_results = $wpdb->get_results('SELECT * FROM '. GMINER_TBL_COMPANY .' ORDER BY state, company_name ASC', ARRAY_A);
	

	$objPHPExcel->setActiveSheetIndex(0);
	$objPHPExcel->getActiveSheet()->setTitle( 'Full Database' );
	$objPHPExcel->getActiveSheet()
		->setCellValue('A1', 'Full Database: ' . date("F d, Y - g:iA"))
		->setCellValue('A2', 'Company Name')
		->setCellValue('B2', 'Address')
		->setCellValue('C2', 'Contact Nos.')
		->setCellValue('D2', 'Website')
		->setCellValue('E2', 'State')
		->setCellValue('F2', 'Keyword');
 
	$i = 3;
	foreach ( $_results as $result ){
		
		$objPHPExcel->getActiveSheet()
			->setCellValue('A' . $i, $result['company_name'])
			->setCellValue('B' . $i, $result['address'])
			->setCellValue('C' . $i, $result['contact_no'])
			->setCellValue('D' . $i, $result['website'])
			->setCellValue('E' . $i, $result['state'])
			->setCellValue('F' . $i, $result['tags']);

		$i++;
	}
	 $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getFont()->setSize(14);
	$objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('FFb2b6f2');
	$objPHPExcel->getActiveSheet()->getStyle('A1:F1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	
	$objPHPExcel->getActiveSheet()->mergeCells('A1:F1');
	
	$objPHPExcel->getActiveSheet()->getStyle('A2:F2')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyle('A2:F2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFF00');
	$objPHPExcel->getActiveSheet()->getStyle('A2:F2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	 
	
	
	$objPHPExcel->getActiveSheet()->freezePane('A3');
	$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(40);
	$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(60);
	$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(18);
	$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
	$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
	
	
	$objPHPExcel->getActiveSheet()->setAutoFilter('E2:E'. $i);
	/* 
	$autoFilter = $objPHPExcel->getActiveSheet()->getAutoFilter();
	$columnFilter = $autoFilter->getColumn('E');
	$columnFilter->setFilterType(
		PHPExcel_Worksheet_AutoFilter_Column::AUTOFILTER_FILTERTYPE_FILTER
	);
	
 */
		 
	
	$_fileName = sanitize_title('Full Export-') . date("Y-M-d_g_iA") . '_'. md5(date("Y-M-d_g_iA"));
	$_filePath = GMINER_PATH_DIR .'/cache/'.$_fileName .'.xlsx';
	$_fileURL = GMINER_PATH_URL .'/cache/'.$_fileName .'.xlsx';
	
	

	
	// Set active sheet index to the first sheet, so Excel opens this as the first sheet
	$objPHPExcel->setActiveSheetIndex(0);

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save( $_filePath , 'w');

	$_ret = array("status"=>"DONE" , "url" => $_fileURL);
	echo json_encode($_ret); 
	
	exit;
	
}

add_action( 'wp_ajax_export_xls_state', 'export_xls_state' );
add_action( 'wp_ajax_nopriv_export_xls_state', 'export_xls_state' );

function export_xls_state() {
	global $wpdb;
	
	
	$_data = $_POST;
	$_state = trim($_data['states']);
	//cleandbTags();
	
	// Create new PHPExcel object
	$objPHPExcel = new PHPExcel();

	// Set document properties
	$objPHPExcel->getProperties()->setCreator("Gyps ". GMINER_VERSION ." by: Jenner F. Alagao")
		 ->setTitle("Gyps ". GMINER_VERSION ." Exported Data")
		 ->setDescription("Gyps ". GMINER_VERSION ." Exported Data");
	
	$_x = 0;
	$_results = $wpdb->get_results('SELECT * FROM '. GMINER_TBL_COMPANY .' WHERE state = "'. $_state . '" ORDER BY company_name ASC', ARRAY_A);
	
	$objPHPExcel->setActiveSheetIndex(0);
	$objPHPExcel->getActiveSheet()->setTitle( $_state );
	$objPHPExcel->getActiveSheet()
		->setCellValue('A1', 'State: ' . $_state)
		->setCellValue('A2', 'Company Name')
		->setCellValue('B2', 'Address')
		->setCellValue('C2', 'Contact Nos.')
		->setCellValue('D2', 'Website')
		->setCellValue('E2', 'Keyword');
 
	$i = 3;
	foreach ( $_results as $result ){
		
		$objPHPExcel->getActiveSheet()
			->setCellValue('A' . $i, $result['company_name'])
			->setCellValue('B' . $i, $result['address'])
			->setCellValue('C' . $i, $result['contact_no'])
			->setCellValue('D' . $i, $result['website'])
			->setCellValue('E' . $i, $result['tags']);

		$i++;
	}
	 $objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getFont()->setSize(14);
	$objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('FFb2b6f2');
	$objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	
	$objPHPExcel->getActiveSheet()->mergeCells('A1:E1');
	
	$objPHPExcel->getActiveSheet()->getStyle('A2:E2')->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getStyle('A2:E2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFF00');
	$objPHPExcel->getActiveSheet()->getStyle('A2:E2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	 
	
	
	$objPHPExcel->getActiveSheet()->freezePane('A3');
	$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(40);
	$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(60);
	$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(18);
	$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
	$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
		 
	
	$_fileName = sanitize_title('State-'. $_state) .'_'. date("Y-M-d_g_iA") . '_'. md5(date("Y-M-d_g_iA"));

	$_filePath = GMINER_PATH_DIR .'/cache/'.$_fileName .'.xlsx';
	$_fileURL = GMINER_PATH_URL .'/cache/'.$_fileName .'.xlsx';
	
	

	
	// Set active sheet index to the first sheet, so Excel opens this as the first sheet
	$objPHPExcel->setActiveSheetIndex(0);

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save( $_filePath , 'w');

	$_ret = array("status"=>"DONE" , "url" => $_fileURL);
	echo json_encode($_ret); 
	
	exit;
	
}


add_action( 'wp_ajax_export_xls', 'export_xls' );
add_action( 'wp_ajax_nopriv_export_xls', 'export_xls' );

function export_xls() {
	global $wpdb;
	
	$_data = $_POST;
	
	$_clean = cleandbTags();
	
	/* 
	 
	$_resultsTask = $wpdb->get_results("SELECT * FROM ". GMINER_TBL_TASK ." WHERE ID=".$_data['task_id'], ARRAY_A);
	
	foreach ( $_resultsTask as $result ){
		$_filnameTitle = $result['keyw'].' in '.$result['statecode'].' '.$result['country'].'-'. date("F j, Y, g:i a"); 
	}
	
	$filename = sanitize_title($_filnameTitle);
	
	@unlink($filename);
	// create a file pointer connected to the output stream
	$output = fopen( GMINER_PATH_DIR .'/cache/'.$filename .'.csv', 'w');

	$results = mysql_query('SELECT company_name,address,website,contact_no,state FROM '. GMINER_TBL_COMPANY .' WHERE taskID='.$_data['task_id']);
	
	$_results = $wpdb->get_results('SELECT company_name,address,website,contact_no,state FROM '. GMINER_TBL_COMPANY .' WHERE taskID='.$_data['task_id'], ARRAY_A);
	foreach ( $_results as $result ){
		fputcsv($output, $result);
	}
		
	$_ret = array("status"=>"DONE" , "url" => GMINER_PATH_URL .'/cache/'.$filename .'.csv');
	echo json_encode($_ret); 
	
	 */
	
	
	
	// Create new PHPExcel object
	$objPHPExcel = new PHPExcel();

	// Set document properties
	$objPHPExcel->getProperties()->setCreator("Gyps ". GMINER_VERSION ." by: Jenner F. Alagao")
		 ->setTitle("Gyps ". GMINER_VERSION ." Exported Data")
		 ->setDescription("Gyps ". GMINER_VERSION ." Exported Data");
	
	/* 
	$_statesArray = array();
	$_statesArray[] = "Alabama";
	$_statesArray[] = "California";
	$_statesArray[] = "Florida"; */
	
	
	$_statesArray =  explode(',',$_data['states']);
	
	$_keyword = trim($_data['keyword']);
	
	
	$_x = 0;
	foreach($_statesArray as $_state){
	
		$sql = "SELECT * FROM ".GMINER_TBL_COMPANY ." WHERE FIND_IN_SET('".$_keyword."', tags) AND state = '{$_state}' ORDER BY company_name ASC";
		$_results = $wpdb->get_results( $sql, 'ARRAY_A' );

		// Add WorkSheet - State
		if($_x > 0){
			$objPHPExcel->createSheet($_x);
		}
		$objPHPExcel->setActiveSheetIndex($_x);
		$objPHPExcel->getActiveSheet()->setTitle( ucwords($_state) );
	
	 
		$objPHPExcel->getActiveSheet()
			->setCellValue('A1', 'KEYWORD: '.$_keyword)
			->setCellValue('A2', 'Company Name')
			->setCellValue('B2', 'Address')
			->setCellValue('C2', 'Contact Nos.')
			->setCellValue('D2', 'Website')
			->setCellValue('E2', 'State');
	 
		$i = 3;
		foreach ( $_results as $result ){
			
			$objPHPExcel->getActiveSheet()
				->setCellValue('A' . $i, $result['company_name'])
				->setCellValue('B' . $i, $result['address'])
				->setCellValue('C' . $i, $result['contact_no'])
				->setCellValue('D' . $i, $result['website'])
				->setCellValue('E' . $i, $result['state']);

			$i++;
			
		}
		$objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getFont()->setSize(14);
		$objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('FFb2b6f2');
		$objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		
		$objPHPExcel->getActiveSheet()->mergeCells('A1:E1');
		
		$objPHPExcel->getActiveSheet()->getStyle('A2:E2')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getStyle('A2:E2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFF00');
		$objPHPExcel->getActiveSheet()->getStyle('A2:E2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		
		
		
		$objPHPExcel->getActiveSheet()->freezePane('A3');
		//$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(50);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(60);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
		
		
		$_x++;
	}
	
	$_fileName = sanitize_title($_keyword) .'_'. date("Y-M-d_g_iA") . '_'. md5(date("Y-M-d_g_iA"));
	$_filePath = GMINER_PATH_DIR .'/cache/'.$_fileName .'.xlsx';
	$_fileURL = GMINER_PATH_URL .'/cache/'.$_fileName .'.xlsx';
	
	

	
	// Set active sheet index to the first sheet, so Excel opens this as the first sheet
	$objPHPExcel->setActiveSheetIndex(0);

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save( $_filePath , 'w');

	$_ret = array("status"=>"DONE" , "url" => $_fileURL);
	echo json_encode($_ret); 
	
	exit;
	
}

add_action( 'wp_ajax_request_states', 'request_states' );
add_action( 'wp_ajax_nopriv_request_states', 'request_states' );

function request_states() {
	global $wpdb;
	$_data = $_POST;
	
	$_country = $_data['country'];
	
	//clean STATE
 	/* include_once(GMINER_PATH_DIR .'assets/states/US.php');
	global $states;
	$_resultsTask = $wpdb->get_results("SELECT * FROM ". GMINER_TBL_TASK ." WHERE CHAR_LENGTH(statecode) < 3 LIMIT 1000", ARRAY_A);
	foreach ( $_resultsTask as $result ){
		$_state = $result['statecode'];
		if(array_key_exists($_state , $states['US'])){
			$wpdb->update(GMINER_TBL_TASK, array('statecode'=>$states['US'][$_state]), array('ID' => $result['ID']));
		echo $result['ID']. '---';
		}
	}
	
	echo sizeof($_resultsTask).'OK';
	exit;
	 
	  */
	if(file_exists( GMINER_PATH_DIR .'assets/states/'. $_country .'.php')){
		global $states;
		
		include_once(GMINER_PATH_DIR .'assets/states/'. $_country .'.php');
		echo json_encode($states[$_country]);
		exit;
		 
	}else{
		echo json_encode(array('NA' => 'No States Available'));
	}
	exit;
}





