<div class="wrap">

	<h1>Miner Settings</h1>



<?

if(isset($_POST['submit_btn'])){

	echo '<div id="message" class="updated"><p>Update Successfully saved.</p></div>';

	update_option('gminer_api_key_private', $_POST['api_key_private']);
	update_option('gminer_api_key_private_1', $_POST['api_key_private_1']);
	update_option('gminer_api_key_private_2', $_POST['api_key_private_2']);
	update_option('gminer_api_key_private_3', $_POST['api_key_private_3']);
	
	
}


$_api_key_private = get_option('gminer_api_key_private','',true);
$_api_key_private_1 = get_option('gminer_api_key_private_1','',true);
$_api_key_private_2 = get_option('gminer_api_key_private_2','',true);
$_api_key_private_3 = get_option('gminer_api_key_private_3','',true);
?>

<form method="post" novalidate="novalidate">

	<table class="form-table">
		<tr>
			<th scope="row"><label for="blogname">API Browser Key</label></th>
			<td><input name="api_key_private" type="text" id="api_key_private" value="<?php echo $_api_key_private;?>" class="regular-text" /></td>
		</tr>
		
		<tr>
			<th scope="row"><label for="blogname">API Browser Key(backup)</label></th>
			<td><input name="api_key_private_1" type="text" id="api_key_private_1" value="<?php echo $_api_key_private_1;?>" class="regular-text" /></td>
		</tr>
		<tr>
			<th scope="row"><label for="blogname">API Browser Key(backup 2)</label></th>
			<td><input name="api_key_private_2" type="text" id="api_key_private_2" value="<?php echo $_api_key_private_2;?>" class="regular-text" /></td>
		</tr>
		<tr>
			<th scope="row"><label for="blogname">API Browser Key(backup 3)</label></th>
			<td><input name="api_key_private_3" type="text" id="api_key_private_3" value="<?php echo $_api_key_private_3;?>" class="regular-text" /></td>
		</tr>
		
		<tr>
			<th scope="row"> </th>
			<td><input type="submit" name="submit_btn" id="submit_btn" value="Save Changes" class="button-primary"></td>
		</tr>
		
	</table>
</form>
	
<pre>
<a href="https://console.developers.google.com/" target="_blank">https://console.developers.google.com/</a> - Create Project (top right of page)

Activate the Google Place API and Google Map API

<a href="https://developers.google.com/places/web-service/get-api-key" target="_blank">https://developers.google.com/places/web-service/get-api-key</a>
generate server key , generate browser key
copy the browser key .. thats all what we need here

Purchase Key for more Quota
https://developers.google.com/maps/pricing-and-plans/

please use this key "AIzaSyD2sT2Udch-pxB4-MBCfeX-LdOW694WhxE" for testing..


</pre>

