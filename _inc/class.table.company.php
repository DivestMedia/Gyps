<?php

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class companies_table extends WP_List_Table {
	
    function __construct(){
		global $status, $page;
			parent::__construct( array(
				'singular'  => __( 'Company', 'xyr' ),     //singular name of the listed records
				'plural'    => __( 'Companies', 'xyr' ),   //plural name of the listed records
				'ajax'      => false        //does this table support ajax?
		) );
		add_action( 'admin_head', array( &$this, 'admin_header' ) );            
    }
	
	
	function admin_header() {
		$page = ( isset($_GET['page'] ) ) ? esc_attr( $_GET['page'] ) : false;
		
		if( 'google-miner' == $page || 'google-miner-task' == $page ){
			echo '
			<style type="text/css">
				.wp-list-table .column-ID{ width:  100px; }
				.wp-list-table .column-taskID{ width:  100px; }
				.wp-list-table .column-counts { width:  80px; }
				.wp-list-table .column-statecode,
				.wp-list-table .column-country,.wp-list-table .column-date_added { width: 160px; }
				.wp-list-table .column-contact_no{ width: 200px; }
				.wp-list-table .column-website { width: 280px; }
			</style>';
		}
		return;
	}
	
	public function views($taskID = '') {
		global $wpdb;
		
		
		
		$taskID = $_GET['taskID'];
		//$_keyword = $wpdb->get_var( "SELECT keyw FROM " . GMINER_TBL_TASK ." WHERE ID = ". $taskID );
		
		
		if(isset($_GET['keyword'])){ // $_GET = Metro_Manila //underscore
			$_keyword = str_replace('_', ' ', $_GET['keyword']);
		}
		
		$_keywordClean = $_GET['keyword'];
		/* echo $wpdb->last_error;
		print_r($taskID);
		print_r($_keyword);
		 */
		$sql = "SELECT state,COUNT(state) as totalEntry  FROM ".GMINER_TBL_COMPANY 
			." WHERE FIND_IN_SET('".$_keyword."', tags) GROUP BY state";
		$result = $wpdb->get_results( $sql, 'ARRAY_A' );
		
		(int) $_count = $wpdb->get_var( "SELECT COUNT(*) FROM " . GMINER_TBL_COMPANY ." WHERE FIND_IN_SET('".$_keyword."', tags)");
		
		$views = array(
			'all' => '<a href="admin.php?page='.$_GET['page'].'&action=view&taskID='.$taskID.'&keyword='.$_keyword.'" class="'
				.(empty($_GET['state'])? 'current' : '') . '">View All <span class="count">('.$_count.')</span></a>',
		);
		
		
		if(isset($_GET['state'])){ // $_GET = Metro_Manila //underscore
			//$_stateClean = str_replace('_', ' ', $_GET['state']);
			$_stateClean = $_GET['state'];
		}
		
		foreach ( $result as $_row => $val ){
			$_state = str_replace(' ', '_',$val['state']);
			$views[$_state] = '<a href="admin.php?page='.$_GET['page'].'&action=view&taskID='.$taskID.'&keyword='.$_keywordClean.'&state='.$_state.'" class="'
				.($_stateClean == $_state ? 'current' : '') . '">'.$val['state'].' <span class="count">('.$val['totalEntry'].')</span></a>';
		}
		/* 
		$views = array(
			'all' => '<a href="#" class="current">View All</a>',
			'new' => '<a href="#">New Datas</a>',
		); */
		
		

		echo "<ul class='subsubsub'>\n";
		foreach ( $views as $class => $view ) {
			$views[ $class ] = "\t<li class='$class '>$view";
		}
		echo implode( " |</li>\n", $views ) . "</li>\n";
		echo "</ul>";
	}
	

	
	public static function get_companies( $per_page = 5, $page_number = 1  , $_keyword = '') {

		global $wpdb;
		$sql = "SELECT * FROM ".GMINER_TBL_COMPANY;
		$_where = array();
		
		
		//if(isset($_GET['keyword'])){ // $_GET = Metro_Manila //underscore
			$_keyword = str_replace('_', ' ', $_GET['keyword']);
		//}
		
		if ( ! empty( $_keyword ) ) {
			$_where[] = " FIND_IN_SET('".$_keyword."', tags) ";
		}
		
		if(isset($_GET['state'])){ // $_GET = Metro_Manila //underscore
			$_state = str_replace('_', ' ',$_GET['state']);
			$_where[] = " state='".$_state."' ";
		}
		
		if(isset($_GET['s'])){ // SEARCH
			$_where[] = " company_name LIKE '%".$_GET['s']."%' ";
		}
		
		if(!empty($_where)){
			$sql .= ' WHERE '. implode( ' AND ', $_where);
		}
		
		
		
		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
			$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
		}
		
		$sql .= " LIMIT $per_page";
		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

		$result = $wpdb->get_results( $sql, 'ARRAY_A' );
		return $result;
	}
	
	function no_items() {
		_e( 'No companies avaliable.', 'xyr' );
	}
	
	/* function get_hidden_columns(){
		$screen = get_current_screen();
		$columns = (array) get_user_option( 'manage'. $screen.'columnshidden');
        return $columns;
    } */
	
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'date_added':
			case 'contact_no':
			case 'website':
			case 'address':
			case 'state':
			case 'ID':
			case 'taskID':
				return $item[ $column_name ];
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}
	
	function get_sortable_columns() {
		$sortable_columns = array(
			'ID'  => array('ID',false),
			'company_name'  => array('company_name',false),
			'address' => array('address',false),
			'state'   => array('state',false),
			'taskID'   => array('taskID',false),
			'date_added'   => array('date_added',true)
		);
		return $sortable_columns;
	}

	function get_columns(){
        $columns = array(
            'cb'        => '<input type="checkbox" />',
            'ID' 		=> __( 'ID', 'xyr' ),
            'company_name' => __( 'Company Name', 'xyr' ),
            'address'    => __( 'Address', 'xyr' ),
            'contact_no'    => __( 'Contact Nos.', 'xyr' ),
            'website'    => __( 'Website', 'xyr' ),
            //'taskID'    => __( 'Task ID', 'xyr' ),
            'state'    => __( 'State', 'xyr' ),
            'date_added'    => __( 'Date Added', 'xyr' ),
        );
         return $columns;
    }
	
	function column_website( $item ) {
		if(!empty($item['website'])){
			$result = parse_url($item['website']);
			return '<a href="" target="_blank" title="' . $item['company_name'] . '">' . $result['host'] . '</a>';
		}else{
			return '';
		}
	}
	
	function column_company_name( $item ) {
		$delete_nonce = wp_create_nonce( 'xyr_delete_company' );
		$title = '<strong>' . $item['company_name'] . '</strong>';
		$actions = [
			'delete' => sprintf( '<a href="?page=%s&action=%s&customer=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['ID'] ), $delete_nonce )
		];
		return $title . $this->row_actions( $actions );
	}
	
	public function get_bulk_actions() {
		$actions = [
			'bulk-delete' => 'Delete'
		];
		return $actions;
	}
	function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="book[]" value="%s" />', $item['ID']
        );    
    }
	
	 public function search_box( $text, $input_id ) { ?>
    <p class="search-box">
      <label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
	  <input type="hidden" name="page" value="<?=$_REQUEST['page'];?>">
      <input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" placeholder="Search Company"/>
      <?php submit_button( $text, 'button', false, false, array('id' => 'search-submit') ); ?>
  </p>
<?php }

	
	function prepare_items($_keyword = '', $per_page  = 20) {
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
		$total_items  = self::record_count($_keyword);

		$this->set_pagination_args( [
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page'    => $per_page //WE have to determine how many items to show on a page
		] );

		$this->items = self::get_companies( $per_page, $current_page , $_keyword);
		
		$this->views($taskID);
		
		return $result;
	}
	
	public static function record_count($_keyword = 0) {
		global $wpdb;
		
		$sql = "SELECT COUNT(*) FROM " . GMINER_TBL_COMPANY;
		$_where = array();
		
		
		$_keyword = str_replace('_', ' ',$_keyword);
		
		
		if ( ! empty( $_keyword ) ) {
			$_where[] = " FIND_IN_SET('".$_keyword."', tags) ";
		}
		
		if(isset($_GET['state'])){ // $_GET = Metro_Manila //underscore
			$_state = str_replace('_', ' ',$_GET['state']);
			$_where[] = " state='".$_state."' ";
		}
		
		if(isset($_GET['s'])){ // SEARCH
			$_where[] = " company_name LIKE '%".$_GET['s']."%' ";
		}
		
		if(!empty($_where)){
			$sql .= ' WHERE '. implode( ' AND ', $_where);
		}
		
		return $wpdb->get_var( $sql );
	}
	
	public static function delete_company( $id ) {
		global $wpdb;

		$wpdb->delete(
			GMINER_TBL_COMPANY,
			[ 'ID' => $id ],
			[ '%d' ]
		);
	}
	
	public function process_bulk_action() {

		//Detect when a bulk action is being triggered...
		if ( 'delete' === $this->current_action() ) {

			// In our file that handles the request, verify the nonce.
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );

			if ( ! wp_verify_nonce( $nonce, 'xyr_delete_company' ) ) {
				die( 'Go get a life script kiddies' );
			}
			else {
				self::delete_company( absint( $_GET['customer'] ) );

				// esc_url_raw() is used to prevent converting ampersand in url to "#038;"
				// add_query_arg() return the current url
				wp_redirect( esc_url_raw(add_query_arg()) );
				exit;
			}

		}

		// If the delete bulk action is triggered
		if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
		     || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
		) {

			$delete_ids = esc_sql( $_POST['bulk-delete'] );

			// loop over the array of record IDs and delete them
			foreach ( $delete_ids as $id ) {
				self::delete_company( $id );

			}

			// esc_url_raw() is used to prevent converting ampersand in url to "#038;"
		        // add_query_arg() return the current url
		        wp_redirect( esc_url_raw(add_query_arg()) );
			exit;
		}
	}


}

add_filter('set-screen-option', 'cmi_set_option', 10, 3);
function cmi_set_option($status, $option, $value) {
    if ( 'company_per_page' == $option ) return $value;
    return $status;
 
}



