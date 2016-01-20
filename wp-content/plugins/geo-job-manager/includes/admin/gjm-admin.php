<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * GJM_Admin class
 */
class GJM_Admin {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		$this->settings = get_option( 'gjm_options' );
		
		add_action( 'admin_print_scripts-post-new.php', array( $this, 'admin_autocomplete'   ), 11 );
		add_action( 'admin_print_scripts-post.php', 	array( $this, 'admin_autocomplete'   ), 11 );
		add_action( 'save_post' , 						array( $this, 'update_job_location'  )     );	
		add_action( 'pmxi_saved_post', 					array( $this, 'pmxi_location_update' ), 20 );	
	}
			
	/**
	 * Trigger autocomplete on the location field in backend
	 */
	function admin_autocomplete() {
		global $post_type;

		if ( $post_type != 'job_listing' || empty( $this->settings['admin']['autocomplete'] ) ) 
			return;

		$autocomplete_options = array(
				'input_field'   => '_job_location',
				'options' 		=> $this->settings['autocomplete_options']
		);
		
		wp_enqueue_script( 'gjm-autocomplete');
		wp_localize_script( 'gjm-autocomplete', 'AutoCompOptions', $autocomplete_options );
		wp_enqueue_style( 'gjm-style');
	}
	
	/**
	 * Add location data to GEo my WP table in database
	 * @param unknown_type $post_id
	 */
	public function add_location_to_db( $post_id ) {
		
		global $wpdb;
		
		$street_number = stripslashes( get_post_meta( $post_id, 'geolocation_street_number', true ) );
		$street_name   = stripslashes( get_post_meta( $post_id, 'geolocation_street', true ) );	
		$street_check  = trim( $street_number.' '.$street_name );
		$street		   = ( !empty( $street_check ) ) ? $street_number.' '.$street_name : '';

		$wpdb->replace( $wpdb->prefix . 'places_locator',
				array(
						'post_id'			=> $post_id,
						'feature'  			=> 0,
						'post_type' 		=> $_POST['post_type'],
						'post_title'		=> $_POST['post_title'],
						'post_status'		=> $_POST['post_status'],
						'street_number' 	=> $street_number,
						'street_name' 		=> $street_name,
						'street' 			=> $street,
						'apt' 				=> '',
						'city' 				=> stripslashes( get_post_meta( $post_id, 'geolocation_city', true ) ),
						'state' 			=> stripslashes( get_post_meta( $post_id, 'geolocation_state_short', true ) ),
						'state_long' 		=> stripslashes( get_post_meta( $post_id, 'geolocation_state_long', true ) ),
						'zipcode' 			=> stripslashes( get_post_meta( $post_id, 'geolocation_postcode', true ) ),
						'country' 			=> stripslashes( get_post_meta( $post_id, 'geolocation_country_short', true ) ),
						'country_long' 		=> stripslashes( get_post_meta( $post_id, 'geolocation_country_long', true ) ),
						'address' 			=> stripslashes( $_POST['_job_location'] ),
						'formatted_address' => stripslashes( get_post_meta( $post_id, 'geolocation_formatted_address', true ) ),
						'phone' 			=> '',
						'fax' 				=> '',
						'email' 			=> $_POST['_application'],
						'website' 			=> $_POST['_company_website'],
						'lat' 				=> get_post_meta( $post_id, 'geolocation_lat', true ),
						'long' 				=> get_post_meta( $post_id, 'geolocation_long', true ),
						'map_icon'  		=> '_default.png',
				)
		);
	}

	/**
	 * Update location data when importing using WP ALL IMPORT
	 * @param unknown_type $post_id
	 */
	public function pmxi_location_update( $post_id ) {
		if ( 'job_listing' === get_post_type( $post_id ) ) {
			$geolocated = get_post_meta( $post_id, 'geolocated', true );
			if ( isset( $geolocated ) && $geolocated == 1 ) {	
				self::add_location_to_db( $post_id );					
				return;
			}
		}
	}
	
	/**
	 * Update database with locaiton fields
	 * @param unknown_type $post_id
	 */
	function update_job_location($post_id) {
		global $post;
	
		if (  !isset( $_POST['post_type']) || $_POST['post_type'] != 'job_listing' )
			return;
		
		// verify nonce //
		if ( empty( $_POST['job_manager_nonce'] ) || ! wp_verify_nonce( $_POST['job_manager_nonce'], 'save_meta_data' ) )
			return;
	
		// Return if it's a post revision
		if ( false !== wp_is_post_revision( $post_id ) )
			return;

		// check autosave //
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) )
			return;

		$geolocated = get_post_meta( $post_id, 'geolocated', true );
		
		//delete location if address field empty
		if ( empty( $_POST['_job_location'] ) || !isset( $geolocated ) || $geolocated != 1  ) {
			global $wpdb;
			$wpdb->query( $wpdb->prepare( "DELETE FROM " . $wpdb->prefix . "places_locator WHERE post_id=%d", $post->ID ) );
			return;
		
		} else {

			self::add_location_to_db( $post_id );
			return;
		} 	
	}	
}
new GJM_Admin;