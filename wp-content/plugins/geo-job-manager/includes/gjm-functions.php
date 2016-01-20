<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) )
	exit;

/**
 * GJM_Query_class
 */
class GJM_Query_class {

	function __construct() {

		$this->settings    = get_option( 'gjm_options' );
		$this->prefix 	   = 'gjm';
		
		//form labels to be translated
		$this->form_labels = array(
				'miles' 	 => __( 'Miles', 'GJM' ),
				'kilometers' => __( 'Kilometers', 'GJM' ),
				'within'	 => __( 'Within', 'GJM' ),
				'distance' 	 => __( 'Distance', 'GJM' ),
				'title' 	 => __( 'Title', 'GJM' ),
				'featured' 	 => __( 'Featured', 'GJM' ),
				'date' 		 => __( 'Date', 'GJM' ),
				'sort_by'	 => __( 'Sort by', 'GJM' ),
				'resize_map' => __( 'Resize map', 'GJM' ),
				'company'	 => __( 'Company:', 'GJM' ),
				'address'    => __( 'Address:', 'GJM' ),
				'job_type'	 => __( 'Job Type:', 'GJM' ),
				'posted'	 => __( 'Posted %s ago', 'GJM' ),
				'mi'		 => __( 'mi', 'GJM' ),
				'km'		 => __( 'km', 'GJM ' )
		);
						
		add_filter( 'job_manager_output_jobs_defaults', 		array( $this, 'default_attrs' ) );
		add_action( 'job_manager_job_filters_search_jobs_end',  array( $this, 'modify_search_form' ), 20 );
		add_filter( 'job_manager_get_listings_args', 			array( $this, 'modify_query_args' ) );
		add_filter( 'get_job_listings_query_args', 				array( $this, 'search_query' ), 99 );
		add_filter( 'the_job_location', 						array( $this, 'job_distance' ), 99, 2 );
		add_action( 'single_job_listing_meta_end', 				array( $this, 'single_page_map' ) );
		add_action( 'gjm_single_job_map', 						array( $this, 'single_page_map' ) );
		add_action( 'submit_job_form_job_fields_start', 		array( $this, 'new_job_form_autocomplete' ) );
		add_action( 'job_manager_update_job_data', 				array( $this, 'frontend_new_location' ), 20, 2 );
		add_shortcode( 'gjm_jobs_map', 							array( $this, 'global_map' ) );
		add_shortcode( 'gjm_results_map', 						array( $this, 'results_map' ) );
	}

	/**
	 * add Google address autocomplete to new/edit job form
	 * @since  1.0
	 * @author Eyal Fitoussi
	 */
	function new_job_form_autocomplete() {
	
		if ( empty( $this->settings['fronend_form']['autocomplete'] ) )
			return;
	
		$autocomplete_options = array(
				'input_field'   => 'job_location',
				'form_type'		=> 'job_listings',
				'options' 		=> $this->settings['autocomplete_options']
		);
		
		wp_enqueue_script( 'gjm-autocomplete' );
		wp_localize_script( 'gjm-autocomplete', 'AutoCompOptions', $autocomplete_options );
	}
	
	/**
	 * GJM Function - declair GJM attributes
	 * @since 1.0
	 * @author Eyal Fitoussi
	 */
	function default_attrs( $args ) {

		$args[$this->prefix.'_use']           = 0;
		$args[$this->prefix.'_map']           = 0;
		$args[$this->prefix.'_map_width']     = '100%';
		$args[$this->prefix.'_map_height']    = '250px';
		$args[$this->prefix.'_orderby']       = 'distance,featured,title,date';
		$args[$this->prefix.'_autocomplete']  = 0;
		$args[$this->prefix.'_radius']        = '5,10,15,25,50,100';
		$args[$this->prefix.'_units']         = 'both';
		$args[$this->prefix.'_distance']      = 1;
		$args[$this->prefix.'_scroll']        = 1;
		$args[$this->prefix.'_locator_use']   = 0;
		$args[$this->prefix.'_locator_title'] = 'Locate me';
		$args[$this->prefix.'_map_type']      = 'ROADMAP';
		$args[$this->prefix.'_scroll_wheel']  = 1;

		return $args;
	}

	/**
	 * display locator button
	 * @since 1.2
	 * @author Eyal Fitoussi
	 */
	protected function locator_button() {

		if ( empty( $this->filters[$this->prefix.'_locator_use'] ) )
			return;
		
		$locator  = '<div class="'.$this->prefix.'-locator-button-wrapper">';
		$locator .= 	'<input type="button" class="'.$this->prefix.'-locator-button" value="' . $this->filters[$this->prefix.'_locator_title'] . '" />';
		$locator .= 	'<img src="' . GJM_URL . '/assets/images/gmw-loader.gif" class="'.$this->prefix.'-locator-loader" />';
		$locator .=	'</div>';
		
		echo apply_filters( $this->prefix.'_locator_button', $locator, $this->filters );
	}

	/**
	 * radius filter to display in search form
	 * If multiple values we will display dropdown select box otherwise if single value it will be default and hidden
	 * @since 1.0
	 * @author Eyal Fitoussi
	 */
	protected function filters_radius() {

		$radius = explode( ",", $this->filters[$this->prefix.'_radius'] );
		$output = '';
		
		//display dropdown
		if ( count( $radius ) > 1 ) {
			
			//set the title of the dropdown based on the units selected
			if ( empty( $stitle ) ) {
				$stitle = ( $this->filters[$this->prefix.'_units'] == 'imperial' ) ? $this->form_labels['miles'] : $this->form_labels['kilometers'];
			}			
			$btitle = ( empty( $btitle ) ) ? $this->form_labels['within'] : $btitle;
		
			/*
			 * count the filters being added by gjm to the form. based on that we will set the
			 * element class name
			 */
			$filterCount = 1;
			
			if ( $this->filters[$this->prefix.'_units'] == 'both' || ( $this->filters[$this->prefix.'_units'] != 'imperial' && $this->filters[$this->prefix.'_units'] != 'metric' ) ) {
				$both = true;
				$filterCount++;
			}
			if ( isset( $this->filters[$this->prefix.'_orderby'] ) && count( explode( ",", $this->filters[$this->prefix.'_orderby'] ) ) > 1 ) {
				$filterCount++;
			}
			
			//Displace dropdown
			$output .= '<div class="'.$this->prefix.'-radius-wrapper '.$this->prefix.'-filters-count-' . $filterCount . ' ' .$this->prefix.'-dropdown-wrapper">';
			$output .= '<select class="'.$this->prefix.'-dropdown-menu '.$this->prefix.'-radius" name="'.$this->prefix.'_radius">';
			$output .= '<option class="radius-first-option" value="' . end( $radius ) . '">';
			
			if ( isset( $both ) ) {
				$output .=  $btitle;
			} else {
				$output .=  $stitle; echo '</option>';
			}
			
			foreach ( $radius as $value ) {
				$output .=  '<option value="' . $value . '">' . $value . '</option>';
			}
			
			$output .= '</select>';
			$output .=  '</div>';
		
		} else {
			//display hidden default value
			$output .=  '<input type="hidden" name="'.$this->prefix.'_radius" value="' . end( $radius ) . '" />';
		}
		
		echo apply_filters( $this->prefix.'_form_radius_filter', $output, $radius, $this );
	}

	/**
	 * Units filter for search form
	 * Will display dropdown when choosing to display both units otherwise the deault will be hidden
	 * @since  1.0
	 * @author Eyal Fitoussi
	 */
	protected function filters_units() {

		//display dropdown
		if ( $this->filters[$this->prefix.'_units'] == 'both' || ( $this->filters[$this->prefix.'_units'] != 'imperial' && $this->filters[$this->prefix.'_units'] != 'metric' ) ) {

			/*
			 * count the filters being added by gjm to the form. based on that we will set the
			 * element class name
			 */
			$filterCount = 1;
			if ( count( explode( ",", $this->filters[$this->prefix.'_radius'] ) ) > 1 ) {
				$filterCount++;
			}
			if ( isset( $this->filters[$this->prefix.'_orderby'] ) && count( explode( ",", $this->filters[$this->prefix.'_orderby'] ) ) > 1 ) {
				$filterCount++;
			}
			$unit_m = $unit_i = false;
	
			//display dropdown
			echo '<div class="'.$this->prefix.'-units-wrapper '.$this->prefix.'-filters-count-' . $filterCount . ' ' .$this->prefix.'-dropdown-wrapper">';
			echo 	'<select name="'.$this->prefix.'_units" class="'.$this->prefix.'-dropdown-menu '.$this->prefix.'-units">';
			echo 		'<option value="imperial">' . $this->form_labels['miles'] . '</option>';
			echo 		'<option value="metric">' . $this->form_labels['kilometers'] . '</option>';
			echo 	'</select>';
			echo '</div>';
		
		} else {
	
			//display hidden field
			echo '<input type="hidden" name="'.$this->prefix.'_units" value="' . $this->filters[$this->prefix.'_units'] . '" />';
		}
	}

	/**
	 * radius filter to display in search form
	 * If multiple values we will display dropdown select box otherwise if single value it will be default and hidden
	 * @since  1.0
	 * @author Eyal Fitoussi
	 */
	protected function filters_sort() {

		$orderby = explode( ",", $this->filters[$this->prefix.'_orderby'] );

		//display dropdown
		if ( count( $orderby ) > 1 ) {

			/*
			 * count the filters being added by gjm to the form. based on that we will set the
			 * element class name
			 */
			$valCount    = 1;
			$filterCount = 1;
			if ( $this->filters[$this->prefix.'_units'] == 'both' || ( $this->filters[$this->prefix.'_units'] != 'imperial' && $this->filters[$this->prefix.'_units'] != 'metric' ) ) {
				$filterCount++;
			}
			if ( count( explode( ",", $this->filters[$this->prefix.'_radius'] ) ) > 1 ) {
				$filterCount++;
			}
			
			$ordery_titles = array(
					'distance' 	=> $this->form_labels['distance'],
					'title' 	=> $this->form_labels['title'],
					'featured' 	=> $this->form_labels['featured'],
					'date' 		=> $this->form_labels['date']
			);
			
			//display dropdown
			echo '<div class="'.$this->prefix.'-orderby-wrapper '.$this->prefix.'-filters-count-' . $filterCount . ' ' .$this->prefix.'-dropdown-wrapper">';
			echo 	'<select class="'.$this->prefix.'-dropdown-menu '.$this->prefix.'-orderby" name="'.$this->prefix.'_orderby">';
			echo 		'<option class="orderby-first-option" value="">' . $this->form_labels['sort_by'] . '</option>';
	
			foreach ( $orderby as $value ) {
				
					if ( in_array( $value, array( 'distance', 'title', 'featured', 'date' ) ) ) {				
					echo '<option value="' . $value . '" class="'.$this->prefix.'-orderby-value-' . $valCount . '">'.$ordery_titles[$value].'</option>';
				}
				$valCount++;
			}
			
			echo 	'</select>';
			echo '</div>';
		
		} else {
		
			//display hidden default value
			echo '<input type="hidden" name="'.$this->prefix.'_orderby" value="' . reset( $orderby ) . '" />';
		}
	}
	
	/**
	 * add Google address autocomplete to new/edit job form
	 * @since  1.0
	 * @author Eyal Fitoussi
	 */
	public function frontend_search_form_autocomplete() {
	
		if ( empty( $this->filters[$this->prefix.'_autocomplete'] ) )
			return;

		$autocomplete_options = array(
				'form_type'   => ( $this->prefix == 'gjm' ) ? 'job_listings' : 'resumes',
				'input_field' => 'search_location',
				'options' 	  => $this->settings['autocomplete_options']
		);
		
		wp_enqueue_script( 'gjm-autocomplete' );
		wp_localize_script( 'gjm-autocomplete', 'AutoCompOptions', $autocomplete_options );
	}

	/**
	 * add some elements to search form
	 * @since  1.0
	 * @author Eyal Fitoussi
	 */
	public function modify_search_form( $atts ) {

		//if we are using shotcode attributes from the settings
		if ( empty( $atts[$this->prefix.'_use'] ) ) {
	
			echo '<input type="hidden" name="'.$this->prefix.'_use" value="0" />';
			return;
		
		} elseif ( $atts[$this->prefix.'_use'] == 1 ) {

			$this->filters = $atts;
			//add gjm hidden values to the form to be able to pass it into the query and otehr functions
			echo '<input type="hidden" name="'.$this->prefix.'_use" value="1" />';
			echo '<input type="hidden" name="'.$this->prefix.'_distance" value="' . $this->filters[$this->prefix.'_distance'] . '" />';
			echo '<input type="hidden" name="'.$this->prefix.'_map" value="' . $this->filters[$this->prefix.'_map'] . '" />';
			echo '<input type="hidden" name="'.$this->prefix.'_map_width" value="' . $this->filters[$this->prefix.'_map_width'] . '" />';
			echo '<input type="hidden" name="'.$this->prefix.'_map_height" value="' . $this->filters[$this->prefix.'_map_height'] . '" />';
			echo '<input type="hidden" name="'.$this->prefix.'_scroll" value="' . $this->filters[$this->prefix.'_scroll'] . '" />';
			echo '<input type="hidden" id="gjm_lat" name="gjm_lat" value="" />';
			echo '<input type="hidden" id="gjm_lng" name="gjm_lng" value="" />';

			//when using the options set in the admin
		} elseif ( $atts[$this->prefix.'_use'] == 2 ) {
			
			$this->filters = $this->settings['search_page'];
			echo '<input type="hidden" name="'.$this->prefix.'_use" value="2" />';
			echo '<input type="hidden" id="gjm_lat" name="gjm_lat" value="" />';
			echo '<input type="hidden" id="gjm_lng" name="gjm_lng" value="" />';
		} else {
			return;
		}
		
		$this->filters['user_location']['lat'] = false;
		$this->filters['user_location']['lng'] = false;
		
		//add locator button
		self::locator_button();
		
		//add radius filter
		self::filters_radius();
		
		//add units filter
		self::filters_units();
		
		//add sort by filter
		self::filters_sort();
		
		//trigger autocomplete
		self::frontend_search_form_autocomplete();
		
		//load stylsheet
		wp_enqueue_style( 'gjm-frontend-style' );

		$gjmMap = false;

		//check if map is needed
		if ( !empty( $this->filters[$this->prefix.'_map'] ) ) {

			//load javascript to be used with the map
			wp_enqueue_script( $this->prefix.'-map' );

			//create map element
			$map  = '<div class="'.$this->prefix.'-map-wrapper '.$this->prefix.'-expand-map-wrapper" style="width:' . $this->filters[$this->prefix.'_map_width'] . ';height:' . $this->filters[$this->prefix.'_map_height'] . ';">';
			$map .= 	'<div id="'.$this->prefix.'-expand-map-trigger" class="'.$this->prefix.'-expand-map-trigger dashicons dashicons-editor-expand" style="display:none;" title="'.$this->form_labels['resize_map'] .'"></div>';		
			$map .= 	'<div class="'.$this->prefix.'-map '.$this->prefix.'-locations-map" id="'.$this->prefix.'-locations-map" style="width:100%; height:100%"></div>';
			$map .= '</div>';

			$gjmMap = true;
			
			if ( !wp_script_is( 'gmw-marker-clusterer', 'enqueue' ) ) {
				wp_enqueue_script( 'gmw-marker-clusterer' );
			}
			
			wp_localize_script( $this->prefix.'-map', 'gjmMap', $map );
			wp_localize_script( $this->prefix.'-map', 'mapIcon', apply_filters( 'gjm_map_icon', 'http://mt.googleapis.com/vt/icon/name=icons/spotlight/spotlight-poi.png&scale=1' ) );	
		}	
			
		$this->javascript_trigger( $gjmMap );	
	}
	
	function javascript_trigger( $gjmMap ) {
		?>
		<script>
            jQuery(document).ready(function($) {
				
				$( '.job_filters' ).on( 'click', '.reset', function () {
			
					var target = $( this ).closest( 'div.job_listings' );
					var form = $( this ).closest( 'form' );
					
					form.find('.gjm-dropdown-menu option:first-child').attr("selected", "selected");

					target.triggerHandler( 'reset' );
					target.triggerHandler( 'update_results', [ 1, false ] );

					return false;
				} );
			    				
                //only when map is needed
                if ('<?php echo $gjmMap; ?>' == true) {

                	gjmMapObject  = {};
                    removeMarkers = true;

                    //create markers holder
                    jobMarkers 	  			= [];
                    userMarker 	  			= false;
                    window.gjmMarkerCluster = false;

                    //create locationss holder
                    gjmLocations = [];
					mapHolder    = false;
					
                    //append map into shortcode					
					if ( $('#<?php echo 'gjm-results-map-holder'; ?>').length ) { 
						mapHolder = jQuery('#<?php echo 'gjm-results-map-holder'; ?>');
						mapHolder.append(gjmMap);
					//otherwise append it below the search form
					} else {
						mapHolder = jQuery('.job_filters');
						mapHolder.after(gjmMap);
					}

                    //create map
                    gjmMap = new google.maps.Map(document.getElementById('gjm-locations-map'), {
                        scrollwheel: '<?php echo $this->filters[$this->prefix.'_scroll_wheel']; ?>',
                        zoom: 13,
                        mapTypeId: google.maps.MapTypeId['<?php echo $this->filters[$this->prefix.'_map_type']; ?>'],
                        styles: false
                    });

                  	//hide the map on no results
					jQuery(document).ajaxStop(function() {  
						if ( jQuery('.gjm-map-wrapper').is(':visible') && jQuery('.no_job_listings_found').is(':visible') ) {
		        			jQuery('.gjm-map-wrapper').slideToggle();
						}
			        });

			        //create expend map button
                    google.maps.event.addListenerOnce(gjmMap, 'idle', function(){
                    	ExpandControl = document.getElementById('gjm-expand-map-trigger');
                    	ExpandControl.style.position = 'absolute';	
        				gjmMap.controls[google.maps.ControlPosition.TOP_RIGHT].push(ExpandControl);	
        				ExpandControl.style.display = 'block';		
                    });
                            			
        			//expand map function
        		    if ( jQuery('.gjm-map-wrapper  #gjm-expand-map-trigger').length ) {
           				
        		    	jQuery('.gjm-map-wrapper  #gjm-expand-map-trigger').click(function() {

							var detachMap = jQuery(this).closest('.gjm-map-wrapper');
        		    		var center = gjmMap.getCenter();
        		    		jQuery(this).closest('.gjm-map-wrapper').toggleClass('gjm-expanded-map');          		
        		    		jQuery(this).toggleClass('dashicons-editor-expand').toggleClass('dashicons-editor-contract');
        		    		
							if ( jQuery('.gjm-map-detach').length ) {
								detachMap.detach().insertAfter('.gjm-map-detach');
	        		    		mapHolder.removeClass('gjm-map-detach');
							} else {
								mapHolder.addClass('gjm-map-detach');
								detachMap.detach().appendTo('body');
							} 
							
        		    		//setTimeout(function() { 
        		    		google.maps.event.trigger(gjmMap, 'resize');
 	      		    		gjmMap.setCenter(center);
        				    //}, 200);            		
        		    	});
        		    }

                    //when click on Load more we do not need to remove the markers from the map
                    $('.load_more_jobs').on("click", function() {
                        removeMarkers = false;
                    });
                }
                
                //when location input field changes check if empty or not
                //if has value dynamically change the value of the radius dropdown
                $('#search_location').change(function() {
                    if ( $(".gjm-radius option:first").is(":selected") && $('#search_location').val() != '' ) {
                        $(".gjm-radius option:last-child").attr('selected', 'selected');
                    }
                });

                //refresh form when changing radius or units value
                $('.gjm-radius, .gjm-units').change(function() {
                     //only if location input field is not empty
                    if ( $('#search_location').val() == '' )
                        return;
                    
                    var target = $(this).closest('div.job_listings');
                    target.trigger('update_results', [1, false]);
                });

                //refresh form when changing the sort by value
                $('.gjm-orderby').change(function() {
                    var target = $(this).closest('div.job_listings');
                    target.trigger('update_results', [1, false]);
                });

                /**
                 * locator button
                 */
                $('.gjm-locator-button').click(function() {

                	$('#search_location').val('');
                	//$('#search_location, #gjm_search_location').val('');
                	
                    //hide locator button and show loader 
                    $('.gjm-locator-button').fadeToggle('fast', function() {
                        $('.gjm-locator-loader').fadeToggle('fast');
                    });

                    // if GPS exists locate the user //
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(showPosition, showError, {timeout: 10000});
                    } else {
                        // if nothing found we cant do much. we cant locate the user :( //
                        alert('Sorry! Geolocation is not supported by this browser and we cannot locate you.');
                    }

                    // GPS locator function //
                    function showPosition(position) {
                        var geocoder = new google.maps.Geocoder();
                        geocoder.geocode({'latLng': new google.maps.LatLng(position.coords.latitude, position.coords.longitude)}, function(results, status) {

                            if (status == google.maps.GeocoderStatus.OK) {

                            	$('#search_location').val(results[0].formatted_address);
                        		$('#gjm_lat').val(results[0].geometry.location.lat());
                        		$('#gjm_lng').val(results[0].geometry.location.lng());
                        		
                                var target = $('#search_location').closest('div.job_listings');
                                target.trigger('update_results', [1, false]);

                                $('.gjm-locator-loader').fadeToggle('fast', function() {
                                    $('.gjm-locator-button').fadeToggle('fast');
                                });
                            } else {
                                alert('Geocoder failed due to: ' + status);
                            }
                        });
                    }

                    function showError(error) {

                        switch (error.code) {
                            case error.PERMISSION_DENIED:
                                alert('User denied the request for Geolocation.');
                                break;
                            case error.POSITION_UNAVAILABLE:
                                alert('Location information is unavailable.');
                                break;
                            case 3:
                                alert('The request to get user location timed out.');
                                break;
                            case error.UNKNOWN_ERROR:
                                alert('An unknown error occurred');
                                break;
                        }

                        $('.gjm-locator-loader').fadeToggle('fast', function() {
                            $('.gjm-locator-button').fadeToggle('fast');
                        });
                    }
                });
            });
        </script>
		<?php 
	}
	
	/**
	 * Modify the query args and set the location search to false. We need to
	 * prevent the original location query from happening since
	 * we are doing our own locaiton query.
	 * @param array $args
	 * @return boolean
	 */
	public function modify_query_args( $args ) {

		parse_str( $_REQUEST['form_data'], $form_data );
		
		//this applys only for resumes geolocation
		if ( $this->prefix == 'grm' && !empty( $form_data[$this->prefix.'_use'] ) && ( $form_data[$this->prefix.'_use'] == 1 || $form_data[$this->prefix.'_use'] == 2 ) ) {					
			$args['search_location'] = false;
		}		
		return $args;
	}

	/**
	 * GJM Function - do some distance quering
	 * @since  1.0
	 * @author Eyal Fitoussi
	 */
	function search_query( $query_args ) {
					
		if ( empty( $_REQUEST['form_data'] ) )
			return $query_args;
		
		//get form values
		$atts = wp_parse_args( $_REQUEST['form_data'], array(
				'search_location'  			  => '',
				'gjm_lat'					  => '',
				'gjm_lng'					  => '',
				$this->prefix.'_use'          => 0,
				$this->prefix.'_map'          => 0,
				$this->prefix.'_map_width'    => '100%',
				$this->prefix.'_map_height'   => '250px',
				$this->prefix.'_units'        => 'both',
				$this->prefix.'_autocomplete' => 0,
				$this->prefix.'_radius'       => '5,10,15,25,50,100',
				$this->prefix.'_distance'     => 0,
				$this->prefix.'_scroll'       => 0,
				$this->prefix.'_orderby'      => ''
		) );

		if ( empty( $atts[$this->prefix.'_use'] ) ) {
			
			return $query_args;
		
		//check if we are using the shortcode attributes settings
		} elseif ( isset( $atts[$this->prefix.'_use'] ) && $atts[$this->prefix.'_use'] == 1 ) {

			//set attributes to global, we will pass it to otehr functions
			$this->filters = $atts;

		//otherwise, are we using the admin settings
		} elseif ( isset( $atts[$this->prefix.'_use'] ) && $atts[$this->prefix.'_use'] == 2 ) {

			$this->filters                    		 = $this->settings['search_page'];
			$this->filters[$this->prefix.'_use']     = $atts[$this->prefix.'_use'];
			$this->filters['search_location'] 		 = $atts['search_location'];
			$this->filters['gjm_lat'] 		 		 = $atts['gjm_lat'];
			$this->filters['gjm_lng'] 		 		 = $atts['gjm_lng'];
			$this->filters[$this->prefix.'_units']   = $atts[$this->prefix.'_units'];
			$this->filters[$this->prefix.'_radius']  = $atts[$this->prefix.'_radius'];
			$this->filters[$this->prefix.'_orderby'] = $atts[$this->prefix.'_orderby'];

		//if we do not use gjm in this shortcode get out!
		} else {
			return $query_args;
		}

		/*
		 * if we are using gjm orderby we will need to override the original setting created by Wp Jobs Manager plugin
		 * Unless when using orderby "featured" we will leave it as is
		 */
		if ( !empty( $this->filters[$this->prefix.'_orderby'] ) && $this->filters[$this->prefix.'_orderby'] != 'featured' ) {

			//force gjm orderby value
			$query_args['orderby'] = $this->filters[$this->prefix.'_orderby'];

			//remove orderby "featured" filter
			remove_filter( 'posts_clauses', 'order_featured_job_listing' );
			remove_filter( 'posts_clauses', 'order_featured_resume' );
			
			unset( $query_args['meta_key'] );

			//adjust the order of posts when choosing to order by title
			if ( $this->filters[$this->prefix.'_orderby'] == 'title' ) {
				$query_args['order'] = 'ASC';
			}
			
			if ( $this->filters[$this->prefix.'_orderby'] == 'date' && $this->prefix == 'grm' )  {
			
				$query_args['orderby'] = 'modified';
			}
			
		//if address entered and no orderby selected we will do featured,distance
		} elseif ( empty( $this->filters[$this->prefix.'_orderby'] ) && !empty( $this->filters['search_location'] ) ) {

			$query_args['orderby'] = 'meta_key';
			$this->filters[$this->prefix.'_orderby'] = 'featured';
		
		}
		
		$query_args[$this->prefix]['location'] = $atts['search_location'];
		$query_args[$this->prefix]['units']    = $atts[$this->prefix.'_units'];
		$query_args[$this->prefix]['radius']   = $atts[$this->prefix.'_radius'];
	
		if ( !empty( $query_args[$this->prefix]['location'] ) ) {
			unset($query_args['meta_query'][0]);
		}

		$this->filters['user_location']['lat'] = false;
		$this->filters['user_location']['lng'] = false;
				
		//when searhing with address
		if ( !empty( $this->filters['search_location'] ) && $this->filters['search_location'] != 'Any Location' && $this->filters['search_location'] != 'Location' ) {
		
			if ( !empty( $this->filters['gjm_lat'] ) && !empty( $this->filters['gjm_lng'] ) ) {
		
				$this->filters['user_location']['lat'] = $this->filters['gjm_lat'];
				$this->filters['user_location']['lng'] = $this->filters['gjm_lng'];
				
			} else {
				include_once( GJM_PATH .'/includes/gjm-geocode.php' );
					
				$this->geocoded = gjm_geocoder( $this->filters['search_location'] );
					
				if ( !empty( $this->geocoded['lat'] ) && !empty( $this->geocoded['lng'] ) ) {
						
					$this->filters['user_location']['lat'] = $this->geocoded['lat'];
					$this->filters['user_location']['lng'] = $this->geocoded['lng'];
					?>
					<script>
						jQuery('#gjm_lat').val('<?php echo $this->geocoded['lat']; ?>');
						jQuery('#gjm_lng').val('<?php echo $this->geocoded['lng']; ?>');
					</script>
					<?php 
				}
			}
		}

		add_filter( 'posts_clauses', array( $this, 'query_clauses' ), 99 );

		/*
		 * add filter that will pass the results of this query to javascipt
		 * In order to display the results on the map
		 */
		if ( $this->filters[$this->prefix.'_map'] == 1 ) {
			add_action( 'the_post', array( $this, 'the_post' 				  ) );
			add_action( 'loop_end', array( $this, 'pass_values_to_javascript' ) );
		}
		
		return $query_args;
	}

	//filter the job query
	public function query_clauses( $clauses ) {

		global $wpdb;
		
		//when searhing with address
		if ( !empty( $this->filters['search_location'] ) && $this->filters['search_location'] != 'Any Location' && $this->filters['search_location'] != 'Location' ) {
	
			if ( empty( $this->filters['user_location']['lat'] ) || empty( $this->filters['user_location']['lng'] ) ) {
				
				$this->geocode_message = ( !empty( $this->geocoded['error'] ) ) ? $this->geocoded['error'] : '';
					
				//"kill" the query
				$clauses['where'] .= " AND 1 = 2";
	
				return $clauses;							
			}
			
			$earthRadius = ( $this->filters[$this->prefix.'_units'] == 'imperial' ) ? 3959 : 6371;

			// join GJM locations table into the job query
			$gjmClauses['join'] = " INNER JOIN " . $wpdb->prefix . "places_locator gmwlocations ON $wpdb->posts.ID = gmwlocations.post_id ";

			//other query filters
			$gjmClauses['fields']  = $wpdb->prepare( ", gmwlocations.formatted_address, gmwlocations.address, gmwlocations.lat, gmwlocations.long,  ROUND( %d * acos( cos( radians( %s ) ) * cos( radians( gmwlocations.lat ) ) * cos( radians( gmwlocations.long ) - radians( %s ) ) + sin( radians( %s ) ) * sin( radians( gmwlocations.lat) ) ),1 ) AS distance", 
					array( $earthRadius, $this->filters['user_location']['lat'], $this->filters['user_location']['lng'], $this->filters['user_location']['lat'] ) );
			
			$gjmClauses['where']   = '';
			$gjmClauses['groupby'] = $wpdb->prepare( " $wpdb->posts.ID HAVING distance <= %d OR distance IS NULL", $this->filters[$this->prefix.'_radius'] );
			$gjmClauses['orderby'] = $clauses['orderby'];
			
			//order by distance when needed
			if ( $this->filters[$this->prefix.'_orderby'] == 'distance' ) {
				
				$gjmClauses['orderby'] = 'distance';
			
			//} elseif ( $this->filters[$this->prefix.'_orderby'] == 'date' && $this->prefix == 'grm' ) {
					
			//	$gjmClauses['orderby'] = "$wpdb->posts.post_modified DESC";
				
			} elseif ( $this->filters[$this->prefix.'_orderby'] == 'featured' ) {

				if ( $this->prefix == 'gjm' ) {
					
					$gjmClauses['orderby'] = "$wpdb->posts.menu_order ASC, distance ASC";
					
				} elseif ( $this->prefix == 'grm' ) {

					$gjmClauses['orderby'] = "$wpdb->postmeta.meta_value+0 DESC, distance ASC";
				}
			} 

			apply_filters( $this->prefix.'_location_geocoded_query_clauses', $gjmClauses, $clauses, $this->filters );
		//when no address entereed we will display all job
		} else {

			// join the location table into the query
			$gjmClauses['join']    = " LEFT JOIN " . $wpdb->prefix . "places_locator gmwlocations ON $wpdb->posts.ID = gmwlocations.post_id ";
			//$gjmClauses['fields']  = "$wpdb->posts.ID , $wpdb->posts.post_title, $wpdb->posts.post_date , $wpdb->posts.post_author, $wpdb->posts.post_status, $wpdb->posts.post_type, $wpdb->posts.post_name, $wpdb->posts.post_modified, gmwlocations.lat, gmwlocations.long ";
			$gjmClauses['fields']  = ", gmwlocations.lat, gmwlocations.long, gmwlocations.formatted_address, gmwlocations.address ";
			$gjmClauses['where']   = " AND ( gmwlocations.lat != '0.000000' AND gmwlocations.long != '0.000000' ) ";
			$gjmClauses['groupby'] = $clauses['groupby'];
			$gjmClauses['orderby'] = $clauses['orderby'];
			
			apply_filters( $this->prefix.'_location_query_clauses', $gjmClauses, $clauses, $this->filters );
		}
			
		$clauses['join']   .= $gjmClauses['join'];
		$clauses['fields'] .= $gjmClauses['fields'];
		$clauses['where']  .= $gjmClauses['where'];
		$clauses['groupby'] = $gjmClauses['groupby'];
		$clauses['orderby'] = $gjmClauses['orderby'];
		
		return $clauses;
	}
	
	/**
	 * Pass locations and other values to javasctip to display on the map
	 * @since  1.0
	 * @author Eyal Fitoussi
	 */
	public function pass_values_to_javascript( $query ) {

		//pass geocoded data to javasctip. we need the lat/long to show the user's current locaiton
		$gjmMapArgs = apply_filters( 'gjm_javascript_values', array(
				'locations' => $query->posts,
				'options'   => $this->filters,
				'prefix' 	=> $this->prefix,
		));
		?>
		<script>
            var gjmMapArgs = <?php print json_encode( $gjmMapArgs ); ?>;

            console.log(gjmMapArgs);
            if ( jQuery('.gjm-map-wrapper, .grm-map-wrapper ').is(':hidden') ) {
        		jQuery('.gjm-map-wrapper, .grm-map-wrapper').slideToggle(function() {
        			gjmMapInit(gjmMapArgs);
        		});
        	} else {
        		gjmMapInit(gjmMapArgs);
        	}
        </script>
		<?php

		//remove this action
		remove_action( 'the_post', array( $this, 'the_post' 				 ) );
		remove_action( 'loop_end', array( $this, 'pass_values_to_javascript' ) );
	}
	
	/**
	 * The post
	 * modify each post in the loop with information that will be added
	 * to map markers
	 */
	function  the_post() {
		global $post;
	
		$post->url 		   = get_permalink( $post->ID );
		$post->info_window = $this->info_window( 'jobs', $post );
	}
	
	/**
	 * Create the content of the info-window
	 * @param unknown_type $post
	 */
	public function info_window( $type, $post ) {
	
		$address  = ( !empty( $post->formatted_address ) ) ? $post->formatted_address : $post->address;
		$job_type_slug = $job_type_name = '';
	
		if ( get_the_job_type( $post ) ) {
			$job_type_slug = sanitize_title( get_the_job_type( $post )->slug );
			$job_type_name =  sanitize_title( get_the_job_type( $post )->name );
		}
	
		$output['div_start']  = '<div id="gjm-'.$type.'-info-window-wrapper-'.$post->ID.'" class="gjm-info-window-wrapper gjm-'.$type.'-info-window-wrapper">';
	
		if ( isset( $post->distance ) ) {
			$output['distance']= '<span class="distance">'.$post->distance.'</span>';
		}
	
		$output['title'] 		= '<h3 class="title"><a href="'.$post->url.'" title="'.$post->post_title.'">'.$post->post_title.'</a></h3>';
		$output['items_start']  = '<ul class="job-items">';
		$output['company'] 		= '<li class="compny-name"><span class="label">'.$this->form_labels['company'].' </span><span class="item">'.the_company_name( '<span class="company-name">', '</span>', false, $post ).'</span></li>';
		$output['address'] 		= '<li class="address"><span class="label">'.$this->form_labels['address'].' </span><span class="item">'.$address.'</span></li>';
		$output['job_types'] 	= '<li class="job-type '.$job_type_slug.'"><span class="label">'.$this->form_labels['job_type'].' </span><span class="item">'.$job_type_name.'</span></li>';
		$output['posted'] 		= '<li class="date"><span class="item"><date>'.sprintf( $this->form_labels['posted'], human_time_diff( get_post_time( 'U' ), current_time( 'timestamp' ) ) ).'</date></li>';
		$output['items_end'] 	= '</ul>';
		$output['div_end'] 		= '</div>';
		
		$output = apply_filters( 'gjm_'.$type.'_info_window_content', $output, $post );
	
		return implode( '', $output );
	}
	
	/**
	 * GJM Function - add distance value to results
	 * @since  1.0
	 * @author Eyal Fitoussi
	 */
	public function job_distance( $location, $post ) {

		if ( !isset( $this->filters ) )
			return $location;

		if ( $this->filters[$this->prefix.'_use'] == 0 || $this->filters[$this->prefix.'_distance'] != 1 || empty( $this->filters['search_location'] ) || $this->filters['search_location'] == 'Any Location' || $this->filters['search_location'] == 'Location' )
			return $location;

		if ( $post->distance == '' ) 
			return $location;
		
		$units = ( $this->filters[$this->prefix.'_units'] == 'imperial' ) ? $this->form_labels['mi'] : $this->form_labels['km'];

		return apply_filters( $this->prefix.'_add_distance_to_results', $location . '<span class="'.$this->prefix.'-distance-wrapper">' . $post->distance . ' ' . $units . '</span>', $location, $post, $this->filters );
	}

	/**
	 * Global Map 
	 * @param unknown_type $args
	 * @return void|string
	 */
	public function global_map( $args ) {
		
		//default shortcode attributes
		extract(
				shortcode_atts(
						array(
								'map_height'      => '250px',
								'map_width'       => '250px',
								'map_type'        => 'ROADMAP',
								'jobs_count'	  => 200,
								'resumes_count'	  => 200,
								'marker_cluster'  => 1,
								'scroll_wheel'	  => 1
						), $args )
		);

		$count    = ( $this->prefix == 'gjm' ) ? $jobs_count : $resumes_count;
		$postType = ( $this->prefix == 'gjm' ) ? 'job_listing' : 'resume';

		$queryArgs = array(
				'post_type'           => $postType,
				'post_status'         => 'publish',
				'ignore_sticky_posts' => 1,
				'posts_per_page'      => $count,
				'tax_query'           => array(),
				'meta_query'          => array()
		);

		add_filter( 'posts_clauses', array( $this, 'map_clauses' ) );
		
		$results = new WP_Query( apply_filters( $this->prefix.'_jobs_map_args' ,$queryArgs, $args ) );

		remove_filter( 'posts_clauses', array( $this, 'map_clauses' ) );
				
		if ( empty( $results->posts ) )
			return;
		
		$map  = '';
		$map .= '<div class="'.$this->prefix.'-gmap-wrapper" style="width:' . $map_width . '; height:' . $map_height . '">';
		$map .= '<div id="'.$this->prefix.'-gmap-expand-map-trigger" class="'.$this->prefix.'-gmap-expand-map-trigger dashicons dashicons-editor-expand" style="display:none;" title="'.$this->form_labels['resize_map'] .'"></div>';
		$map .= 	'<div id="'.$this->prefix.'-gmap" class="gjm-map" style="width:100%; height:100%;"></div>';
		$map .= '</div>';
		
		if ( $marker_cluster == 1 ) {
			wp_enqueue_script( 'gmw-marker-clusterer');
		}
				
		foreach ( $results->posts as $key => $post ) {
			global $post;
			$results->posts[$key]->url 			= get_permalink( $post->ID );
			$results->posts[$key]->info_window  = $this->info_window( 'gmap', $post );
		}
		
		?>
        <script>

		jQuery(document).ready(function($) {

			var gjmGmIw, i;
			var jobsMapMarkers = [];
			var jobsLocation   = <?php print json_encode( $results->posts ); ?>;
			gjmJobsMap 	       = new google.maps.Map(document.getElementById('<?php echo $this->prefix; ?>-gmap'), {
				zoom: 10,
				scrollwheel: <?php echo $scroll_wheel; ?>,
				mapTypeId: google.maps.MapTypeId['<?php echo $map_type; ?>'],
			});

			//create map expand toggle
			setTimeout(function() { 
				ExpandControl = document.getElementById('<?php echo $this->prefix; ?>-gmap-expand-map-trigger');
				gjmJobsMap.controls[google.maps.ControlPosition.TOP_RIGHT].push(ExpandControl);			
				ExpandControl.style.display = 'block';
			}, 1000);
			
			//expand map function
		    if ( jQuery('.<?php echo $this->prefix; ?>-gmap-wrapper  #<?php echo $this->prefix; ?>-gmap-expand-map-trigger').length ) {
		    	
		    	jQuery('.<?php echo $this->prefix; ?>-gmap-wrapper  #<?php echo $this->prefix; ?>-gmap-expand-map-trigger').click(function() {

		    		var center = gjmJobsMap.getCenter();
		    		jQuery(this).closest('.<?php echo $this->prefix; ?>-gmap-wrapper').toggleClass('<?php echo $this->prefix; ?>-gmap-expanded-map');          		
		    		jQuery(this).toggleClass('dashicons-editor-expand').toggleClass('dashicons-editor-contract');
		    		
		    		//setTimeout(function() { 
		    			google.maps.event.trigger(gjmJobsMap, 'resize');

		    			gjmJobsMap.setCenter(center);
				    //}, 200);            		
		    	});
		    }
		    
			var latlngbounds = new google.maps.LatLngBounds();

            for (i = 0; i < jobsLocation.length; i++) {

				//make sure job has geolocation. Only then it can be displayed on the map
				var jobLocation = new google.maps.LatLng(jobsLocation[i]['lat'], jobsLocation[i]['long']);
		                	
				latlngbounds.extend(jobLocation);
              	
				jobsMapMarkers[i] = new google.maps.Marker({
					position : jobLocation,
					id 		 : i,
  					map 	 : gjmJobsMap
				}); 
				
				with ({ jobMarker: jobsMapMarkers[i] }) {

					//show info window on mouseover
					google.maps.event.addListener( jobMarker, 'mouseover', function() {		

						if (gjmGmIw) {
							gjmGmIw.close();
							gjmGmIw = null;
						}

						gjmGmIw = new google.maps.InfoWindow({
							content: jobsLocation[jobMarker.id]['info_window'],
						});
						gjmGmIw.open( gjmJobsMap, jobMarker );
					});
									
					//link to user profile on marker click
					google.maps.event.addListener(jobMarker, 'click', function() {
						window.location = jobsLocation[jobMarker.id]['url'];
					});
				};
  		    }

           	if ('<?php echo $marker_cluster; ?>' == 1 ) {
            	var markerCluster = new MarkerClusterer( gjmJobsMap, jobsMapMarkers );
           	}
            gjmJobsMap.fitBounds(latlngbounds);
		});

        </script>
        <?php
        return $map;
	}
	
	public function results_map() {
		return '<div id="gjm-results-map-holder"></div>';
	}
	
	/**
	 * Global Map clauses
	 * @param unknown_type $clauses
	 */
	public function map_clauses( $clauses ) {
		
		global $wpdb;
		
		// join the location table into the query
		$clauses['join']   .= " INNER JOIN " . $wpdb->prefix . "places_locator gmwlocations ON $wpdb->posts.ID = gmwlocations.post_id ";
		$clauses['fields']  = "$wpdb->posts.*, gmwlocations.lat, gmwlocations.long, gmwlocations.address, gmwlocations.formatted_address ";
		$clauses['where']  .= " AND ( gmwlocations.lat != '0.000000' AND gmwlocations.long != '0.000000' ) ";
		
		return apply_filters( $this->prefix.'_global_map_query_clauses', $clauses );	
	}
	
	/**
	 * Single job map
	 */
	public function single_page_map() {

		if ( apply_filters( 'gjm_single_map_exists', false ) == true )
			return;
		
		if ( !isset( $this->settings['single_page'][$this->prefix.'_map'] ) || $this->settings['single_page'][$this->prefix.'_map'] == 0 )
			return;

		//disable th creation of a map after first map is displayed
		add_filter( 'gjm_single_map_exists', create_function( '' , 'return true;' ) );
		
		global $post, $wpdb;
		
		$gjmLocation = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}places_locator WHERE post_id = {$post->ID}");

		if ( empty( $gjmLocation ) )
			return;
		
		$lat = $gjmLocation->lat;
		$lng = $gjmLocation->long;

		//if post doen not have coordinates stop the function
		if ( !isset( $lat ) || !isset( $lng ) )
			return;

		//create map element
		$map  = '<div class="'.$this->prefix.'-map-wrapper '.$this->prefix.'-single-map-wrapper" style="width:' . $this->settings['single_page'][$this->prefix.'_map_width'] . ';height:' . $this->settings['single_page'][$this->prefix.'_map_height'] . ';">';
		$map .= 	'<div class="'.$this->prefix.'-map '.$this->prefix.'-single-map" id="'.$this->prefix.'-single-map" style="width:100%; height:100%"></div>';
		$map .= '</div>';

		echo $map;
		wp_enqueue_style( 'gjm-frontend-style' );
		
		echo "<script>
			jQuery(document).ready(function() {
	            var latLng = new google.maps.LatLng('{$lat}', '{$lng}');
	            clMap = new google.maps.Map(document.getElementById('{$this->prefix}-single-map'), {
	                scrollwheel: '{$this->settings['single_page'][$this->prefix.'_scroll_wheel']}',
	                zoom: 12,
	                mapTypeId: google.maps.MapTypeId['{$this->settings['single_page'][$this->prefix.'_map_type']}'],
	                center: latLng
	            });	
	            marker = new google.maps.Marker({
	                position: latLng,
	                map: clMap,
	            });
			});
        </script>";
	}
	
	/**
	 * add new job to GJM table in database
	 * @since  1.0
	 * @author Eyal Fitoussi
	 */
	public function frontend_new_location( $post_id, $values ) {

		$geolocated = get_post_meta( $post_id, 'geolocated', true );
		
		//delete location if address field empty
		if ( empty( $values['job']['job_location'] ) || !isset( $geolocated ) || $geolocated != 1  ) {
			global $wpdb;
			$wpdb->query( $wpdb->prepare( "DELETE FROM " . $wpdb->prefix . "places_locator WHERE post_id=%d", $post_id ) );
			return;
		
		} else {
		
			$street_number = stripslashes( get_post_meta( $post_id, 'geolocation_street_number', true ) );
			$street_name   = stripslashes( get_post_meta( $post_id, 'geolocation_street', true ) );
			$street_check  = trim( $street_number.' '.$street_name );
			$street		   = ( !empty( $street_check ) ) ? $street_number.' '.$street_name : '';
			
			//Save location information to database
			global $wpdb;
			$wpdb->replace( $wpdb->prefix . 'places_locator', array(
					'post_id'           => $post_id,
					'feature'           => 0,
					'post_type'         => 'job_listing',
					'post_title'        => $values['job']['job_title'],
					'post_status'       => 'publish',
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
					'address' 			=> stripslashes( $values['job']['job_location'] ),
					'formatted_address' => stripslashes( get_post_meta( $post_id, 'geolocation_formatted_address', true ) ),
					'phone'             => '',
					'fax'               => '',
					'email'             => $values['job']['application'],
					'website'           => ( isset( $values['job']['company_website'] ) ) ? $values['job']['company_website'] : '',
					'lat'               => get_post_meta( $post_id, 'geolocation_lat',  true ),
					'long'              => get_post_meta( $post_id, 'geolocation_long', true ),
					'map_icon'          => '_default.png',
			)
			);
		}
	}
}