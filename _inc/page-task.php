<div class="wrap">


<?php if(!isset($_GET['taskID'])){ ?>
<h1>Tasks
	<a href="admin.php?page=google-miner-task-create" class="page-title-action">Create New Task</a>
</h1>

	
	
	<div id="message"></div>
	<?php
	$TaskListTable = new task_table();
	$TaskListTable->prepare_items(); 
	?>
	<form method="post">
		<input type="hidden" name="page" value="ttest_list_table">
		<?php
			//$TaskListTable->search_box( 'search', 'search_id' );
			$TaskListTable->display(); 
		?>
	</form>
	
		<hr/>
	<style type="text/css">
		.wp-list-table .column-ID{ width:  100px; }
		.wp-list-table .column-counts { width:  80px; }
		.wp-list-table .column-statecode{ width: 300px !important; }
		.wp-list-table .column-colorhex,
		.wp-list-table .column-country { width: 150px; }
		.wp-list-table .column-date_added, .wp-list-table .column-contact_no, .wp-list-table .column-website { width: 180px; }
	</style>
	
	<div id="modal-window-delete" style="display:none;">
    <p>Lorem Ipsum sit dolla amet.</p>
</div>
	
	<script>
	
	jQuery(document).ready(function($){
	
		$('.export-task').on('click',function(e){
			
			/* alert('Export not yet working.');
			
			return ; */
			var taskID = $(this).attr('taskID');
			var keyw = $(this).attr('keys');
			var states = $(this).attr('states');
			
				e.preventDefault();
				$.ajax({
					url: '<?php echo admin_url('admin-ajax.php');?>' ,
					data: {
						task_id: taskID,
						//action: 'export_task'
						states: states,
						keyword: keyw,
						action: 'export_xls'
					},
					tryCount : 0,
					retryLimit : 5,
					timeout: 25000,
					error: function(x, t, m) {
						if(t==="timeout") {
							$('#task-info-error').html('<p>An error has occurred -  Timeout: Exporting Task</p>');
							
							this.tryCount++;
							if (this.tryCount <= this.retryLimit) {
								$('#task-current').html('<i class="fa fa-spinner fa-pulse fa-fw"></i>Retry: Exporting Task - '+ this.tryCount);
								//try again
								$.ajax(this);
								
								return;
							}else{
								$('#task-info-error').html('<p>Erro: Retry Max : '+ this.retryLimit+'</p>');
							}
							return;
						}
						
						if (x.status == 500) {
							$('#task-info-error').html('<p>An error has occurred -  Error: 500</p>');
						} else {
							$('#task-info-error').html('<p>An error has occurred -  Error: Unknown</p>');
						}
					},
					
					success: function(data) {
						
						var retReq = jQuery.parseJSON(data); 
						//$('#message').html(data);
						console.log(location.href = retReq.url);
						//alert(data);
						return;
						/* if(retReq.status == 'OK'){
							$('#message').html(data);
							$('#message').addClass('updated');
						}else{
							$('#message').html('<p>'+retReq.message+'</p>');
							$('#message').addClass('error');
						} */
						
					},
					type: 'POST'
				
				});
				console.log('done');
				
				
			return false;
		});
		
		
		$('#export_full').on('click',function(e){
			
			e.preventDefault();
			$.ajax({
				url: '<?php echo admin_url('admin-ajax.php');?>' ,
				data: {
					action: 'export_xls_full'
				},
				tryCount : 0,
				retryLimit : 5,
				timeout: 25000,
				error: function(x, t, m) {
					if(t==="timeout") {
						$('#task-info-error').html('<p>An error has occurred -  Timeout: Exporting Task</p>');
						
						this.tryCount++;
						if (this.tryCount <= this.retryLimit) {
							$('#task-current').html('<i class="fa fa-spinner fa-pulse fa-fw"></i>Retry: Exporting Task - '+ this.tryCount);
							//try again
							$.ajax(this);
							
							return;
						}else{
							$('#task-info-error').html('<p>Erro: Retry Max : '+ this.retryLimit+'</p>');
						}
						return;
					}
					
					if (x.status == 500) {
						$('#task-info-error').html('<p>An error has occurred -  Error: 500</p>');
					} else {
						$('#task-info-error').html('<p>An error has occurred -  Error: Unknown</p>');
					}
				},
				
				success: function(data) {
					
					var retReq = jQuery.parseJSON(data); 
					//$('#message').html(data);
					console.log(location.href = retReq.url);
					//alert(data);
					return;
					/* if(retReq.status == 'OK'){
						$('#message').html(data);
						$('#message').addClass('updated');
					}else{
						$('#message').html('<p>'+retReq.message+'</p>');
						$('#message').addClass('error');
					} */
					
				},
				type: 'POST'
			
			});
			console.log('done');
				
				
			return false;
		});
		
		
		
				
		$('#export_state').on('click',function(e){
			
			e.preventDefault();
			
			var sel_state = $('#export_by_state').val();
			if(sel_state == ''){
				alert('Please select State to export');
				$('#export_by_state').focus();
				return false;
			}
			
			
			$.ajax({
				url: '<?php echo admin_url('admin-ajax.php');?>' ,
				data: {
					states: sel_state,
					action: 'export_xls_state'
				},
				tryCount : 0,
				retryLimit : 5,
				timeout: 25000,
				error: function(x, t, m) {
					if(t==="timeout") {
						$('#task-info-error').html('<p>An error has occurred -  Timeout: Exporting Task</p>');
						
						this.tryCount++;
						if (this.tryCount <= this.retryLimit) {
							$('#task-current').html('<i class="fa fa-spinner fa-pulse fa-fw"></i>Retry: Exporting Task - '+ this.tryCount);
							//try again
							$.ajax(this);
							
							return;
						}else{
							$('#task-info-error').html('<p>Erro: Retry Max : '+ this.retryLimit+'</p>');
						}
						return;
					}
					
					if (x.status == 500) {
						$('#task-info-error').html('<p>An error has occurred -  Error: 500</p>');
					} else {
						$('#task-info-error').html('<p>An error has occurred -  Error: Unknown</p>');
					}
				},
				
				success: function(data) {
					
					var retReq = jQuery.parseJSON(data); 
					//$('#message').html(data);
					console.log(location.href = retReq.url);
					//alert(data);
					return;
					/* if(retReq.status == 'OK'){
						$('#message').html(data);
						$('#message').addClass('updated');
					}else{
						$('#message').html('<p>'+retReq.message+'</p>');
						$('#message').addClass('error');
					} */
					
				},
				type: 'POST'
			
			});
			console.log('done');
				
				
			return false;
		});
		
		
		
		
		$('.delete-task').on('click',function(e){
			e.preventDefault();
			var $row = $(this).closest("tr");
			var taskID = $(this).attr('taskID');
			
			if(confirm("Are your sure you want to delete this task?\n\nPlease note that all mines under this task will also be deleted.\nThis function cannot be undone")){
				$('#task-current').html('<i class="fa fa-spinner fa-pulse fa-fw"></i>Fetching All Company Full details');
			
			
				$.ajax({
					url: '<?php echo admin_url('admin-ajax.php');?>' ,
					data: {
						task_id: taskID,
						action: 'delete_task'
					},
					tryCount : 0,
					retryLimit : 5,
					timeout: 25000,
					error: function(x, t, m) {
						if(t==="timeout") {
							$('#task-info-error').html('<p>An error has occurred -  Timeout: Deleting Task</p>');
							
							this.tryCount++;
							if (this.tryCount <= this.retryLimit) {
								$('#task-current').html('<i class="fa fa-spinner fa-pulse fa-fw"></i>Retry: Deleting Task - '+ this.tryCount);
								//try again
								$.ajax(this);
								
								return;
							}else{
								$('#task-info-error').html('<p>Erro: Retry Max : '+ this.retryLimit+'</p>');
							}
							return;
						}
						
						if (xhr.status == 500) {
							$('#task-info-error').html('<p>An error has occurred -  Error: 500</p>');
						} else {
							$('#task-info-error').html('<p>An error has occurred -  Error: Unknown</p>');
						}
					},
					
					success: function(data) {
						
						var retReq = jQuery.parseJSON(data); 
						
						if(retReq.status == 'OK'){
							$('#message').html('<p>'+retReq.message+'</p>');
							$('#message').addClass('updated');
							
							
						}else{
							$('#message').html('<p>'+retReq.message+'</p>');
							$('#message').addClass('error');
						}
						$('td',$row).css('background-color','#FDbcbc');
						$($row).fadeOut('slow');
						
						return;
					},
					type: 'POST'
				
				});
				console.log('done');
				
			}
			return false;
		});
		
		$('.delete-opt').on('click',function(e){
			console.log('good');
		       tb_show("", "TB_inline?width=600&height=550&inlineId=modal-window-delete");
			  return false;
		});
	});
	
	</script>
<hr/>

<?php } ?>

<?php if(!empty($_GET['taskID']) && isset($_GET['taskID'])){ 
	global $wpdb;
	$taskInfo = $wpdb->get_row("SELECT * FROM ".GMINER_TBL_TASK." WHERE ID = ".$_GET['taskID'] );
?>

<a href="admin.php?page=google-miner-task" class="button-primary">Reset Task Filter</a>
<h1>
Keyword Task: <i>"<?php echo $taskInfo->keyw;?>"</i>
<? if(isset($_REQUEST['s'])){ ?>
	 <span class="subtitle">Search results for &#8220;<?php _admin_search_query(); ?>&#8221;</span>
	 
	 <a href="admin.php?page=google-miner-task&taskID=<?=$_GET['taskID'];?>&keyword=<?=$_GET['keyword'];?>" class="button">Reset search</a>
	<? } ?>
</h1>
<h2>Mines</h2>
	
	<?php
	$CompanyListTable = new companies_table();
	$CompanyListTable->prepare_items($_GET['keyword'],20); 
	?>
	<form method="get">
		<input type="hidden" name="taskID" value="<?=$_GET['taskID'];?>">
		<input type="hidden" name="keyword" value="<?=$_GET['keyword'];?>">
		<?php
			$CompanyListTable->search_box( 'Search', 'company_name' );
			//$CompanyListTable->views(); 
			$CompanyListTable->display(); 
		?>
	</form>
<? } ?>

<style type="text/css">
		.wp-list-table .column-ID{ width:  100px; }
		.wp-list-table .column-taskID{ width:  100px; }
		.wp-list-table .column-counts { width:  80px; }
		.wp-list-table .column-state,
		.wp-list-table .column-statecode,
		.wp-list-table .column-country,.wp-list-table .column-date_added { width: 160px; }
		.wp-list-table .column-contact_no{ width: 130px; }
		.wp-list-table .column-website { width: 280px; }
	</style>


