<div class="wrap">

	<h1>Miner Overview</h1>
	
	<?php
	global $CompanyListTable;
	$CompanyListTable->prepare_items(); 
	?>
	<form method="post">
		<input type="hidden" name="page" value="ttest_list_table">
		<?php
			//$CompanyListTable->search_box( 'search', 'search_id' );
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
