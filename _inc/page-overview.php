<div class="wrap">

	<h1>Miner Overview
	
	<? if(isset($_REQUEST['s'])){ ?>
	 <span class="subtitle">Search results for &#8220;<?php _admin_search_query(); ?>&#8221;</span>
	<? } ?>
	</h1>
	
	<?php
	global $CompanyListTable;
	$CompanyListTable->prepare_items(); 
	?>
	<form method="get">
		
		<?php
			$CompanyListTable->search_box( 'Search', 'company_name' );
			$CompanyListTable->display(); 
		?>
	</form>
	
	<style type="text/css">
		.wp-list-table .column-ID{ width:  100px; }
		.wp-list-table .column-taskID{ width:  100px; }
		.wp-list-table .column-counts { width:  80px; }
		.wp-list-table .column-statecode,
		.wp-list-table .column-country,.wp-list-table .column-date_added { width: 160px; }
		.wp-list-table .column-contact_no{ width: 200px; }
		.wp-list-table .column-website { width: 280px; }
	</style>
	
	<hr/>
</div>
