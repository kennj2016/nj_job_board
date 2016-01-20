<?php
/*
 Plugin Name: GEO Job Manager
 Plugin URI: http://www.geomywp.com/add-ons/geo-job-manager
 Description: Add Geo-location search functionality to WP Job Manager plugin
 Author: Eyal Fitoussi
 Version: 1.6.2.3
 Author URI: http://www.geomywp.com
 Text Domain: GJM
 Domain Path: /languages/
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * GEO_Job_Manager class.
 */
class GEO_Job_Manager {

	/**
	 * @var GEO Job Manager
	 * @since 1.1.1
	 */
	private static $instance;

	/**
	 *
	 * @var type
	 * @since 1.1.1
	 */
	public $functions;

	/**
	 * Main Instance
	 *
	 * Insures that only one instance of GEO_Job_Managers exists in memory at any one
	 * time.
	 *
	 * @since 1.1.1
	 * @static
	 * @staticvar array $instance
	 * @return GEO_Job_Manager
	 */
	public static function instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof GEO_Job_Manager ) ) {

			self::$instance = new GEO_Job_Manager;
			self::$instance->constants();
			self::$instance->includes();
			self::$instance->actions();
			self::$instance->functions = new GJM_Query_class();
			self::$instance->license_updater();	
		}
		return self::$instance;
	}

	/**
	 * A dummy constructor to prevent GEO Job Manager from being loaded more than once.
	 *
	 * @since 1.1.1
	 */
	private function __construct() {}

	/**
	 * Setup plugin constants
	 *
	 * @access private
	 * @since 1.1.1
	 * @return void
	 */
	public function constants() {

		define(	'GJM_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
		define( 'GJM_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
		define( 'GJM_ITEM_NAME', 'Geo Job Manager' );
		define( 'GJM_LICENSE_NAME', 'geo_job_manager' );
		define( 'GJM_VERSION', '1.6.2.3' );
		define( 'GJM_DB_VERSION', '1.2' );
		define( 'GJM_FILE', __FILE__ );

		if ( !defined( 'GMW_REMOTE_SITE_URL' ) ) {
			define( 'GMW_REMOTE_SITE_URL', 'https://geomywp.com' );
		}
	}

	/**
	 * Include file
	 *
	 * @since 1.1.1
	 *
	 */
	private function includes() {

		//main functions file
		include_once GJM_PATH . '/includes/gjm-functions.php';

		//admin files
		if ( is_admin() && !defined( 'DOING_AJAX' ) ) {
			
			include_once GJM_PATH . '/includes/admin/gjm-admin.php';
			include_once GJM_PATH . '/includes/admin/gjm-admin-settings.php';
			include_once GJM_PATH . '/includes/admin/gjm-db.php';
			
			//create custom table in database. Include the files only if GEO my WP is not installed.
			if ( !class_exists( 'GEO_my_WP' ) && !class_exists( 'gmw_loaded' ) ) {
				include_once GJM_PATH . '/updater/geo-my-wp-updater.php';
				include_once GJM_PATH . '/updater/geo-my-wp-license-handler.php';
			}
		}
	}

	/**
	 * Fire some hooks
	 *
	 * @since 1.1.1
	 *
	 */
	private function actions() {

		// init add-on		
		add_action( 'wp_enqueue_scripts', 							 array( $this, 'register_scripts' ) );
		add_action( 'admin_enqueue_scripts', 						 array( $this, 'register_scripts' ) );
		add_action( 'after_plugin_row_'.plugin_basename( __FILE__ ), array( $this, 'license_key_input' ) );
		
		//only with GEO my WP
		add_filter( 'gmw_admin_addons_page', array( $this, 'addon_init' ) );		
	}
	
	/**
	 * Include addon function.
	 *
	 * @since 1.1
	 * @access public
	 * @return $addons
	 */
	public function addon_init( $addons ) {
		
		$addons[GJM_LICENSE_NAME] = array(
				'name' 	  	=> GJM_LICENSE_NAME,
				'title'   	=> 'GEO Job Manager',
				'version' 	=> GJM_VERSION,
				'item'	  	=> GJM_ITEM_NAME,
				'file' 	  	=> GJM_FILE,
				'author'  	=> 'Eyal Fitoussi',
				'desc'    	=> __( 'Provides GeoLocation features to WP Job Manager plugin.', 'GJM' ),
				'license' 	=> true,
				'image'  	=> false,
				'require' 	=> array(
						'WP Job Manager plugin' => array( 'plugin_file' => 'wp-job-manager/wp-job-manager.php', 'link' => 'http://wordpress.org/plugins/wp-job-manager/' )
				)				
		);
		return $addons;
	}
	
	/**
	 * add license key input field to plugins page
	 * 
	 * @since 2.5
	 * @author Eyal Fitoussi
	 */
	public function license_key_input() {		
		if ( class_exists( 'GMW_License_Key' ) ) {
			$gjm_license_key = new GMW_License_Key( __FILE__, GJM_ITEM_NAME, GJM_LICENSE_NAME );
			$gjm_license_key->license_key_output();	 
		}	
	}
	
	/**
	 *  Check for license updates
	 *  
	 */
	protected function license_updater() {
			 
    	if ( class_exists( 'GMW_License' ) ) {
    		$gjm_license = new GMW_License( __FILE__, GJM_ITEM_NAME, GJM_LICENSE_NAME, GJM_VERSION, 'Eyal Fitoussi' );
    	}
    		 
    	if ( class_exists( 'GEO_my_WP' ) && GMW_VERSION < '2.5' ) {
    		  		
	        //check license key
	        $gmw_license_keys = get_option( 'gmw_license_keys' );
	
	        if ( isset( $gmw_license_keys[GJM_LICENSE_NAME] ) && class_exists( 'GMW_Premium_Plugin_Updater' ) ) {
	
	            $license = trim( $gmw_license_keys[GJM_LICENSE_NAME] );
	
	            $gmw_updater = new GMW_Premium_Plugin_Updater( GMW_REMOTE_SITE_URL, __FILE__, array(
	                'version'   => GJM_VERSION,
	                'license'   => $license,
	                'item_name' => GJM_ITEM_NAME,
	                'author'    => 'Eyal Fitoussi',
	                'url'       => home_url()
	            ) );
	        }
    	}
    }

	/**
	 * register scripts function.
	 *
	 * @access public
	 * @return void
	 */
	public function register_scripts() {
		
		$options  = get_option('gjm_options');
		$language = ( !empty( $options['language'] ) ) ? '&language='.$options['language'] : '';
		$region   = ( !empty( $options['region'] ) ) ? '&region='.$options['region'] : '';
		
		if ( !class_exists( 'GEO_my_WP' ) || GMW_VERSION < '2.5' ) {
		
			//register google maps api
	        if ( !wp_script_is( 'google-maps', 'registered' ) ) {
	            wp_register_script( 'google-maps', ( is_ssl() ? 'https' : 'http' ) . '://maps.googleapis.com/maps/api/js?sensor=false&libraries=places'.$language, array( 'jquery' ), false );
	    	}
	    	
	        //register google maps api
	        if ( !wp_script_is( 'google-maps', 'enqueued' ) ) {
	        	wp_enqueue_script( 'google-maps' );
	    	}

			if ( !is_admin() && !wp_script_is( 'gmw-marker-clusterer', 'registered' ) ) {
				wp_register_script( 'gmw-marker-clusterer', GJM_URL . '/assets/js/marker-clusterer.min.js', array( 'jquery' ), GJM_VERSION, true );
			}
			$cluster_image = ( is_ssl() ? 'https' : 'http' ).'://google-maps-utility-library-v3.googlecode.com/svn/trunk/markerclustererplus/images/m';
			wp_localize_script( 'gmw-marker-clusterer', 'clusterImage', $cluster_image );
			
			if ( !wp_script_is( 'dashicons', 'enqueued' ) ) {
				wp_enqueue_style( 'dashicons' );
			}
			
			if ( is_admin() ) {
				wp_enqueue_style( 'gjm-backend-style', GJM_URL . '/updater/assets/css/style-admin.css' );
			}
		}
				
		wp_register_script( 'gjm-autocomplete', GJM_URL .'/assets/js/autocomplete.min.js', array( 'jquery' ), GJM_VERSION, true );
		wp_register_script( 'gjm-map', 			GJM_URL .'/assets/js/map.min.js',	   array( 'jquery', 'wp-job-manager-ajax-filters' ), GJM_VERSION, true );
		
		if ( function_exists( 'jobify_setup') ) {
			wp_enqueue_style( 'gjm-frontend-style', GJM_URL . '/assets/css/frontend.css', array( 'jobify-parent') );
		} elseif ( function_exists( 'listify_setup') ) {
			wp_enqueue_style( 'gjm-frontend-style', GJM_URL . '/assets/css/frontend.css', array( 'listify') );
		} else {
			wp_enqueue_style( 'gjm-frontend-style', GJM_URL . '/assets/css/frontend.css' );
		}		
	}
}

/**
 *  GJM Instance
 *
 * @since 1.1.1
 * @return GEO Job Manager Instance
 */
function GJM() {

	load_plugin_textdomain( 'GJM', FALSE, dirname( plugin_basename(__FILE__)).'/languages/' );
	
	//make sure that WP Job Manager is activated
	if ( !class_exists( 'WP_Job_Manager') || JOB_MANAGER_VERSION < '1.21.3' ) {
		function gjm_deactivated_admin_notice() {
			?>
		<div class="error">
			<p>
				GEO Job Manager <?php printf( __( "requires <a %s>WP Job Manager</a> plugin version 1.21.3 or higher in order to work.", "GJM" ), 'href=\"http://wordpress.org/plugins/wp-job-manager/\" target=\"_blank\"' ); ?>
			</p>
		</div>
		<?php       
		}
		return add_action( 'admin_notices', 'gjm_deactivated_admin_notice' );
	}
	return GEO_Job_Manager::instance();
}
// Get GJM Running
add_action( 'plugins_loaded', 'GJM' );