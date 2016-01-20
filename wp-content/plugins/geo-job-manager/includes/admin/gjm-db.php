<?php
if ( !defined( 'ABSPATH' ) ) exit;

global $wpdb;
$ptTable = $wpdb->get_results( "SHOW TABLES LIKE '{$wpdb->prefix}places_locator'", ARRAY_A );

//create or update database
if ( get_option( "gmw_pt_db_version" ) == '' || get_option( "gmw_pt_db_version" ) != GJM_DB_VERSION || count($ptTable) == 0 ) {
 
	//create table
	if ( count( $ptTable ) == 0 ) {
		gjm_db_installation();

		//update database version
		update_option( "gmw_pt_db_version", GJM_DB_VERSION );
		
	//update table
	} elseif ( count( $ptTable ) == 1 ) {
		gjm_db_update();
	}	
}

function gjm_db_installation() {

	global $wpdb;

	$gmw_sql = "CREATE TABLE {$wpdb->prefix}places_locator (
	`post_id` 			bigint(30) NOT NULL,
	`feature`			tinyint NOT NULL default '0',
	`post_status` 		varchar(20) NOT NULL ,
	`post_type` 		varchar(20) default 'post',
	`post_title` 		TEXT,
	`lat` 				float(10,6) NOT NULL,
	`long` 				float(10,6) NOT NULL,
	`street_number` 	varchar(60) NOT NULL,
	`street_name` 		varchar(128) NOT NULL,
	`street` 			varchar(128) NOT NULL,
	`apt` 				varchar(50) NOT NULL,
	`city` 				varchar(128) NOT NULL,
	`state` 			varchar(50) NOT NULL,
	`state_long` 		varchar(128) NOT NULL,
	`zipcode` 			varchar(40) NOT NULL,
	`country` 			varchar(50) NOT NULL,
	`country_long` 		varchar(128) NOT NULL,
	`address` 			varchar(255) NOT NULL,
	`formatted_address` varchar(255) NOT NULL,
	`phone` 			varchar(50) NOT NULL,
	`fax` 				varchar(50) NOT NULL,
	`email` 			varchar(255) NOT NULL,
	`website` 			varchar(255) NOT NULL,
	`map_icon`			varchar(50) NOT NULL,
	UNIQUE KEY id (post_id)

	)	DEFAULT CHARSET=utf8;";
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($gmw_sql);
}

function gjm_db_update() {

	if ( get_option( "gmw_pt_db_version" ) == '1.1' ) {

		global $wpdb;
		
		$dbTable = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}places_locator");
		
		//Add column if not present.
		if ( !isset( $dbTable->street_number ) ) {
			
			$wpdb->query("ALTER TABLE {$wpdb->prefix}places_locator ADD COLUMN `street_name` varchar(128) NOT NULL AFTER `long`");
			$wpdb->query("ALTER TABLE {$wpdb->prefix}places_locator ADD COLUMN `street_number` varchar(60) NOT NULL AFTER `long`");
			
			//update database version
			update_option( "gmw_pt_db_version", GJM_DB_VERSION );
		} else {
			update_option( "gmw_pt_db_version", GJM_DB_VERSION );
		}
	}
}
?>