<?php 
	if($_POST['auto_location_hidden'] == 'Y') {
		//Form data sent  
        $auto_location_maps_key = $_POST['auto_location_maps_key'];  
        update_option('auto_location_maps_key', $auto_location_maps_key);  

        $auto_location_limit_geography = $_POST['auto_location_limit_geography'];  
        update_option('auto_location_limit_geography', $auto_location_limit_geography);  

        $auto_location_limit_type = $_POST['auto_location_limit_type'];  
        update_option('auto_location_limit_type', $auto_location_limit_type);  
          
        $auto_location_countrycode = $_POST['auto_location_countrycode'];  
        update_option('auto_location_countrycode', $auto_location_countrycode);  
  
        ?>  
        <div class="updated"><p><strong><?php _e('Options saved.' ); ?></strong></p></div>  
       	<?php
	} else {
		//Normal page display
		$auto_location_maps_key = get_option('auto_location_maps_key'); 
        $auto_location_countrycode = get_option('auto_location_countrycode');  
	}
?>
	


<div class="wrap">
	<?php    echo "<h2>" . __( 'Auto Location Settings', 'auto_location' ) . "</h2>"; ?>
	
	<form method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
		<input type="hidden" name="auto_location_hidden" value="Y">
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row"><label for="auto_location_maps_key"><?php _e("Google Maps API Key: " ); ?></label></th>
					<td><input type="text" name="auto_location_maps_key" value="<?php echo $auto_location_maps_key; ?>" ><p class="description"><?php _e("Only required if Google API requests are likely to exceed 25 000 requests per day. Get one from <a href='https://code.google.com/apis/console' target='_blank'>Google Maps</a> now by following the steps below." ); ?></p></td>
				</tr>

				<tr valign="top">
					<th scope="row"><label for="auto_location_limit_geography"><?php _e("Limit by Geography: " ); ?></label></th>
					<td><select name="auto_location_limit_geography">
							<?php if (get_option('auto_location_limit_geography') == 'Yes') { ?>
								<option value="Yes" selected >Yes</option>
								<option value="No" >No</option>
							<?php } else { ?>
								<option value="Yes" >Yes</option>
								<option value="No" selected >No</option>
							<?php } ?>
						</select><p class="description"><?php _e("Setting this to no, will result in suggestions from all over th world. Ideal if you've got an international job board." ); ?></p></td>
				</tr>

				<tr valign="top">
					<th scope="row"><label for="auto_location_limit_type"><?php _e("Limit by Type: " ); ?></label></th>
					<td><select name="auto_location_limit_type">
							<?php if (get_option('auto_location_limit_type') == '(cities)') { ?>
								<option value="(cities)" selected >Cities</option>
								<option value="(regions)" >Regions</option>
							<?php } else { ?>
								<option value="(cities)" >Cities</option>
								<option value="(regions)" selected >Regions</option>
							<?php } ?>
						</select><p class="description"><?php _e("Limit suggestions to either regions or specific cities." ); ?></p></td>
				</tr>

				<tr valign="top">
					<th scope="row"><label for="auto_location_countrycode"><?php _e("Country Code: " ); ?></th>
					<td><input type="text" name="auto_location_countrycode" value="<?php echo $auto_location_countrycode; ?>" size="2"><p class="description"><?php _e(" Use a country code to limit suggestions to a specific country (e.g. FR for France). A full list of country codes is <a href='http://www.iso.org/iso/country_names_and_code_elements' target='_blank'>available here</a>." ); ?></p></td>
			</tbody>
		</table>

		<p class="submit">
		<input class="button-primary" type="submit" name="Submit" value="<?php _e('Save Options', 'auto_location' ) ?>" />
		</p>

		<hr />

		<h3>How to get your API Key</h3>
		<p>API keys are managed through the Google APIs console. Follow these step to activate your Google API:</p>
		<ol>
		<li>Visit the APIs console at <a href="https://cloud.google.com/console/project" target="_blank">https://cloud.google.com/console/project</a> and log in with your Google Account.</li>
		<li>Create a new project by click on the "Create Project" button.</li>
		<li>Once create, click on the API's &amp; Auth link in the left-hand menu.</li>
		<li>A large list of Google Services will be listed. Scroll down until your find Google Maps API v3 and click to toggle the button to "On".</li>
		<li>Click on the "Credentials" link in the left-hands menu.</li>
		<li>Under the "Public API Access", click on the "Create New Key" button.</li>
		<li>When Prompted, click on the Browser Key button.</li>
		<li>Once complete, copy the newly created API key into the field above.</li>
		</ol>
	</form>
</div>


