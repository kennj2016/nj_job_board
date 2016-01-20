<?php 
/*
Plugin Name: WP Job Manager - Auto Location
Version: 1.4
Plugin URI: http://tinygiantstudios.co.uk/product/auto-location/
Author: Tiny Giant Studios
Author URI: http://www.tinygiantstudios.co.za
Description: Display a list of locations in Wp Job Manageras the user types.
*/


/*
 * Create Admin Menu
 * 
 */
function auto_location_admin_menu() {
	add_options_page('Auto Location', 'Auto Location', 'manage_options', 'your-unique-identifier', 'auto_location_options');
}



/*
 * Create admin menu content / forms
 *
 */
function auto_location_options() {
	include('auto-location-admin.php');
}



/* 
 * Add Google Maps Autocomplete code to footer
 *
 */
function auto_location_init() {
?>

<script>
jQuery.noConflict();
jQuery( document ).ready(function() {
  

	function initialize() {
		var autocompleteOptions = {
				types: [ '<?php echo get_option('auto_location_limit_type'); ?>']<?php if (!(get_option('auto_location_countrycode') == '')) { ?>,
				componentRestrictions: {country: '<?php echo get_option('auto_location_countrycode'); ?>'},
				<?php } ?>
		};


		/* Used on main search page */
		if (jQuery('#search_location').length) {
	  		var normal_input = jQuery('#search_location')[0];

	  		<?php if (get_option('auto_location_limit_geography') == 'Yes') { ?>
	  			var autocomplete = new google.maps.places.Autocomplete(normal_input, autocompleteOptions);
	  		<?php } else { ?>
	  			var autocomplete = new google.maps.places.Autocomplete(normal_input);
	  		<?php } ?>
	  	} 


	  	/* Used by Jobify's "Map + Listings" page template */
		if (jQuery('.job_listings #search_location').length) {
	  		var jobify_input = jQuery('.job_listings #search_location')[0];

	  		<?php if (get_option('auto_location_limit_geography') == 'Yes') { ?>
	  			var autocomplete = new google.maps.places.Autocomplete(jobify_input, autocompleteOptions);
	  		<?php } else { ?>
	  			var autocomplete = new google.maps.places.Autocomplete(jobify_input);
	  		<?php } ?>
	  	} 


	  	/* Used by Jobify's "Job Search" Widget */
	  	if (jQuery('.jobify_widget_jobs_search #search_location').length) {
	  		var jobify_input = jQuery('.jobify_widget_jobs_search #search_location')[0];

	  		<?php if (get_option('auto_location_limit_geography') == 'Yes') { ?>
	  			var autocomplete = new google.maps.places.Autocomplete(jobify_input, autocompleteOptions);
	  		<?php } else { ?>
	  			var autocomplete = new google.maps.places.Autocomplete(jobify_input);
	  		<?php } ?>
	  	} 
		
		/* Used by Jobify's "Map + Resumes" page template */
		if (jQuery('.resumes #search_location').length) {
	  		var jobify_input = jQuery('.resumes #search_location')[0];

	  		<?php if (get_option('auto_location_limit_geography') == 'Yes') { ?>
	  			var autocomplete = new google.maps.places.Autocomplete(jobify_input, autocompleteOptions);
	  		<?php } else { ?>
	  			var autocomplete = new google.maps.places.Autocomplete(jobify_input);
	  		<?php } ?>
	  	} 

	  	/* Used on job submission page */
	  	if (jQuery('#job_location').length) {
	  		var input = document.getElementById('job_location');

	  		<?php if (get_option('auto_location_limit_geography') == 'Yes') { ?>
	  			var autocomplete = new google.maps.places.Autocomplete(input, autocompleteOptions);
	  		<?php } else { ?>
	  			var autocomplete = new google.maps.places.Autocomplete(input);
	  		<?php } ?>
	  	}
		
		/* Used on resume submission page */
	  	if (jQuery('#candidate_location').length) {
	  		var input = document.getElementById('candidate_location');

	  		<?php if (get_option('auto_location_limit_geography') == 'Yes') { ?>
	  			var autocomplete = new google.maps.places.Autocomplete(input, autocompleteOptions);
	  		<?php } else { ?>
	  			var autocomplete = new google.maps.places.Autocomplete(input);
	  		<?php } ?>
	  	}
		
	}

	google.maps.event.addDomListener(window, 'load', initialize); 
});	
</script>



<?php
}



/* 
 * Embed new scripts / styles
 * 
 */
function auto_location_enqueue() {

	wp_register_script( 'google-maps', ( is_ssl() ? 'https' : 'http' ) . '://maps.googleapis.com/maps/api/js?key=' . get_option('auto_location_maps_key') .'&sensor=false&libraries=places', array('jquery'), '1.0', false );
	wp_enqueue_script( 'google-maps' );
	

	wp_register_style('auto_location_styles', plugins_url() . '/auto-location-pro/css/auto-location-styles.css' );
	wp_enqueue_style( 'auto_location_styles' );

	wp_register_script('auto_location_styles', plugins_url() . '/auto-location-pro/js/auto-location-styles.js', array(), '1.0', true);
	wp_enqueue_script( 'auto_location_styles');

}


add_action('wp_enqueue_scripts', 'auto_location_enqueue'); 
add_action('wp_head', 'auto_location_init');

add_action('admin_menu', 'auto_location_admin_menu');

?>