<?php





class company{
	
	
	
	function __construct() {
	
	
	}


	//wp_gminer_companies
	
	public function is_exist($_reffkey){
		global $wpdb;
		$_found = $wpdb->get_var("SELECT reff_key FROM ".GMINER_TBL_COMPANY." WHERE reff_key='".$_reffkey."'");
		if($_found){
			return true;
		}
		return false;
	}
	public function company_exist($_name , $_location ){
		global $wpdb;
		$_found = $wpdb->get_var("SELECT ID FROM ".GMINER_TBL_COMPANY." WHERE company_name='".$_name."' AND location='".$_location."'");
		if($_found){
			return $_found;
		}
		return false;
	}
	
	
	public function add($_info,$_full = false){
		global $wpdb;
		if(company::is_exist($_info['reff_key']) == false){
			//get the whole company info
			/* $_url_req = "https://maps.googleapis.com/maps/api/place/details/xml".$_reffkey."?reference=&key=". GMINER_API_SECRET;
			$result = wp_remote_get($_url_req, true); */
			$wpdb->insert(GMINER_TBL_COMPANY,$_info);
			
			$lastid = $wpdb->insert_id;
			if($_full == true){
				company::request_fullinfo($_info['reff_key']);
			}
			return $lastid;
		}
		return false;
	}
	
	public function update($_info, $_ID = 0){
		global $wpdb;
		//get the whole company info
		/* $_url_req = "https://maps.googleapis.com/maps/api/place/details/xml".$_reffkey."?reference=&key=". GMINER_API_SECRET;
		$result = wp_remote_get($_url_req, true); */
		$wpdb->update(GMINER_TBL_COMPANY,$_info , array('ID' => $_ID));
		$lastid = $wpdb->rows_affected;
		//return as boolean
		return $lastid;
	}
	
	
	
	public function search_save($_keyword, $_state, $_country = 'US', $_taskID =0, $_pageToken = '', $_saveDB = true,$_full = false, $_city = ''){
		
		
		$_keyword_user = $_keyword;
		$_city_q = empty($_city) ? '' : $_city . ',';
		$_keyword = str_replace(' ','+',$_keyword  . ' in '. $_city_q . $_state .','. $_country);
		$q = $_keyword ;
		
		
		$_pageTokenLink = '';
		if(!empty($_pageToken)){
			
			$order   = array("\r\n", "\n", "\r");
			$_pageToken = str_replace($order, '', $_pageToken);
			$_pageTokenLink = '&pagetoken='.$_pageToken.'&sensor=true';
			
			//$_pageTokenLink = '&nextpage='.$_pageToken.'&sensor=true';
			$_url_req = "https://maps.googleapis.com/maps/api/place/textsearch/json?key=". GMINER_API_SECRET . $_pageTokenLink;
			
		}else{
			$_url_req = "https://maps.googleapis.com/maps/api/place/textsearch/json?query=". $q ."&key=". GMINER_API_SECRET . $_pageTokenLink;
		}
		
		//echo $_url_req;
		
		$args = array(
			 'headers' => array( 
				"User-Agent" => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.9; rv:32.0) Gecko/20100101 Firefox/32.0"
			) 
		);
		
		
		$result = wp_remote_get($_url_req, $args);
		
		$decode = json_decode($result['body'], true);
		$results = $decode['results'];
		$status = $decode['status'];
		$_nextToken = $decode['next_page_token'];


		$_task = array(
			'url' => $_url_req,
			'status' => $status,
			'date' => date("Y-m-d H:i:s"),
			'next_page_token' => $_nextToken
		);
		update_option('google-miner', $_task);
		
		if($status != 'OK'){
			//let the ajax make it retry by our saved optioin info
			$_refkeyRet['next'] = '';
			$_refkeyRet['status'] = $status;
			return $_refkeyRet;
		}
		
		if($_saveDB == false){
			echo 'Cant Saved';
			return false;
		}
		if(empty($_state)){
			echo 'Empty State';
			return false;
		}
		
		$_counts = array(
			'new' => array(),
			'update' => array()
		);
		
		
		//$_refkeyRet = array();
		foreach ($results as $_result) {
			
			if(company::is_exist($_result['reference']) == false){
				//just save the Reff Key -- completing the company info will be separate ajax req.
				
				
				$_companyInfo = array(
					'address' => $_result['formatted_address'],
					'reff_key' => $_result['reference'],
					'company_name' => $_result['name'],
					'state' => $_state,
					'taskID' => $_taskID ,
					'tags' => $_keyword_user ,
					'date_added' => date("Y-m-d H:i:s")
				);
				
				
				$_loc = @$_result['geometry']['location'];
				$_latlang = $_loc['lat'] .','. $_loc['lng'];
			
				$_companyID = company::company_exist($_result['name'] , $_latlang) ;
				
				//company::add($_companyInfo, $_full);
				
				if(!empty($_companyID)){
					//$_companyInfo['tags'] .= ',' . $_keyword_user;
					$_tags = explode( ',',$_companyInfo['tags']);
					$_newtagsAll = array_merge($_tags , array( $_keyword_user));
					$_newtags = array_unique($_newtagsAll);
					$_companyInfo['tags'] = implode(',', $_newtags);
					
					company::update($_companyInfo , $_companyID );
					$_counts['update'][] = $_companyID;
					//echo "update\n";
				}else{
					$_ID = company::add($_companyInfo, $_full);
					$_counts['new'][] = $_ID;
					//echo "new\n";
				}
				
				
				//echo 'OK<br/>';
				//$_refkeyRet[] = $_result['reference'];
				
			}else{
				//echo 'Error<br/>';
			}
			flush();
		}
		
		$_refkeyRet['next'] = $_nextToken;
		$_refkeyRet['status'] = $status;
		$_refkeyRet['companyID'] = $_counts;
		$_refkeyRet['counts'] = ((int)sizeof($_counts['new']) + (int)sizeof($_counts['update']));
		return $_refkeyRet;
	}
	
	public function request_fullinfo($_refKey){
		$_url_req = "https://maps.googleapis.com/maps/api/place/details/json?reference=".$_refKey."&key=". GMINER_API_SECRET;
		
		$args = array(
			'headers' => array( 
				"User-Agent" => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.9; rv:32.0) Gecko/20100101 Firefox/32.0"
			)
		);
		
		$result = wp_remote_get($_url_req, $args);
		$decode = json_decode($result['body'], true);
		$results = $decode['result'];
		$status = $decode['status'];
		
		
		global $wpdb;
		
		//echo '<br>'.$_refKey.'<hr>';
		if(company::is_exist($_refKey) == true){
			
			
			$_loc = @$results['geometry']['location'];
			$_latlang = $_loc['lat'] .','. $_loc['lng'];
			
			$_info = array(
				'address' => @$results['vicinity'],
				'location' => $_latlang,
				//'logo' => $_result['vicinity'],
				'website' => @$results['website'],
				'contact_no' => @$results['formatted_phone_number'],
				//'emails' => $_result['vicinity'],
				//'state' => $_result['vicinity']
			);
			$wpdb->update(GMINER_TBL_COMPANY,$_info , array('reff_key' => $_refKey) );
		
			return true;
		}
		return false;
		
		
	}
	
	
	
}