<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * GJM_Admin class
 */
class GJM_Admin_Settings {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		$this->prefix = 'gjm';
		
		add_action( 'admin_init', 			array( $this, 'default_options' ) );
		add_filter( 'job_manager_settings', array( $this, 'admin_settings' ) );
		
		if ( !empty( $_POST ) && isset( $_POST['option_page' ]) && $_POST['action'] == 'update' && $_POST['option_page'] == 'job_manager' ) {
			
			$this->settings = get_option( 'gjm_options' );	
			$this->prefix 	= 'gjm';
		
			add_action( 'admin_init', array( $this, 'options_validate' ), 10 );	
		}	
		
		add_action( 'wp_job_manager_admin_field_gjm_locations_importer', array( $this, 'locations_importer_form' ) );
		add_action( 'admin_init', array( $this, 'locations_importer' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
	}

	/**
	 * Display admin notices
	 */
	function admin_notices() {
		
		//check if notice exist
		if ( empty( $_GET['gjm_notice'] ) )
			return;
		
		$notice 	= $_GET['gjm_notice'];
		$status 	= $_GET['notice_status'];
		$locations  = ( !empty( $_GET['number_of_locations'] ) ) ? $_GET['number_of_locations'] : 0;
		
		$messages 						    = array();
		$messages['no_locations_to_import'] = __( 'No locations to import were found.', 'GJM' );
		$messages['no_locations_imported'] 	= __( 'No locations were imported.', 'GJM' );
		$messages['locations_imported'] 	= sprintf( __( '%d Locations successfully imported.', 'GJM' ), $locations );
							
		?>
	    <div class="<?php echo $status;?>" style="display:block;margin-top:10px;">
	    	<p><?php echo $messages[$notice]; ?></p>
	    </div>
		<?php
	}
	
	/**
	 * Locations importer form
	 * 
	 */
	function locations_importer_form( $option ) {
	?>	
		<input type="hidden" name="locations_import_type" value="gjm" />
		<?php wp_nonce_field( 'gjm_locations_import_nonce', 'gjm_locations_import_nonce' ); ?>
		<button type="submit" id="gjm-locations-import-submit" name="gjm_import_action" value="locations_import" class="button-secondary"><?php _e( "Import", "GJM" ); ?></button> 
		<p class="description"><?php echo $option['desc']; ?></p>
	<?php 				
	}
	
	/**
	 * Location importer
	 * 
	 */
	function locations_importer() {
		
		if ( empty( $_POST['gjm_import_action'] ) || $_POST['gjm_import_action'] != 'locations_import' ) 
			return;
	
		$this->prefix = $_POST['locations_import_type'];
		
		//look for nonce
		if ( empty( $_POST[$this->prefix.'_locations_import_nonce'] ) )
			return;
		
		//varify nonce
		if ( !wp_verify_nonce( $_POST[$this->prefix.'_locations_import_nonce'], $this->prefix.'_locations_import_nonce' ) )
			return;
		
		global $wpdb;
		
		header( 'Content-Type: text/csv; charset=utf-8' );
		
		//get prefix
		if ( $this->prefix == 'gjm' ) {
			$post_type = 'job_listing';
			$page_name = 'job-manager-settings';
		} else {
			$post_type = 'resume';
			$page_name = 'resume-manager-settings';
		}
		
		//get all posts with posts types jobs or resume
		$posts = $wpdb->get_results("
				SELECT `ID` as 'post_id', `post_title`, `post_type`, `post_status`
				FROM `{$wpdb->prefix}posts`
				WHERE `post_type` = '{$post_type}'
				", ARRAY_A );
		
		//abort if no posts found
		if ( empty( $posts ) ) {
			wp_safe_redirect( admin_url( 'edit.php?post_type='.$post_type.'&page='.$page_name.'&gjm_notice=no_locations_to_import&notice_status=error' ) );
			exit;
		}
		
		//number of locations imported
		$imported = 0;
		
		//loop found posts
		foreach ( $posts as $post_key => $post_info ) {
			
			//check that location does not already exist in gmw table database
			$check_location = $wpdb->get_col( "SELECT `post_id` FROM `{$wpdb->prefix}places_locator` WHERE `post_id` = {$post_info['post_id']}", 0 );
				
			//skip location if exist in gmw table
			if ( !empty( $check_location ) )
				continue;
							
			//get all jobs or resume custom fields with location from database
			$post_location = $wpdb->get_results("
					SELECT `meta_key`, `meta_value`
					FROM `{$wpdb->prefix}postmeta`
					WHERE `post_id` = {$post_info['post_id']}
					AND `meta_key` IN ( 'geolocation_street', 'geolocation_city', 'geolocation_state_short', 'geolocation_state_long', 'geolocation_postcode',
					'geolocation_country_short', 'geolocation_country_long', 'geolocation_lat', 'geolocation_long', 'geolocation_formatted_address')
			", ARRAY_A );

			//loop custom fields and add them to array of posts
			foreach ( $post_location as $location_key => $location ) {			
				$posts[$post_key][$location['meta_key']] =  $location['meta_value'];
			}

			if ( empty( $posts[$post_key]['geolocation_lat'] ) || empty( $posts[$post_key]['geolocation_long'] ) )
				continue;
			
			//create entry in gmw table in database
			$created = $wpdb->query( $wpdb->prepare(
					"INSERT INTO `{$wpdb->prefix}places_locator`
					( `post_id`, `feature`, `post_status`, `post_type`, `post_title`, `lat`,
					`long`, `street`, `apt`, `city`, `state`, `state_long`, `zipcode`, `country`,
					`country_long`, `address`, `formatted_address`, `phone`, `fax`, `email`, `website`, `map_icon` )
					VALUES ( %d, %d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s )",
					array(
							$posts[$post_key]['post_id'],
							0,
							$posts[$post_key]['post_status'],
							$post_type,
							$posts[$post_key]['post_title'],
							$posts[$post_key]['geolocation_lat'],
							$posts[$post_key]['geolocation_long'],
							$posts[$post_key]['geolocation_street'],
							'',
							$posts[$post_key]['geolocation_city'],
							$posts[$post_key]['geolocation_state_short'],
							$posts[$post_key]['geolocation_state_long'],
							$posts[$post_key]['geolocation_postcode'],
							$posts[$post_key]['geolocation_country_short'],
							$posts[$post_key]['geolocation_country_long'],
							$posts[$post_key]['geolocation_formatted_address'],
							$posts[$post_key]['geolocation_formatted_address'],
							'',
							'',
							'',
							'',
							'default.png'
					) ));
	
			if ( $created )
				$imported++;		
		}
					
		if ( $imported == 0 ) {
			wp_safe_redirect( admin_url( 'edit.php?post_type='.$post_type.'&page='.$page_name.'&gjm_notice=no_locations_imported&notice_status=error' ) );
		} else {
			wp_safe_redirect( admin_url( 'edit.php?post_type='.$post_type.'&page='.$page_name.'&gjm_notice=locations_imported&number_of_locations='.$imported.'&notice_status=updated' ) );
		}
		exit;
	}
	
	/**
	 * Set the default options of the plugin if not exist
	 */
	public function default_options() {
			
		$plugins 				= array();
		$plugins['gjm_options'] = 'gjm';
		
		$plugins = apply_filters( 'gjm_plugins_default_options', $plugins );
		
		foreach ( $plugins as $plugin => $prefix ) {
			
			//check if not exists
			if ( false === get_option( $plugin ) ) {
				//default options
				$options = array(
						'admin'	        		=> array( 'autocomplete' => 1 ),
						'fronend_form'			=> array( 'autocomplete' => 1 ),
						'autocomplete_options' 	=> array( 'country' => '' ),
						'search_page'  			=> array(
								$prefix.'_autocomplete' 	=> 0,
								$prefix.'_radius' 			=> '10,15,25,50,100',
								$prefix.'_units' 			=> 'both',
								$prefix.'_locator_use' 		=> '0',
								$prefix.'_locator_title' 	=> 'Get your current location',
								$prefix.'_orderby' 			=> 'distance,title,featured,date',
								$prefix.'_map' 				=> 1,
								$prefix.'_map_width' 		=> '100%',
								$prefix.'_map_height' 		=> '250px',
								$prefix.'_scroll' 			=> 0,
								$prefix.'_distance' 		=> 1,
								$prefix.'_map_type' 		=> 'ROADMAP',
								$prefix.'_scroll_wheel' 	=> 1
						),
						'single_page'  			=> array(
								$prefix.'_map' 				=> 1,
								$prefix.'_map_width' 		=> '100%',
								$prefix.'_map_height' 		=> '200px',
								$prefix.'_map_type' 		=> 'ROADMAP',
								$prefix.'_scroll_wheel' 	=> 1
						),
				);
	
				//update default options
				update_option( $plugin, $options );
			}
		}
	}

	/**
	 * add geo Job manager settings tab to WP Job Manager settings page
	 */
	public function admin_settings( $args ) {

		$prefix  = $this->prefix;
		$sc_px   = ( $this->prefix == 'gjm') ? __( 'jobs', 'GJM' ) : __( 'resumes', 'GJM' );
		$sc_px_s = ( $this->prefix == 'gjm') ? 'GEO Job Manager'  : 'Resume Manager Geolocation';

		$args['gjm_autocomplete'] = array(
				__( 'GEO General Settings', 'GJM' ),
				array(
						$prefix.'_import_locations' => array(
								'name'        => $prefix.'_import_locations',
								'std'         => '',
								'label'       => __( 'Import Locations', 'GJM' ),
								'cb_label'    => __( 'Yes', 'GJM' ),
								'desc'        => sprintf( __( "Import %s location to %s database. You should do if you have created %s previously the installation of %s plugin.", 'GJM' ), $sc_px, $sc_px_s, $sc_px, $sc_px_s ),    
								'type'        => $prefix.'_locations_importer',
								'attributes'  => array()
						),
						array(
								'name'        => $prefix.'_autocomplete_admin',
								'std'         => '1',
								'label'       => __( 'Google address autocomplete (admin)', 'GJM' ),
								'cb_label'    => __( 'Yes', 'GJM' ),
								'desc'        => sprintf( __( 'Disply suggested results by Google when typing an address in the location field of the new/edit %s screen', 'GJM' ), $sc_px_s ),
								'type'        => 'checkbox',
								'attributes'  => array()
						),
						array(
								'name'        => $prefix.'_autocomplete_front_job_form',
								'std'         => '1',
								'label'       => __( 'Google address autocomplete (front-end) - new/edit job form', 'GJM' ),
								'cb_label'    => __( 'Yes', 'GJM' ),
								'desc'        => sprintf( __( 'Disply suggested results by Google when typing an address in the location field of the new/edit %s form in the front end', 'GJM' ), $sc_px_s ),
								'type'        => 'checkbox',
								'attributes'  => array()
						),
						array(
								'name'        => $prefix.'_autocomplete_country',
								'std'         => '',
								'placeholder' => '',
								'label'       => __( 'Autocomplete country restriction', 'GJM' ),
								'desc'        => __( "Enter the country code of the country which you would like to restrict the autocomplete results to. Leave it empty to show all countries", 'GJM' ),
								'attributes'  => array( 'size' => 3 )
						),
				),
		);
		
		if ( 
			isset( $_GET['page'] ) && $_GET['page'] == 'job-manager-settings' ||
			isset( $_POST['option_page']) && $_POST['action'] == 'update' && $_POST['option_page'] == 'job_manager' ) {
			
			array_unshift( $args['gjm_autocomplete'][1], 		
					array(
							'name'        => 'gjm_language',
							'std'         => '',
							'placeholder' => '',
							'label'       => __( 'Google API language', 'GJM' ),
							'desc'        => __( "This feature controls the language of the autocomplete results and Google map. Enter the language code of the langauge you would like to use. List of avaliable langauges can be found", 'GJM' ) . '<a href="https://spreadsheets.google.com/spreadsheet/pub?key=0Ah0xU81penP1cDlwZHdzYWkyaERNc0xrWHNvTTA1S1E&gid=1" target="_blank"> '.__('here', 'GJM' ) .'</a>',
							'attributes'  => array( 'size' => 3 )
					),
					array(
							'name'        => 'gjm_region',
							'std'         => '',
							'placeholder' => '',
							'label'       => __( 'Google API default region', 'GJM' ),
							'desc'        => sprintf( __( "This feature controls the regions of Goole API. Enter a country code; for example for United States enter US. you can find your country code <a %s>here</a>", 'GJM' ), 'href="http://geomywp.com/country-code/" target="blank"' ),
							'attributes'  => array( 'size' => 3 )
					)
			);	
			
			$temp_item = $args['gjm_autocomplete'][1][$prefix.'_import_locations'];
			unset( $args['gjm_autocomplete'][1][$prefix.'_import_locations'] );
			array_unshift( $args['gjm_autocomplete'][1], $temp_item);
		}
		
		$args['gjm_options'] = array(
				__( 'Geo Search Form Settings', 'GJM' ),
				array(
						array(
								'name'        => 'top_message',
								'std'         => '1',
								'label'       => '<h3 style="margin:0">'.__( 'Usage', 'GJM' ).'</h3>',
								'desc'        => sprintf( __( 'Using the GEO features is as simple as adding an attribute to the already exists %s shortcode. There are two ways you can use the shortcode:','GJM'), '<code>['.$sc_px.']</code>' ).'<br />
							
								<a id="'.$prefix.'-settings-usage-trigger" onclick="jQuery(&#34;#'.$prefix.'-settings-usage-wrapper&#34;).slideToggle();">'. __( 'Show more','GJM'). '</a>
								<div id="'.$prefix.'-settings-usage-wrapper" style="display:none;">'
								.'<ol>'
								    .'<li>'.sprintf( __( 'Add the attribute %s_use with the value 2 to the %s shortcode ( ex. %s ). This way the search form will use the settings on this page in order to add %s features to the %s search form.', 'GJM'), $prefix, '<code>['.$sc_px.']</code>', '<code>['.$sc_px.' '.$prefix.'_use="2"]</code>', $sc_px_s, $sc_px ).'</li>'
								    .'<li>'.sprintf( __( 'Add the attribute %s_use with the value 1 to the %s shortcode ( ex. %s ). This way you can define each of your %s search forms ( when you have more than one ) with different Geolocation features. You can do so by using the other shotcode attributes that %s provides:', 'GJM'), $prefix, '<code>['.$sc_px.']</code>', '<code>['.$sc_px.' '.$prefix.'_use="1"]</code>.', $sc_px, $sc_px_s ).'</li>'
								        .'<ol>'
											.'<li>'.$prefix.'_map - '. __( 'value 1 to display map or 0 to hide map', 'GJM' ).'</li>'
											.'<li>'.$prefix.'_map_width - '.__( 'map width in percentage or pixels','GJM' ).'</li>'
											.'<li>'.$prefix.'_map_height - '.__( 'map height in percentage or pixels','GJM' ).'</li>'
											.'<li>'.$prefix.'_orderby - '.__( 'values comma separated', 'GJM' ). ' distance,featured,title,date</li>'
											.'<li>'.$prefix.'_autocomplete - '.__( '1 to enable Google autocomplete on the address field','GJM' ).'</li>'
											.'<li>'.$prefix.'_radius - '.__( 'multiple values comma separated to display as dropdown or single value to be default. ex "5,10,25,50"','GJM' ).'</li>'
											.'<li>'.$prefix.'_distance - '.__( '1 to display the distance in each of the results','GJM' ).'</li>'
											.'<li>'.$prefix.'_scroll - '.__( '1 to scroll down to job listing when clicking on its marker on the map','GJM' ).'</li>'
											.'<li>'.$prefix.'_locator_use - '.__( '1 to enable locator button','GJM' ).'</li>'
											.'<li>'.$prefix.'_locator_title - '.__( 'Type the title for the locator button','GJM' ).'</li>'
								.'</ol>'
								. __( 'Custom shortcode example', 'GJM' ) . '<code>['.$sc_px.' '.$prefix.'_use="1" '.$prefix.'_map="1" '.$prefix.'_map_width="100%" '.$prefix.'_map_height="250px" '.$prefix.'_orderby="distance,featured,title" '.$prefix.'_autocomplete="1"]</code>'
								.'</ol>'
								.'</div>',
								'type'        => '',
								'attributes'  => array( 'style' => 'display:none')
						),
						array(
								'name'        => $prefix.'_prefix',
								'std'         => $prefix,
								'label'       => '',
								'desc'        => '',
								'type'        => '',
								'attributes'  => array( 'style' => 'display:none')
						),

						array(
								'name'        => $prefix.'_autocomplete',
								'std'         => '1',
								'label'       => __( 'Google address autocomplete', 'GJM' ),
								'cb_label'    => __( 'Yes', 'GJM' ),
								'desc'        => sprintf( __( 'Disply suggested results by Google when typing an address in the location field of the %s search form', 'GJM' ), $sc_px ),
								'type'        => 'checkbox',
								'attributes'  => array()
						),
						array(
								'name'        => $prefix.'_radius',
								'std'         => '5,10,15,25,50,100',
								'placeholder' => '',
								'label'       => __( 'Radius', 'GJM' ),
								'desc'        => __( 'Enter single value to be the default or multiple values comma separated to be displaed as a dropdown', 'GJM' ),
								'attributes'  => array()
						),
						array(
								'name'        => $prefix.'_units',
								'std'         => 'both',
								'placeholder' => '',
								'label'       => __( 'Units', 'GJM' ),
								'desc'        => __( 'Choose a single unit value to be used as the default or choose "Both" to let the user choose using a dropdown select menu', 'GJM' ),
								'type'        => 'select',
								'options'	  => array( 'both' => 'Both', 'imperial' => 'Miles', 'metric' => 'Kilometers' ),
								'attributes'  => array()
						),
						array(
								'name'        => $prefix.'_locator_use',
								'std'         => '0',
								'label'       => __( 'Display locator button', 'GJM' ),
								'cb_label'    => __( 'Yes', 'GJM' ),
								'desc'        => __( 'Disply button that will get the user\'s current location.', 'GJM' ),
								'type'        => 'checkbox',
								'attributes'  => array()
						),
						array(
								'name'        => $prefix.'_locator_title',
								'std'         => '',
								'placeholder' => __( 'Locator button title', 'GJM' ),
								'label'       => __( 'Locator button title', 'GJM' ),
								'desc'        => __( 'Type the title for the locator button', 'GJM' ),
								'attributes'  => array()
						),
						array(
								'name'        => $prefix.'_orderby',
								'std'         => 'distance,title,featured,date',
								'placeholder' => '',
								'label'       => __( 'Order By', 'GJM' ),
								'desc'        => __( 'Enter the values you want to use in the "Sort by" dropdown select box. Enter, comma separated, in the order that you want the values to appear any of the values: distance, title, date and featured.', 'GJM' ),
								'attributes'  => array()
						),
						array(
								'name'        => $prefix.'_map',
								'std'         => '1',
								'label'       => __( 'Display map', 'GJM' ),
								'cb_label'    => __( 'Yes', 'GJM' ),
								'desc'        => __( 'Disply map with '.$sc_px.' location above the list of results', 'GJM' ),
								'type'        => 'checkbox',
								'attributes'  => array()
						),
						array(
								'name'        => $prefix.'_map_width',
								'std'         => '100%',
								'placeholder' => '',
								'label'       => __( 'Map Width', 'GJM' ),
								'desc'        => __( 'Map width in pixels or percentage (ex. 100% or 250px)', 'GJM' ),
								'attributes'  => array()
						),
						array(
								'name'        => $prefix.'_map_height',
								'std'         => '250px',
								'placeholder' => '',
								'label'       => __( 'Map height', 'GJM' ),
								'desc'        => __( 'Map height in pixels or percentage (ex. 100% or 250px)', 'GJM' ),
								'attributes'  => array()
						),
						array(
								'name'        => $prefix.'_map_type',
								'std'         => 'ROADMAP',
								'label'       => __( 'Map Type', 'GJM' ),
								'desc'        => __( 'Choose the map type', 'GJM' ),
								'type'		  => 'select',
								'options'	  => array(
										'ROADMAP' 	=> __( 'ROADMAP' , 'GJM' ),
										'SATELLITE' => __( 'SATELLITE' , 'GJM' ),
										'HYBRID'    => __( 'HYBRID' , 'GJM' ),
										'TERRAIN'   => __( 'TERRAIN' , 'GJM' )
								),
						),
						array(
								'name'        => $prefix.'_scroll_wheel',
								'std'         => '1',
								'label'       => __( "Enable Maps scrollwheel control?", 'GJM' ),
								'cb_label'    => __( 'Yes', 'GJM' ),
								'desc'        => __( "Zoom map in/out using mouse wheel?", 'GJM' ),
								'type'        => 'checkbox',
								'attributes'  => array()
						),
						array(
								'name'        => $prefix.'_scroll',
								'std'         => '0',
								'label'       => __( 'Scroll on Marker Click', 'GJM' ),
								'cb_label'    => __( 'Yes', 'GJM' ),
								'desc'        => __( "On map's marker click, scroll down to the job associate with the marker in the list of results.", 'GJM' ),
								'type'        => 'checkbox',
								'attributes'  => array()
						),
						array(
								'name'        => $prefix.'_distance',
								'std'         => '1',
								'label'       => __( "Display distance", 'GJM' ),
								'cb_label'    => __( 'Yes', 'GJM' ),
								'desc'        => sprintf( __( 'Display the distance to each %s in the list of results', 'GJM' ), $sc_px_s ),
								'type'        => 'checkbox',
								'attributes'  => array()
						),
							
				),
		);

		$args['gjm_single_page_options'] = array(
				__( 'Geo Single Page Settings', 'GJM' ),
				array(
						array(
								'name'        => $prefix.'_single_map_use',
								'std'         => '1',
								'label'       => __( 'Display map', 'GJM' ),
								'cb_label'    => __( 'Yes', 'GJM' ),
								'desc'        => sprintf( __( 'Display map showing the %s location in a single %s page', 'GJM' ), $sc_px_s, $sc_px_s ),
								'type'        => 'checkbox',
								'attributes'  => array()
						),
						array(
								'name'        => $prefix.'_single_map_width',
								'std'         => '100%',
								'placeholder' => '',
								'label'       => __( 'Map Width', 'GJM' ),
								'desc'        => __( 'Map width in pixels or percentage (ex. 100% or 250px)', 'GJM' ),
								'attributes'  => array()
						),
						array(
								'name'        => $prefix.'_single_map_height',
								'std'         => '250px',
								'placeholder' => '',
								'label'       => __( 'Map height', 'GJM' ),
								'desc'        => __( 'Map height in pixels or percentage (ex. 100% or 250px)', 'GJM' ),
								'attributes'  => array()
						),
						array(
								'name'        => $prefix.'_single_map_type',
								'std'         => 'ROADMAP',
								'label'       => __( 'Map Type', 'GJM' ),
								'desc'        => __( 'Choose the map type', 'GJM' ),
								'type'		  => 'select',
								'options'	  => array(
										'ROADMAP' 	=> __( 'ROADMAP' , 'GJM' ),
										'SATELLITE' => __( 'SATELLITE' , 'GJM' ),
										'HYBRID'    => __( 'HYBRID' , 'GJM' ),
										'TERRAIN'   => __( 'TERRAIN' , 'GJM' )
								),
						),
						array(
								'name'        => $prefix.'_single_map_scroll_wheel',
								'std'         => '1',
								'label'       => __( 'Enable maps scrollwheel control?', 'GJM' ),
								'cb_label'    => __( 'Yes', 'GJM' ),
								'desc'        => __( 'Zoom map in/out using mouse wheel?', 'GJM' ),
								'type'        => 'checkbox',
								'attributes'  => array()
						),
				),
		);

		return $args;
	}

	/**
	 * Validate the updated values and return them to be saved in gjm_options single option.
	 * i like keeping all the options saved in a single array which can later be pulled with
	 * a single call to database instead of multiple calles per option
	 */
	function options_validate() {

		$valid_input = $this->settings;
		$prefix 	 = $this->prefix;
		
		$valid_input['language']								= $_POST['gjm_language'] 		 			 	 = ( !empty( $_POST['gjm_language'] ) ) ? $_POST['gjm_language'] : '';
		$valid_input['region']									= $_POST['gjm_region'] 		 			 	 	 = ( !empty( $_POST['gjm_region'] ) ) ? $_POST['gjm_region'] : '';
		$valid_input['search_page'][$prefix.'_use'] 			= 1;
		$valid_input['search_page'][$prefix.'_use'] 			= 1;
		$valid_input['admin']['autocomplete'] 					= $_POST[$prefix.'_autocomplete_admin'] 		 = ( isset( $_POST[$prefix.'_autocomplete_admin'] ) && $_POST[$prefix.'_autocomplete_admin'] == 1 ) ? 1 : 0;
		$valid_input['fronend_form']['autocomplete']   			= $_POST[$prefix.'_autocomplete_front_job_form'] = ( isset( $_POST[$prefix.'_autocomplete_front_job_form'] ) && $_POST[$prefix.'_autocomplete_front_job_form'] == 1 ) ? 1 : 0;
		$valid_input['autocomplete_options']['country']			= $_POST[$prefix.'_autocomplete_country'] 		 = ( !empty($_POST[$prefix.'_autocomplete_country']) ) ? $_POST[$prefix.'_autocomplete_country'] : '';
		$valid_input['search_page'][$prefix.'_autocomplete']  	= $_POST[$prefix.'_autocomplete']                = ( isset( $_POST[$prefix.'_autocomplete'] ) && $_POST[$prefix.'_autocomplete'] == 1 ) ? 1 : 0;
		$valid_input['search_page'][$prefix.'_radius']     		= $_POST[$prefix.'_radius'] 			   		 = ( !empty($_POST[$prefix.'_radius']) ) ? $_POST[$prefix.'_radius'] : '10,15,25,50,100';
		$valid_input['search_page'][$prefix.'_units'] 			= $_POST[$prefix.'_units'];
		$valid_input['search_page'][$prefix.'_locator_use']		= $_POST[$prefix.'_locator_use'] 				 = ( isset( $_POST[$prefix.'_locator_use'] ) && $_POST[$prefix.'_locator_use'] == 1 ) ? 1 : 0;
		$valid_input['search_page'][$prefix.'_locator_title'] 	= $_POST[$prefix.'_locator_title'] 				 = ( !empty($_POST[$prefix.'_locator_title']) ) ? $_POST[$prefix.'_locator_title'] : '';
		$valid_input['search_page'][$prefix.'_orderby']    		= $_POST[$prefix.'_orderby'] 					 = ( !empty($_POST[$prefix.'_orderby']) ) ? $_POST[$prefix.'_orderby'] : 'distance,title,featured,date';
		$valid_input['search_page'][$prefix.'_map'] 			= $_POST[$prefix.'_map'] 						 = ( isset( $_POST[$prefix.'_map'] ) && $_POST[$prefix.'_map'] == 1 ) ? 1 : 0;
		$valid_input['search_page'][$prefix.'_map_width']  		= $_POST[$prefix.'_map_width'] 					 = ( !empty($_POST[$prefix.'_map_width']) ) ? $_POST[$prefix.'_map_width'] : '100%';
		$valid_input['search_page'][$prefix.'_map_height'] 		= $_POST[$prefix.'_map_height'] 				 = ( !empty($_POST[$prefix.'_map_height']) ) ? $_POST[$prefix.'_map_height'] : '250px';
		if ( in_array( $_POST[$prefix.'_map_type'], array( 'ROADMAP', 'SATELLITE','HYBRID','TERRAIN' ) ) ) $valid_input['search_page'][$prefix.'_map_type'] = $_POST[$prefix.'_map_type'];
		$valid_input['search_page'][$prefix.'_scroll_wheel'] 	= $_POST[$prefix.'_scroll_wheel']   			 = ( isset( $_POST[$prefix.'_scroll_wheel'] ) && $_POST[$prefix.'_scroll_wheel'] == 1 ) ? true : false;
		$valid_input['search_page'][$prefix.'_scroll']        	= $_POST[$prefix.'_scroll']                      = ( isset( $_POST[$prefix.'_scroll'] ) && $_POST[$prefix.'_scroll'] == 1 ) ? 1 : 0;
		$valid_input['search_page'][$prefix.'_distance'] 		= $_POST[$prefix.'_distance']                    = ( isset( $_POST[$prefix.'_distance'] ) && $_POST[$prefix.'_distance'] == 1 ) ? 1 : 0;
		$valid_input['single_page'][$prefix.'_map'] 			= $_POST[$prefix.'_single_map_use']              = ( isset( $_POST[$prefix.'_single_map_use'] ) && $_POST[$prefix.'_single_map_use'] == 1 ) ? 1 : 0;
		$valid_input['single_page'][$prefix.'_map_width']  		= $_POST[$prefix.'_single_map_width'] 			 = ( !empty($_POST[$prefix.'_single_map_width']) ) ? $_POST[$prefix.'_single_map_width'] : '100%';
		$valid_input['single_page'][$prefix.'_map_height']		= $_POST[$prefix.'_single_map_height'] 			 = ( !empty($_POST[$prefix.'_single_map_height']) ) ? $_POST[$prefix.'_single_map_height'] : '200px';
		if ( in_array( $_POST[$prefix.'_single_map_type'], array( 'ROADMAP', 'SATELLITE','HYBRID','TERRAIN' ) ) ) $valid_input['single_page'][$prefix.'_map_type'] = $_POST[$prefix.'_single_map_type'] ;
		$valid_input['single_page'][$prefix.'_scroll_wheel'] 	= $_POST[$prefix.'_single_map_scroll_wheel'] 	 = ( isset( $_POST[$prefix.'_single_map_scroll_wheel'] ) && $_POST[$prefix.'_single_map_scroll_wheel'] == 1 ) ? true : false;

		update_option( $prefix.'_options', $valid_input );
	}
}
new GJM_Admin_Settings;