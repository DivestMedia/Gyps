<?php

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class task_table extends WP_List_Table {
	
    function __construct(){
		global $status, $page;
			parent::__construct( array(
				'singular'  => __( 'Task', 'xyr' ),     //singular name of the listed records
				'plural'    => __( 'Tasks', 'xyr' ),   //plural name of the listed records
				'ajax'      => false        //does this table support ajax?
		) );
    }
	
	
	public static function get_tasks( $per_page = 20, $page_number = 1, $_task =0) {

		global $wpdb;
		
		$sql = "SELECT ID, keyw, colorhex, date_added, country, statecode,COUNT(statecode) as counts  FROM ".GMINER_TBL_TASK ." GROUP BY keyw";
		
		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
			$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
		}/* else{
			$sql .= ' ORDER BY ID DESC';
		} */
		
		$sql .= " LIMIT $per_page";
		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

		$result = $wpdb->get_results( $sql, 'ARRAY_A' );
		
		return $result;
	}
	
	function no_items() {
		_e( 'No tasks avaliable.', 'xyr' );
	}
	
	/* function get_hidden_columns(){
		$screen = get_current_screen();
		$columns = (array) get_user_option( 'manage'. $screen.'columnshidden');
        return $columns;
    } */
	
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'ID':
			case 'date_added':
			case 'statecode':
			case 'counts':
			case 'country':
			case 'colorhex':
			case 'keyw':
				return $item[ $column_name ];
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}
	
	function get_sortable_columns() {
		$sortable_columns = array(
			'ID'  => array('ID',true),
			'company_name'  => array('company_name',false),
			'keyw'  => array('keyw',false),
			'counts'  => array('counts',false),
			'country' => array('country',false),
			'statecode'   => array('statecode',false),
			'date_added'   => array('date_added',false)
		);
		return $sortable_columns;
	}

	function get_columns(){
        $columns = array(
            'cb'        => '<input type="checkbox" />',
            'ID' => __( 'ID', 'xyr' ),
            'keyw' => __( 'Keyword', 'xyr' ),
            'statecode'    => __( 'State', 'xyr' ),
            'country'    => __( 'Country', 'xyr' ),
            'counts' => __( 'Total', 'xyr' ),
            'colorhex'    => __( 'ColorHEX', 'xyr' ),
            'date_added'    => __( 'Date Added', 'xyr' ),
        );
         return $columns;
    }
	
	function column_keyw( $item ) {
		$delete_nonce = wp_create_nonce( 'xyr_delete_task' );
		$title = '<strong>' . $item['keyw'] . '</strong>';
		
		$_keyword = str_replace(' ','_',$item['keyw']);
		
		$actions = [
			'view' => sprintf( '<a href="?page=%s&action=%s&taskID=%s&keyword=%s">View</a>', esc_attr( $_REQUEST['page'] ), 'view', absint( $item['ID'] ) , $_keyword) , 
			'export' => sprintf( '<a href="?page=%s&action=%s&taskID=%s" taskID="%s" class="export-task">Export</a>', esc_attr( $_REQUEST['page'] ), 'export', absint( $item['ID'] ), absint( $item['ID'] ) ),
			'delete' => sprintf( '<a href="?page=%s&action=%s&taskID=%s&_wpnonce=%s" taskID="%s" class="delete-task">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['ID'] ), $delete_nonce , absint( $item['ID'] ))
		];
		return $title . $this->row_actions( $actions );
	}
	function column_counts( $item ) {
		global $wpdb;
		
		(int) $_count = $wpdb->get_var( "SELECT COUNT(*) FROM " . GMINER_TBL_COMPANY ." WHERE FIND_IN_SET('".$item['keyw']."', tags)");
		
		$_totalmines =  sprintf( '<a href="?page=%s&action=%s&taskID=%s&keyword=%s">'. $_count .'</a>', esc_attr( $_REQUEST['page'] ), 'view',  $item['ID'] , $_keyword) ;
		
		return $_totalmines;
	}
	
	
	function column_country( $item ) {
		global $wpdb;
		
		$result = $wpdb->get_results("SELECT DISTINCT(country) FROM " . GMINER_TBL_TASK ." WHERE keyw ='".$item['keyw']."'", 'ARRAY_A' );
		
		$_country = array();
		foreach($result as $_row){
			if(file_exists(GMINER_PATH_DIR. '/assets/flags/'.$_row['country'] . '.png')){
				$_country[] = '<img src="'.GMINER_PATH_URL. '/assets/flags/'.$_row['country'] . '.png" title="'. strtoupper($_row['country']) .'"> ';
			}
		
		}
		
		return implode(' ',$_country);
	}
	
	function column_statecode( $item ) {
		global $wpdb;
		
		$result = $wpdb->get_results("SELECT DISTINCT(statecode) FROM " . GMINER_TBL_TASK ." WHERE keyw ='".$item['keyw']."'", 'ARRAY_A' );
		
		$_country = array();
		
		foreach($result as $_row){
			$_country[] = '<li>'.ucwords($_row['statecode']) . '</li>';
		}
		
		return '<ol>'.implode('',$_country) .'</ol>';
	}
	
	
	function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="book[]" value="%s" />', $item['ID']
        );    
    }
	
	function prepare_items($taskID = 0) {
		/* global $screen_options_test;
		$screen = get_current_screen();
		$user = get_current_user_id(); */
		
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		/** Process bulk action */
		$this->process_bulk_action();
		
		/* $option = $screen->get_option('per_page', 'option');
		$per_page = get_user_meta($user, $option, true);
		
		if ( empty ( $per_page) || $per_page < 1 ) {
			$per_page = $screen->get_option( 'per_page', 'default' );
		} 
		 */
		
		$per_page  = 20;
		
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count($taskID);

		$this->set_pagination_args( [
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page'    => $per_page //WE have to determine how many items to show on a page
		] );

		$this->items = self::get_tasks( $per_page, $current_page , $taskID);
		return $result;
	}
	
	public static function record_count($taskID = 0) {
		global $wpdb;
		
		$sql = "SELECT COUNT(DISTINCT(keyw)) FROM ".GMINER_TBL_TASK;

		$_er = $wpdb->get_var( $sql );
		
		return $wpdb->get_var( $sql );
	}
	
	public static function delete_task( $id ) {
		global $wpdb;

		$wpdb->delete(
			GMINER_TBL_TASK,
			[ 'ID' => $id ],
			[ '%d' ]
		);
	}
	
}



