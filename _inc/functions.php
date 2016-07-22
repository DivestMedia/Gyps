<?php

function gminer_install(){
	
	global $wpdb;
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	$_sql[] = '
		CREATE TABLE IF NOT EXISTS `'. GMINER_TBL_COMPANY .'` (
		  `ID` bigint(11) NOT NULL AUTO_INCREMENT,
		  `reff_key` varchar(400) NOT NULL,
		  `company_name` varchar(255) NOT NULL,
		  `address` text NOT NULL,
		  `location` varchar(255) NOT NULL,
		  `logo` varchar(255) NOT NULL,
		  `taskID` bigint(11) NOT NULL,
		  `website` varchar(255) NOT NULL,
		  `contact_no` varchar(255) NOT NULL,
		  `emails` varchar(255) NOT NULL,
		  `state` varchar(255) NOT NULL,
		  `tags` text NOT NULL,
		  `date_added` datetime NOT NULL,
		  PRIMARY KEY (`ID`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1;
	';
	
	$_sql[] = '	
		CREATE TABLE `'. GMINER_TBL_TASK .'` (
		  `ID` bigint(10) NOT NULL AUTO_INCREMENT,
		  `keyw` varchar(255) NOT NULL,
		  `statecode` varchar(255) NOT NULL,
		  `country` varchar(255) NOT NULL,
		  `colorhex` varchar(255) NOT NULL,
		  `date_added` datetime NOT NULL,
		  PRIMARY KEY (`ID`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1;
	';

	foreach($_sql as $sql){
		dbDelta($sql);
	}
	
	update_option( "GMINER_DB_VERSION", GMINER_DB_VERSION );
	update_option( "GMINER_VERSION", GMINER_VERSION );
	
}


function gminer_update_db_check() {

    if ( get_site_option( 'GMINER_VERSION' ) != GMINER_VERSION ) {
        gminer_install();
    }
}
add_action( 'plugins_loaded', 'gminer_update_db_check' );
