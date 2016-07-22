<div class="wrap">

	<h1>Create Task
		<a href="admin.php?page=google-miner-task" class="page-title-action">View Task List</a>
	</h1>
	
	

	<br>
	<br>
	
	<hr/>
	<form method="post" action="options.php">

	
		<style>
			.form-table td{padding:5px 0;}
			.form-table th{padding:0;}
			
		</style>
		<table class="form-table">
			<tr>
				<th scope="row"><label for="keyw">Keyword: </label></th>
				<td>
				<input type="text" name="keyw" id="keyw" value="" placeholder="Keyword(s)" class="regular-text" required>
				</td>
			</tr>
			
			<tr>
				<th scope="row"><label for="blogname">Country</label></th>
				<td>
					<select name="country" id="country" class="regular-text" required>
						<?

						global $_countries;
						include_once(GMINER_PATH_DIR. 'assets/countries.php');
						
						
						foreach($_countries as $_country => $_data){
							
							echo '<option value="'.$_country.'">'.$_data.'</option>'."\n";
						}
						?>
					</select>
				</td>
			</tr>
			
			<tr>
				<th scope="row"><label for="keyw">State </label></th>
				<td>
					<input type="text" name="state" id="state" value="" placeholder="State" class="regular-text" required>
				</td>
			</tr>
			
			<tr>
				<th scope="row"> </th>
				<td>
					<input type="text" name="task_id" id="task_id" value="0" ><br/>
					<input type="text" name="next_token" id="next_token" value="" ><br/>
					<input type="button" name="search_now" id="search_now" value="Run Task Now!" class="button button-primary">
				</td>
			</tr>
			
		</table>
		
	
	
	

		
		
		
		
	</form>
	
	
	
	<br/>
	<br/>
	<br/>
	<div>
		<div class="hiddenx"><br/>
		<i>NOTE: Click this button if encounter "TIMEOUT ERROR"</i><br/>
			<input type="button" name="fetch_company" id="fetch_company" value="Fetch Company Details" class="button">
		</div>
	</div>
	
	<hr/>
	
	
	<br/>

	
	<h3>Reports Logs:</h3>
	<div id="task-current" class="update-nag">
		Current Task...
	</div>
	<div id="task-info" class="updated">
		<p>Task Logs...
	</div>
	<div id="task-info-error" class="error" style="display:none;">
		<p>Task Error Log....
	</div>




<script>
	jQuery(document).ready(function($) {
		var CurrentPage = 1;
		
		
		$('#search_now').on('click',function() {
			
			if( $('#task_id').val() == '') CurrentPage = 1;
			
			
			if( $('#keyw').val() == '') return false;
			
			
			
				var pageID = CurrentPage;
				$.ajax({
					url: '<?php echo admin_url('admin-ajax.php');?>' ,
					data: {
						keywords: $('#keyw').val(),
						state: $('#state').val(),
						task_id: $('#task_id').val(),
						country: $('#country').val(),
						nextpage_token: $('#next_token').val(),
						action: 'search_company_reff'
					},
					beforeSend: function( xhr ) {
						$('#task-current').html('<i class="fa fa-spinner fa-pulse fa-fw"></i>Fetching Request');
					},
					error: function() {
						$('#task-info-error').html('<p>An error has occurred</p>');
						$('#task-info-error').css('display','block');
					},
					
					//dataType: 'jsonp',
					success: function(data) {
						/* var $title = $('<h1>').text(data.talks[0].talk_title);
						var $description = $('<p>').text(data.talks[0].talk_description);
						$('#info')
						.append($title)
						.append($description); */
						
						
						var retReq = jQuery.parseJSON(data); 
						
						
						if(retReq.status == 'OK'){
							$('#task-current').html('<i class="fa fa-check"></i> Status : <b>OK</b>');	
							$('#task-info').append('<br/><i class="fa fa-check"></i> Page <b>('+pageID+')</b> Complete - Status : <b>OK</b>');
							
						}else if(retReq.status == 'OVER_QUERY_LIMIT'){
							$('#task-current').html('<i class="fa fa-check"></i> Status : <b>OVER LIMIT</b>');	
							$('#task-info').append('<br/><i class="fa fa-cross"></i> Stop @ Page <b>('+pageID+')</b> Overlimit Request');
						}
						else if(retReq.status == 'REQUEST_DENIED'){
							$('#task-current').html('<i class="fa fa-check"></i> Status : <b>REQUEST DENIED</b>');	
							$('#task-info').append('<br/><i class="fa fa-cross"></i> Stop @ Page <b>('+pageID+')</b> Request Denied - Please Check your API Key creadentials.');
						}else if(retReq.status == 'INVALID_REQUEST'){
							$('#task-current').html('<i class="fa fa-check"></i> Status : <b>INVALID REQUEST</b>');	
							$('#task-info').append('<br/><i class="fa fa-cross"></i> Stop @ Page <b>('+pageID+')</b> Invalid Request - Next Page token issue');
						}
						
						console.log('next Token: '+retReq.next );
						if( retReq.next != null && retReq.next != ''){
							$('#task-current').html('<i class="fa fa-spinner fa-pulse fa-fw"></i> Requesting Next Page (' + (pageID+1) +')');
							
							//if(pageID >= 3){
							$('#task-current').append('<br/><i class="fa fa-check"></i> COMPLETE : Details of Page <b>'+pageID+'</b>');	
							
							//console.log(retReq.companyID.new.length);
							//console.log(retReq.companyID.update);
							//console.log(retReq.companyID.update.length);
							$('#task-info').append('<br/><i class="fa fa-check"></i>Page <b>'+pageID+' </b> - <b>'+retReq.counts+' Records Affected</b> ('+retReq.companyID.new.length+' New /'+retReq.companyID.update.length+' Update)');	
								//return;
							//}
							CurrentPage = pageID +1;
							//set token_get_all
							
							//console.log(data);
							
							//console.log(ccs);
							$('#next_token').val(retReq.next);
							$('#task_id').val(retReq.taskID);
							console.log('ready for next request');
							setTimeout(function(){
								console.log('1 minute delay..good');
								$('#task-current').html('<i class="fa fa-spinner fa-pulse fa-fw"></i> 1 minute delay..good');	
								setTimeout(function(){
									$('#task-current').html('<i class="fa fa-spinner fa-pulse fa-fw"></i>Searching again...');	
									console.log('Searching again for next page under Task ID '+retReq.taskID);
									//setTimeout(function(){
									//	console.log('3 good');
										//setTimeout(function(){
										//	console.log('4 good');
											//setTimeout(function(){
											//	console.log('6 good');
												$( "#search_now" ).trigger( "click" );
											//}, 60000);
										//}, 60000);
									//}, 60000);
								}, 60000);
							}, 60000);
							
						}else if(retReq.next == null || retReq.next == ''){
							console.log('Getting All company Info');
							$('#task-current').html('<i class="fa fa-check"></i>DONE');
							setTimeout(function(){
								$( "#fetch_company" ).trigger( "click" );
							}, 5000);
							
							return;
						}
						
					},
					type: 'POST'
				});
			console.log('done');
		});
		
		
		
		
		
		$('#fetch_company').on('click',function() {
			
			$('#task-current').html('<i class="fa fa-spinner fa-pulse fa-fw"></i>Fetching All Company Full details');
			$.ajax({
				url: '<?php echo admin_url('admin-ajax.php');?>' ,
				data: {
					task_id: $('#task_id').val(),
					action: 'cleandb_reffKeys'
				},
				tryCount : 0,
				retryLimit : 5,
				timeout: 20000,
				error: function(x, t, m) {
					if(t==="timeout") {
						$('#task-info-error').html('<p>An error has occurred -  Timeout: Fetching All Company Full details...Retrying..('+ this.tryCount +') </p>');
						$('#task-info-error').css('display','block');
						
						this.tryCount++;
						if (this.tryCount <= this.retryLimit) {
							$('#task-current').html('<i class="fa fa-spinner fa-pulse fa-fw"></i>Retry: Fetching All Company Full details - '+ this.tryCount);
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
						$('#task-info-error').css('display','block');
					} else {
						$('#task-info-error').html('<p>An error has occurred -  Error: Unknown</p>');
						$('#task-info-error').css('display','block');
					}
				},
				
				success: function(data) {
					
					var retReq = jQuery.parseJSON(data); 
					
					if(retReq.status == 'OK'){
						$('#task-current').html('<i class="fa fa-check"></i> Status : <b>OK</b>');	
						$('#task-info').append('<br/><i class="fa fa-check"></i> Page <b>('+pageID+')</b> Complete - Status : <b>OK</b>');
						
					}else if(retReq.status == 'OVER_QUERY_LIMIT'){
						$('#task-current').html('<i class="fa fa-check"></i> Status : <b>OVER LIMIT</b>');	
						$('#task-info').append('<br/><i class="fa fa-cross"></i> Stop @ Page <b>('+pageID+')</b> Overlimit Request');
					}
					else if(retReq.status == 'REQUEST_DENIED'){
						$('#task-current').html('<i class="fa fa-check"></i> Status : <b>REQUEST DENIED</b>');	
						$('#task-info').append('<br/><i class="fa fa-cross"></i> Stop @ Page <b>('+pageID+')</b> Request Denied - Please Check your API Key creadentials.');
					}else if(retReq.status == 'INVALID_REQUEST'){
						$('#task-current').html('<i class="fa fa-check"></i> Status : <b>INVALID REQUEST</b>');	
						$('#task-info').append('<br/><i class="fa fa-cross"></i> Stop @ Page <b>('+pageID+')</b> Invalid Request - Next Page token issue');
					}else if(retReq.status == 'DONE'){
						$('#task-current').html('<i class="fa fa-check"></i> Status : <b>COMPLETED</b>');	
						$('#task-info').append('<br/><i class="fa fa-check"></i> <b>'+ retReq.total +'</b> Entries had been addedupdate with full details');
					}
					
					$('#task-current').html('<i class="fa fa-check"></i>DONE');
					return;
				},
				type: 'POST'
			
			});
			console.log('done');
		});
		
		
		
	});
	
	
	
</script>
<?php


if(isset($_GET['run'])){
	company::search_save('web development', 'dallas', 'us');


}


//echo "https://maps.googleapis.com/maps/api/place/details/xml?reference=&key=". GMINER_API_SECRET;


//https://maps.googleapis.com/maps/api/place/details/json?reference=CmRYAAAAciqGsTRX1mXRvuXSH2ErwW-jCINE1aLiwP64MCWDN5vkXvXoQGPKldMfmdGyqWSpm7BEYCgDm-iv7Kc2PF7QA7brMAwBbAcqMr5i1f4PwTpaovIZjysCEZTry8Ez30wpEhCNCXpynextCld2EBsDkRKsGhSLayuRyFsex6JA6NPh9dyupoTH3g&key='.GMINER_API_SECRET
	/* $_url_req = "https://maps.googleapis.com/maps/api/place/textsearch/json?query=".$q."&key=". GMINER_API_SECRET;
	$result = wp_remote_get($_url_req, true);
	$decode = json_decode($result['body'], true);
	$results = $decode['results'];
	$status = $decode['status'];

	
	https://maps.googleapis.com/maps/api/place/textsearch/json?query=Web+Development+in+California,+US&key=AIzaSyAXrE0S5VI2HixjIzs3NQuXwOuvLzBUNzQ

	if($status != 'OK'){
		echo 'Error Status: '.$status;
		exit;
	} */
	//print_r(	$_url_req);	
	echo '<pre>';
/* 
	$_max = 5;
	$x=0;
	foreach ($results as $_result) {
		echo $_result['name'].'<br/>';
		echo $_result['reference'];
		echo "<br/>";
		if(company::is_exist($_result['reference']) == false){
			
			//company->add($_result['reference'])
			echo 'Add---';
		}else{
			echo 'minus---';
		}
		
		if($x>= $_max){
			break;
		}
		$x++;
	} */
 

