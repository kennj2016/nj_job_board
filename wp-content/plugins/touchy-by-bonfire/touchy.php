<?php
/*
Plugin Name: Touchy, by Bonfire 
Plugin URI: http://bonfirethemes.com/
Description: A mobile menu for WordPress.
Version: 1.5
Author: Bonfire Themes
Author URI: http://bonfirethemes.com/
License: GPL2
*/


	//
	// CREATE THE SETTINGS PAGE (for WordPress backend, Settings > touchy plugin)
	//
	
	/* create "Settings" link on plugins page */
	function bonfire_touchy_settings_link($links) { 
		$settings_link = '<a href="options-general.php?page=touchy-by-bonfire/touchy.php">Settings</a>'; 
		array_unshift($links, $settings_link); 
		return $links; 
	}
	$plugin = plugin_basename(__FILE__); 
	add_filter("plugin_action_links_$plugin", 'bonfire_touchy_settings_link' );

	/* create the "Settings > touchy plugin" menu item */
	function bonfire_touchy_admin_menu() {
		add_submenu_page('options-general.php', 'Touchy Plugin Settings', 'Touchy plugin', 'administrator', __FILE__, 'bonfire_touchy_page');
	}
	
	/* create the actual settings page */
	function bonfire_touchy_page() {
		if ( isset ($_POST['update_bonfire_touchy']) == 'true' ) { bonfire_touchy_update(); }
	?>

		<div class="wrap">
			<h2>Touchy, by Bonfire</h2>
			<strong>Psst!</strong> touchy's color options can be changed under <strong>Appearance > Customize > Touchy plugin colors</strong>

			<form method="POST" action="">
				<input type="hidden" name="update_bonfire_touchy" value="true" />

				<br><hr><br>

				<table class="form-table">
					<tr valign="top">
					<th scope="row">Absolute/fixed positioning</th>
					<td><label><input type="checkbox" name="touchy_absolute" id="touchy_absolute" <?php echo get_option('touchy_position_absolute'); ?> /> Absolute positioning (Touchy leaves the screen when scrolled).
					<br>If unticked, Touchy will have fixed positioning and will remain at the top at all times.
					<br><strong>Please note:</strong> Do not tick if you select the bottom placement below.</label></td>
					</tr>
					
					<tr valign="top">
					<th scope="row">Top/bottom positioning</th>
					<td><label><input type="checkbox" name="touchy_bottom" id="touchy_bottom" <?php echo get_option('touchy_position_bottom'); ?> /> Bottom positioning (Touchy is placed at the bottom of the screen).
					<br>If unticked, Touchy will have its default top position.</label></td>
					</tr>
					
					<tr valign="top">
					<th scope="row">Show only on touch devices?</th>
					<td><label><input type="checkbox" name="touchy_show_mobile_only" id="touchy_show_mobile_only" <?php echo get_option('touchy_mobile_only'); ?> /> Show Touchy only on touch devices </label></td>
					</tr>
			
					<tr valign="top">
					<th scope="row">Hide back button?</th>
					<td><label><input type="checkbox" name="touchy_hide_back" id="touchy_hide_back" <?php echo get_option('touchy_hide_back_button'); ?> /> Hide the back button on all pages</label></td>
					</tr>
			
					<tr valign="top">
					<th scope="row">Phone number:</th>
					<td>
					<input type="text" name="touchy_enter_phone_number" id="touchy_enter_phone_number" value="<?php echo get_option('touchy_phone_number'); ?>"/>
					<label><input type="checkbox" name="touchy_hide_call" id="touchy_hide_call" <?php echo get_option('touchy_hide_call_button'); ?> /> Hide call button</label>
					</td>
					</tr>
			
					<tr valign="top">
					<th scope="row">Email address:</th>
					<td>
					<input type="text" name="touchy_email_address" id="touchy_email_address" value="<?php echo get_option('touchy_email'); ?>"/>
					<label><input type="checkbox" name="touchy_hide_email" id="touchy_hide_email" <?php echo get_option('touchy_hide_email_button'); ?> /> Hide email button</label>
					</td>
					</tr>
					
					<tr valign="top">
					<th scope="row">Transparency</th>
					<td>
					<input type="text" name="touchy_transparency" id="touchy_transparency" value="<?php echo get_option('touchy_set_transparency'); ?>"/> From 0-1. Example: 0.8 or 0.85. If left emtpy, defaults to 1.
					<br>You'll probably want to keep this at .95 and above, to have that very subtle see-through effect. Depending on how you color customize your menu though, you could go lower.
					</td>
					</tr>
				</table>

				<br><br>
				<h3>Customize icons</h3>
				<hr>
				To customize the four menubar icons, enter new icon names into the fields below.<br>
				You can pick and choose from over 300 icons here: <a target="_blank" href="http://fortawesome.github.io/Font-Awesome/cheatsheet/">http://fortawesome.github.io/Font-Awesome/cheatsheet/</a> (the icon names are <strong>fa-angle-up</strong>, <strong>fa-anchor</strong> etc.).<br>
				If a field is left empty, the default icon will be used.<br><br>

				<table class="form-table">
					<tr valign="top">
					<th scope="row">Custom back icon:</th>
					<td>
					<input type="text" name="touchy_custom_back" id="touchy_custom_back" value="<?php echo get_option('touchy_custom_back_icon'); ?>"/> If left empty, defaults to <strong>fa-long-arrow-left</strong>
					</td>
					</tr>
					
					<tr valign="top">
					<th scope="row">Custom call icon:</th>
					<td>
					<input type="text" name="touchy_custom_call" id="touchy_custom_call" value="<?php echo get_option('touchy_custom_call_icon'); ?>"/> If left empty, defaults to <strong>fa-phone</strong>
					</td>
					</tr>
					
					<tr valign="top">
					<th scope="row">Custom email icon:</th>
					<td>
					<input type="text" name="touchy_custom_email" id="touchy_custom_email" value="<?php echo get_option('touchy_custom_email_icon'); ?>"/> If left empty, defaults to <strong>fa-envelope</strong>
					</td>
					</tr>
					
					<tr valign="top">
					<th scope="row">Custom menu icon:</th>
					<td>
					<input type="text" name="touchy_custom_menu" id="touchy_custom_menu" value="<?php echo get_option('touchy_custom_menu_icon'); ?>"/> If left empty, defaults to <strong>fa-bars</strong>
					</td>
					</tr>
				</table>
				
				<br><br>
				<h3>Customize button links</h3>
				<hr>
				If you'd like to override the call and email button functions with custom links, enter them below. Combined with custom icons (which can be set above), this allows you to give these two buttons a completely different function.<br><br>

				<table class="form-table">
					<tr valign="top">
					<th scope="row">Custom call button link:</th>
					<td>
					<input type="text" name="touchy_custom_call_link" id="touchy_custom_call_link" value="<?php echo get_option('bonfire_touchy_custom_call_link'); ?>"/> If left empty, defaults to call function
					</td>
					</tr>
					
					<tr valign="top">
					<th scope="row">Custom button link:</th>
					<td>
					<input type="text" name="touchy_custom_email_link" id="touchy_custom_email_link" value="<?php echo get_option('bonfire_touchy_custom_email_link'); ?>"/> If left empty, defaults to email function
					</td>
					</tr>
				</table>

				<br><hr><br>

				<!-- BEGIN 'SAVE OPTIONS' BUTTON -->	
				<p><input type="submit" name="search" value="Save Options" class="button button-primary" /></p>
				<!-- BEGIN 'SAVE OPTIONS' BUTTON -->	

			</form>

		</div>
	<?php }
	function bonfire_touchy_update() {

		/* absolute/fixed positioning */
		if ( isset ($_POST['touchy_absolute'])=='on') { $display = 'checked'; } else { $display = ''; }
	    update_option('touchy_position_absolute', $display);

		/* top/bottom positioning */
		if ( isset ($_POST['touchy_bottom'])=='on') { $display = 'checked'; } else { $display = ''; }
	    update_option('touchy_position_bottom', $display);

		/* show on touch devices only */
		if ( isset ($_POST['touchy_show_mobile_only'])=='on') { $display = 'checked'; } else { $display = ''; }
	    update_option('touchy_mobile_only', $display);
		
		/* hide back button */
		if ( isset ($_POST['touchy_hide_back'])=='on') { $display = 'checked'; } else { $display = ''; }
	    update_option('touchy_hide_back_button', $display);
		
		/* enter phone number */
		update_option('touchy_phone_number',   $_POST['touchy_enter_phone_number']);
		/* hide call button */
		if ( isset ($_POST['touchy_hide_call'])=='on') { $display = 'checked'; } else { $display = ''; }
	    update_option('touchy_hide_call_button', $display);
		
		/* enter email address */
		update_option('touchy_email',   $_POST['touchy_email_address']);
		/* hide email button */
		if ( isset ($_POST['touchy_hide_email'])=='on') { $display = 'checked'; } else { $display = ''; }
	    update_option('touchy_hide_email_button', $display);
		
		/* menu transparency */
		update_option('touchy_set_transparency',   $_POST['touchy_transparency']);
		
		/* custom back icon */
		update_option('touchy_custom_back_icon',   $_POST['touchy_custom_back']);
		/* custom call icon */
		update_option('touchy_custom_call_icon',   $_POST['touchy_custom_call']);
		/* custom email icon */
		update_option('touchy_custom_email_icon',   $_POST['touchy_custom_email']);
		/* custom menu icon */
		update_option('touchy_custom_menu_icon',   $_POST['touchy_custom_menu']);

		/* custom call button link */
		update_option('bonfire_touchy_custom_call_link',   $_POST['touchy_custom_call_link']);
		/* custom email button link */
		update_option('bonfire_touchy_custom_email_link',   $_POST['touchy_custom_email_link']);

	}
	add_action('admin_menu', 'bonfire_touchy_admin_menu');
	?>
<?php


	//
	// Add menu to theme
	//
	
	function bonfire_touchy_footer() {
	?>

		<!-- BEGIN PREVENT TOUCHSTART MISHAPS -->
		<meta name="viewport" content="initial-scale=1.0, user-scalable=no">
		<!-- END PREVENT TOUCHSTART MISHAPS -->
		
		<?php if( get_option('touchy_mobile_only') ) { ?>
		
			<?php if ( wp_is_mobile() ) { ?>
				<?php if( get_option('touchy_position_bottom') ) { ?>
					<style>
					/* add padding to ensure that whatever content may be at the top of the site doesn't get hidden behind the menu */
					html { padding-bottom:50px !important; }
					</style>
				<?php } else { ?>
					<style>
					/* add padding to ensure that whatever content may be at the top of the site doesn't get hidden behind the menu */
					html { margin-top:50px !important; }
					</style>
				<?php } ?>
			<?php } ?>

		<?php } else { ?>
		
			<?php if( get_option('touchy_position_bottom') ) { ?>
				<style>
				/* add padding to ensure that whatever content may be at the top of the site doesn't get hidden behind the menu */
				html { padding-bottom:50px !important; }
				</style>
			<?php } else { ?>
				<style>
				/* add padding to ensure that whatever content may be at the top of the site doesn't get hidden behind the menu */
				html { margin-top:50px !important; }
				</style>
			<?php } ?>
		
		<?php } ?>

<?php if( get_option('touchy_mobile_only') ) { ?>

	<!-- BEGIN SHOW TOUCHY ON MOBILE DEVICES ONLY -->
	<?php if ( wp_is_mobile() ) { ?>

		<!-- BEGIN MENU BAR -->
		<div class="touchy-wrapper<?php if ( is_admin_bar_showing() ) { ?> touchy-wp-toolbar<?php } else { ?><?php } ?><?php if( get_option('touchy_position_absolute') ) { ?> touchy-absolute<?php } ?><?php if( get_option('touchy_position_bottom') ) { ?> touchy-bottom<?php } ?>">
		
			<!-- BEGIN BACK BUTTON -->
			<?php if( get_option('touchy_hide_back_button') ) { ?>
			<?php } else { ?>
				<?php if(is_front_page() ) { ?><?php } else { ?>
					<div class="touchy-back-button" onClick="history.back()">
						<i class="fa <?php if( get_option('touchy_custom_back_icon') ) { ?><?php echo get_option('touchy_custom_back_icon'); ?><?php } else { ?>fa-long-arrow-left<?php } ?>"></i>
					</div>
				<?php } ?>
			<?php } ?>
			<!-- END BACK BUTTON -->
			
			<!-- BEGIN CALL BUTTON -->
			<?php if( get_option('touchy_hide_call_button') ) { ?>
			<?php } else { ?>
				<a href="<?php if( get_option('bonfire_touchy_custom_call_link') ) { ?><?php echo get_option('bonfire_touchy_custom_call_link'); ?><?php } else { ?>tel://<?php echo get_option('touchy_phone_number'); ?><?php } ?>" class="touchy-call-button">
					<i class="fa <?php if( get_option('touchy_custom_call_icon') ) { ?><?php echo get_option('touchy_custom_call_icon'); ?><?php } else { ?>fa-phone<?php } ?>"></i>
				</a>
			<?php } ?>
			<!-- END CALL BUTTON -->
			
			<!-- BEGIN EMAIL BUTTON -->
			<?php if( get_option('touchy_hide_email_button') ) { ?>
			<?php } else { ?>
				<a href="<?php if( get_option('bonfire_touchy_custom_email_link') ) { ?><?php echo get_option('bonfire_touchy_custom_email_link'); ?><?php } else { ?>mailto:<?php echo get_option('touchy_email'); ?><?php } ?>" class="touchy-email-button">
					<i class="fa <?php if( get_option('touchy_custom_email_icon') ) { ?><?php echo get_option('touchy_custom_email_icon'); ?><?php } else { ?>fa-envelope<?php } ?>"></i>
				</a>
			<?php } ?>
			<!-- END EMAIL BUTTON -->
			
			<!-- BEGIN MENU BUTTON -->
			<div class="touchy-menu-button">
				<i class="fa <?php if( get_option('touchy_custom_menu_icon') ) { ?><?php echo get_option('touchy_custom_menu_icon'); ?><?php } else { ?>fa-bars<?php } ?>"></i>
				<div class="touchy-accordion-tooltip"></div>
			</div>
			<!-- END MENU BUTTON -->

		</div>
		<!-- END MENU BAR -->
		
		<!-- BEGIN ACCORDION MENU -->
		<div class="touchy-menu-close"></div>
		<div class="<?php if( get_option('touchy_position_bottom') ) { ?>touchy-bottom<?php } ?>">
		<div class="touchy-by-bonfire<?php if( get_option('touchy_position_absolute') ) { ?> touchy-absolute<?php } ?>">
			<?php wp_nav_menu( array( 'theme_location' => 'touchy-by-bonfire' ) ); ?>
		</div>
		</div>
		<!-- END ACCORDION MENU -->

	<?php } ?>
	<!-- END SHOW TOUCHY ON MOBILE DEVICES ONLY -->
	
<?php } else { ?>

		<!-- BEGIN MENU BAR -->
		<div class="touchy-wrapper<?php if ( is_admin_bar_showing() ) { ?> touchy-wp-toolbar<?php } else { ?><?php } ?><?php if( get_option('touchy_position_absolute') ) { ?> touchy-absolute<?php } ?><?php if( get_option('touchy_position_bottom') ) { ?> touchy-bottom<?php } ?>">
		
			<!-- BEGIN BACK BUTTON -->
			<?php if( get_option('touchy_hide_back_button') ) { ?>
			<?php } else { ?>
				<?php if(is_front_page() ) { ?><?php } else { ?>
					<div class="touchy-back-button" onClick="history.back()">
						<i class="fa <?php if( get_option('touchy_custom_back_icon') ) { ?><?php echo get_option('touchy_custom_back_icon'); ?><?php } else { ?>fa-long-arrow-left<?php } ?>"></i>
					</div>
				<?php } ?>
			<?php } ?>
			<!-- END BACK BUTTON -->
			
			<!-- BEGIN CALL BUTTON -->
			<?php if( get_option('touchy_hide_call_button') ) { ?>
			<?php } else { ?>
				<a href="<?php if( get_option('bonfire_touchy_custom_call_link') ) { ?><?php echo get_option('bonfire_touchy_custom_call_link'); ?><?php } else { ?>tel://<?php echo get_option('touchy_phone_number'); ?><?php } ?>" class="touchy-call-button">
					<i class="fa <?php if( get_option('touchy_custom_call_icon') ) { ?><?php echo get_option('touchy_custom_call_icon'); ?><?php } else { ?>fa-phone<?php } ?>"></i>
				</a>
			<?php } ?>
			<!-- END CALL BUTTON -->
			
			<!-- BEGIN EMAIL BUTTON -->
			<?php if( get_option('touchy_hide_email_button') ) { ?>
			<?php } else { ?>
				<a href="<?php if( get_option('bonfire_touchy_custom_email_link') ) { ?><?php echo get_option('bonfire_touchy_custom_email_link'); ?><?php } else { ?>mailto:<?php echo get_option('touchy_email'); ?><?php } ?>" class="touchy-email-button">
					<i class="fa <?php if( get_option('touchy_custom_email_icon') ) { ?><?php echo get_option('touchy_custom_email_icon'); ?><?php } else { ?>fa-envelope<?php } ?>"></i>
				</a>
			<?php } ?>
			<!-- END EMAIL BUTTON -->
			
			<!-- BEGIN MENU BUTTON -->
			<div class="touchy-menu-button">
				<i class="fa <?php if( get_option('touchy_custom_menu_icon') ) { ?><?php echo get_option('touchy_custom_menu_icon'); ?><?php } else { ?>fa-bars<?php } ?>"></i>
				<div class="touchy-accordion-tooltip"></div>
			</div>
			<!-- END MENU BUTTON -->

		</div>
		<!-- END MENU BAR -->
		
		<!-- BEGIN ACCORDION MENU -->
		<div class="touchy-menu-close"></div>
		<div class="<?php if( get_option('touchy_position_bottom') ) { ?>touchy-bottom<?php } ?>">
		<div class="touchy-by-bonfire<?php if( get_option('touchy_position_absolute') ) { ?> touchy-absolute<?php } ?>">
			<?php wp_nav_menu( array( 'theme_location' => 'touchy-by-bonfire' ) ); ?>
		</div>
		</div>
		<!-- END ACCORDION MENU -->

<?php } ?>
<!-- END SHOW TOUCHY ON MOBILE DEVICES ONLY -->

	<?php
	}
	add_action('wp_head','bonfire_touchy_footer');


	//
	// ENQUEUE touchy.css
	//

	function bonfire_touchy_css() {
	// enqueue touchy.css only on mobile
	if( get_option('touchy_mobile_only') ) {
	if ( wp_is_mobile() ) {
		wp_register_style( 'bonfire-touchy-css', plugins_url( '/touchy.css', __FILE__ ) . '', array(), '1', 'all' );
		wp_enqueue_style( 'bonfire-touchy-css' );
	}
	// enqueue touchy.css everywhere
	} else {
		wp_register_style( 'bonfire-touchy-css', plugins_url( '/touchy.css', __FILE__ ) . '', array(), '1', 'all' );
		wp_enqueue_style( 'bonfire-touchy-css' );
	}
	}
	add_action( 'wp_enqueue_scripts', 'bonfire_touchy_css' );


	//
	// ENQUEUE touchy.js
	//
	
	function bonfire_touchy_js() {
	// enqueue touchy.js only on mobile
	if( get_option('touchy_mobile_only') ) {
	if ( wp_is_mobile() ) {
		wp_register_script( 'bonfire-touchy-js', plugins_url( '/touchy.js', __FILE__ ) . '', array( 'jquery' ), '1', true );  
		wp_enqueue_script( 'bonfire-touchy-js' );  
	}
	// enqueue touchy.js everywhere
	} else {
		wp_register_script( 'bonfire-touchy-js', plugins_url( '/touchy.js', __FILE__ ) . '', array( 'jquery' ), '1', true );  
		wp_enqueue_script( 'bonfire-touchy-js' );
	}
	}
	add_action( 'wp_enqueue_scripts', 'bonfire_touchy_js' );


	//
	// ENQUEUE font-awesome.min.css (icons for menu)
	//
	
	function bonfire_touchy_fontawesome() {
	// enqueue font-awesome.min.css only on mobile
	if( get_option('touchy_mobile_only') ) {
	if ( wp_is_mobile() ) {
		wp_register_style( 'touchy-fontawesome', plugins_url( '/fonts/font-awesome/css/font-awesome.min.css', __FILE__ ) . '', array(), '1', 'all' );  
		wp_enqueue_style( 'touchy-fontawesome' );
	}
	// enqueue font-awesome.min.css everywhere
	} else {
		wp_register_style( 'touchy-fontawesome', plugins_url( '/fonts/font-awesome/css/font-awesome.min.css', __FILE__ ) . '', array(), '1', 'all' );  
		wp_enqueue_style( 'touchy-fontawesome' );
	}
	}
	add_action( 'wp_enqueue_scripts', 'bonfire_touchy_fontawesome' );


	//
	// Enqueue Google WebFonts
	//
	function bonfire_touchy_font() {
	$protocol = is_ssl() ? 'https' : 'http';

	// enqueue google webfonts only on mobile
	if( get_option('touchy_mobile_only') ) {
	if ( wp_is_mobile() ) {
		wp_enqueue_style( 'bonfire-touchy-font', "$protocol://fonts.googleapis.com/css?family=Open+Sans:400' rel='stylesheet' type='text/css" );
	}
	// enqueue google webfonts everywhere
	} else {
		wp_enqueue_style( 'bonfire-touchy-font', "$protocol://fonts.googleapis.com/css?family=Open+Sans:400' rel='stylesheet' type='text/css" );
	}
	}
	add_action( 'wp_enqueue_scripts', 'bonfire_touchy_font' );
	

	//
	// Register Custom Menu Function
	//
	if (function_exists('register_nav_menus')) {
		register_nav_menus( array(
			'touchy-by-bonfire' => ( 'Touchy, by Bonfire' ),
		) );
	}

	//
	// Add color options to Appearance > Customize
	//
	add_action( 'customize_register', 'bonfire_touchy_customize_register' );
	function bonfire_touchy_customize_register($wp_customize)
	{
		$colors = array();
		/* Touchy > BACK button */
		$colors[] = array( 'slug'=>'bonfire_touchy_back_button_background', 'default' => '', 'label' => __( 'Touchy > BACK button background', 'bonfire' ) );
		$colors[] = array( 'slug'=>'bonfire_touchy_back_button_icon', 'default' => '', 'label' => __( 'Touchy > BACK button icon', 'bonfire' ) );
		$colors[] = array( 'slug'=>'bonfire_touchy_back_button_background_hover', 'default' => '', 'label' => __( 'Touchy > BACK button background hover', 'bonfire' ) );
		$colors[] = array( 'slug'=>'bonfire_touchy_back_button_icon_hover', 'default' => '', 'label' => __( 'Touchy > BACK button icon hover', 'bonfire' ) );

		/* Touchy > CALL button */
		$colors[] = array( 'slug'=>'bonfire_touchy_call_button_background', 'default' => '', 'label' => __( 'Touchy > CALL button background', 'bonfire' ) );
		$colors[] = array( 'slug'=>'bonfire_touchy_call_button_icon', 'default' => '', 'label' => __( 'Touchy > CALL button icon', 'bonfire' ) );
		$colors[] = array( 'slug'=>'bonfire_touchy_call_button_background_hover', 'default' => '', 'label' => __( 'Touchy > CALL button background hover', 'bonfire' ) );
		$colors[] = array( 'slug'=>'bonfire_touchy_call_button_icon_hover', 'default' => '', 'label' => __( 'Touchy > CALL button icon hover', 'bonfire' ) );

		/* Touchy > EMAIL button */
		$colors[] = array( 'slug'=>'bonfire_touchy_email_button_background', 'default' => '', 'label' => __( 'Touchy > EMAIL button background', 'bonfire' ) );
		$colors[] = array( 'slug'=>'bonfire_touchy_email_button_icon', 'default' => '', 'label' => __( 'Touchy > EMAIL button icon', 'bonfire' ) );
		$colors[] = array( 'slug'=>'bonfire_touchy_email_button_background_hover', 'default' => '', 'label' => __( 'Touchy > EMAIL button background hover', 'bonfire' ) );
		$colors[] = array( 'slug'=>'bonfire_touchy_email_button_icon_hover', 'default' => '', 'label' => __( 'Touchy > EMAIL button icon hover', 'bonfire' ) );

		/* Touchy > MENU button */
		$colors[] = array( 'slug'=>'bonfire_touchy_menu_button_background', 'default' => '', 'label' => __( 'Touchy > MENU button background', 'bonfire' ) );
		$colors[] = array( 'slug'=>'bonfire_touchy_menu_button_icon', 'default' => '', 'label' => __( 'Touchy > MENU button icon', 'bonfire' ) );
		$colors[] = array( 'slug'=>'bonfire_touchy_menu_button_background_hover', 'default' => '', 'label' => __( 'Touchy > MENU button background hover', 'bonfire' ) );
		$colors[] = array( 'slug'=>'bonfire_touchy_menu_button_icon_hover', 'default' => '', 'label' => __( 'Touchy > MENU button icon hover', 'bonfire' ) );

		/* Touchy > Menubar separator */
		$colors[] = array( 'slug'=>'bonfire_touchy_menubar_separator_color', 'default' => '', 'label' => __( 'Touchy > Menubar separator', 'bonfire' ) );

		/* Touchy > Accordion menu background */
		$colors[] = array( 'slug'=>'bonfire_touchy_accordion_menu_background', 'default' => '', 'label' => __( 'Touchy > Accordion menu background', 'bonfire' ) );

		/* Touchy > Accordion menu item background hover */
		$colors[] = array( 'slug'=>'bonfire_touchy_accordion_menu_item_background_hover', 'default' => '', 'label' => __( 'Touchy > Accordion menu item background hover', 'bonfire' ) );

		/* Touchy > Accordion menu separator */
		$colors[] = array( 'slug'=>'bonfire_touchy_accordion_menu_separator', 'default' => '', 'label' => __( 'Touchy > Accordion menu separator', 'bonfire' ) );
		
		/* Touchy > Accordion sub-menu separator */
		$colors[] = array( 'slug'=>'bonfire_touchy_accordion_submenu_separator', 'default' => '', 'label' => __( 'Touchy > Accordion sub-menu separator', 'bonfire' ) );

		/* Touchy > Accordion expand icon (down and up) */
		$colors[] = array( 'slug'=>'bonfire_touchy_accordion_expand_icon_down', 'default' => '', 'label' => __( 'Touchy > Accordion expand icon (down)', 'bonfire' ) );
		$colors[] = array( 'slug'=>'bonfire_touchy_accordion_expand_icon_up', 'default' => '', 'label' => __( 'Touchy > Accordion expand icon (up)', 'bonfire' ) );

		/* Touchy > Accordion menu item */
		$colors[] = array( 'slug'=>'bonfire_touchy_accordion_menu_item', 'default' => '', 'label' => __( 'Touchy > Accordion menu item', 'bonfire' ) );
		$colors[] = array( 'slug'=>'bonfire_touchy_accordion_menu_item_hover', 'default' => '', 'label' => __( 'Touchy > Accordion menu item hover', 'bonfire' ) );

		/* Touchy > Expanded menu item */
		$colors[] = array( 'slug'=>'bonfire_touchy_accordion_expanded_menu_item', 'default' => '', 'label' => __( 'Touchy > Accordion expanded menu item', 'bonfire' ) );

		/* Touchy > Expanded menu item background */
		$colors[] = array( 'slug'=>'bonfire_touchy_accordion_expanded_menu_item_background', 'default' => '', 'label' => __( 'Touchy > Accordion expanded menu background', 'bonfire' ) );

		/* Touchy > Sub-menu item with "text" class */
		$colors[] = array( 'slug'=>'bonfire_touchy_accordion_submenu_text_class', 'default' => '', 'label' => __( 'Touchy > Accordion sub-menu item with "text" class', 'bonfire' ) );

		/* Touchy > Sub-menu item */
		$colors[] = array( 'slug'=>'bonfire_touchy_accordion_submenu_item', 'default' => '', 'label' => __( 'Touchy > Accordion sub-menu item', 'bonfire' ) );
		$colors[] = array( 'slug'=>'bonfire_touchy_accordion_submenu_item_hover', 'default' => '', 'label' => __( 'Touchy > Accordion sub-menu item hover', 'bonfire' ) );

		/* Touchy > Content overlay (when menu open) */
		$colors[] = array( 'slug'=>'bonfire_touchy_content_overlay', 'default' => '', 'label' => __( 'Touchy > Content overlay (when menu open)', 'bonfire' ) );

	foreach($colors as $color)
	{

	/* create custom color customization section */
	$wp_customize->add_section( 'touchy_plugin_colors' , array( 'title' => __('Touchy plugin colors', 'bonfire'), 'priority' => 30 ));
	$wp_customize->add_setting( $color['slug'], array( 'default' => $color['default'], 'type' => 'option', 'capability' => 'edit_theme_options' ));
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $color['slug'], array( 'label' => $color['label'], 'section' => 'touchy_plugin_colors', 'settings' => $color['slug'] )));
	}
	}


	//
	// Insert theme customizer options into the footer
	//
	
	function bonfire_touchy_header_customize() {
	?>

		<!-- BEGIN CUSTOM COLORS (WP THEME CUSTOMIZER) -->
		<!-- BACK button -->
		<?php $bonfire_touchy_back_button_background = get_option('bonfire_touchy_back_button_background'); ?>
		<?php $bonfire_touchy_back_button_icon = get_option('bonfire_touchy_back_button_icon'); ?>
		<?php $bonfire_touchy_back_button_background_hover = get_option('bonfire_touchy_back_button_background_hover'); ?>
		<?php $bonfire_touchy_back_button_icon_hover = get_option('bonfire_touchy_back_button_icon_hover'); ?>
		
		<!-- CALL button -->
		<?php $bonfire_touchy_call_button_background = get_option('bonfire_touchy_call_button_background'); ?>
		<?php $bonfire_touchy_call_button_icon = get_option('bonfire_touchy_call_button_icon'); ?>
		<?php $bonfire_touchy_call_button_background_hover = get_option('bonfire_touchy_call_button_background_hover'); ?>
		<?php $bonfire_touchy_call_button_icon_hover = get_option('bonfire_touchy_call_button_icon_hover'); ?>

		<!-- EMAIL button -->
		<?php $bonfire_touchy_email_button_background = get_option('bonfire_touchy_email_button_background'); ?>
		<?php $bonfire_touchy_email_button_icon = get_option('bonfire_touchy_email_button_icon'); ?>
		<?php $bonfire_touchy_email_button_background_hover = get_option('bonfire_touchy_email_button_background_hover'); ?>
		<?php $bonfire_touchy_email_button_icon_hover = get_option('bonfire_touchy_email_button_icon_hover'); ?>

		<!-- MENU button -->
		<?php $bonfire_touchy_menu_button_background = get_option('bonfire_touchy_menu_button_background'); ?>
		<?php $bonfire_touchy_menu_button_icon = get_option('bonfire_touchy_menu_button_icon'); ?>
		<?php $bonfire_touchy_menu_button_background_hover = get_option('bonfire_touchy_menu_button_background_hover'); ?>
		<?php $bonfire_touchy_menu_button_icon_hover = get_option('bonfire_touchy_menu_button_icon_hover'); ?>

		<!-- menu bar separator -->
		<?php $bonfire_touchy_menubar_separator_color = get_option('bonfire_touchy_menubar_separator_color'); ?>

		<!-- accordion menu background -->
		<?php $bonfire_touchy_accordion_menu_background = get_option('bonfire_touchy_accordion_menu_background'); ?>
		
		<!-- accordion menu item background hover -->
		<?php $bonfire_touchy_accordion_menu_item_background_hover = get_option('bonfire_touchy_accordion_menu_item_background_hover'); ?>

		<!-- accordion menu separator -->
		<?php $bonfire_touchy_accordion_menu_separator = get_option('bonfire_touchy_accordion_menu_separator'); ?>

		<!-- accordion sub-menu separator -->
		<?php $bonfire_touchy_accordion_submenu_separator = get_option('bonfire_touchy_accordion_submenu_separator'); ?>
		
		<!-- accordion expand icon (down and up) -->
		<?php $bonfire_touchy_accordion_expand_icon_down = get_option('bonfire_touchy_accordion_expand_icon_down'); ?>
		<?php $bonfire_touchy_accordion_expand_icon_up = get_option('bonfire_touchy_accordion_expand_icon_up'); ?>

		<!-- accordion menu item -->
		<?php $bonfire_touchy_accordion_menu_item = get_option('bonfire_touchy_accordion_menu_item'); ?>
		<?php $bonfire_touchy_accordion_menu_item_hover = get_option('bonfire_touchy_accordion_menu_item_hover'); ?>

		<!-- expanded menu item -->
		<?php $bonfire_touchy_accordion_expanded_menu_item = get_option('bonfire_touchy_accordion_expanded_menu_item'); ?>

		<!-- expanded menu item background -->
		<?php $bonfire_touchy_accordion_expanded_menu_item_background = get_option('bonfire_touchy_accordion_expanded_menu_item_background'); ?>

		<!-- accordion sub-menu item with "text" class -->
		<?php $bonfire_touchy_accordion_submenu_text_class = get_option('bonfire_touchy_accordion_submenu_text_class'); ?>
		
		<!-- accordion sub-menu item -->
		<?php $bonfire_touchy_accordion_submenu_item = get_option('bonfire_touchy_accordion_submenu_item'); ?>
		<?php $bonfire_touchy_accordion_submenu_item_hover = get_option('bonfire_touchy_accordion_submenu_item_hover'); ?>

		<!-- content overlay (when menu open) -->
		<?php $bonfire_touchy_content_overlay = get_option('bonfire_touchy_content_overlay'); ?>
		
		<style>
		/**************************************************************
		*** MAIN MENUBAR COLORS (back + call + email + menu buttons)
		**************************************************************/
		/* BACK button */
		.touchy-wrapper .touchy-back-button { color:<?php echo $bonfire_touchy_back_button_icon; ?>; background-color:<?php echo $bonfire_touchy_back_button_background; ?>; }
		.touchy-wrapper .touchy-back-button:hover { color:<?php echo $bonfire_touchy_back_button_icon_hover; ?>; background-color:<?php echo $bonfire_touchy_back_button_background_hover; ?>; }

		/* CALL button */
		.touchy-wrapper .touchy-call-button { color:<?php echo $bonfire_touchy_call_button_icon; ?>; background-color:<?php echo $bonfire_touchy_call_button_background; ?>; }
		.touchy-wrapper .touchy-call-button:hover { color:<?php echo $bonfire_touchy_call_button_icon_hover; ?>; background-color:<?php echo $bonfire_touchy_call_button_background_hover; ?>; }
		
		/* EMAIL button */
		.touchy-wrapper .touchy-email-button { color:<?php echo $bonfire_touchy_email_button_icon; ?>; background-color:<?php echo $bonfire_touchy_email_button_background; ?>; }
		.touchy-wrapper .touchy-email-button:hover { color:<?php echo $bonfire_touchy_email_button_icon_hover; ?>; background-color:<?php echo $bonfire_touchy_email_button_background_hover; ?>; }
		
		/* MENU button */
		.touchy-menu-button { color:<?php echo $bonfire_touchy_menu_button_icon; ?>; background-color:<?php echo $bonfire_touchy_menu_button_background; ?>; }
		.touchy-menu-button-hover, .touchy-menu-button-hover-touch, .touchy-menu-button-active { color:<?php echo $bonfire_touchy_menu_button_icon_hover; ?>; background-color:<?php echo $bonfire_touchy_menu_button_background_hover; ?>; }
		
		/* menu bar separator */
		.touchy-wrapper .touchy-back-button, .touchy-wrapper .touchy-call-button, .touchy-wrapper .touchy-email-button, .touchy-menu-button { border-color:<?php echo $bonfire_touchy_menubar_separator_color; ?>; }

		/* accordion background */
		.touchy-accordion-tooltip { border-bottom-color:<?php echo $bonfire_touchy_accordion_menu_background; ?>; }
		.touchy-bottom .touchy-accordion-tooltip { border-top-color:<?php echo $bonfire_touchy_accordion_menu_background; ?>; }
		.touchy-by-bonfire { background:<?php echo $bonfire_touchy_accordion_menu_background; ?>; }

		/* accordion menu item hover */
		.touchy-by-bonfire .menu li:hover { background-color:<?php echo $bonfire_touchy_accordion_menu_item_background_hover; ?>; }

		/* accordion menu separator */
		.touchy-by-bonfire .menu li { border-top-color:<?php echo $bonfire_touchy_accordion_menu_separator; ?>; }
		
		/* accordion sub-menu separator */
		.touchy-by-bonfire .sub-menu li { border-bottom-color:<?php echo $bonfire_touchy_accordion_submenu_separator; ?>; }
		
		/* accordion expand icon (down and up) */
		.touchy-by-bonfire .menu-item-has-children:before { color:<?php echo $bonfire_touchy_accordion_expand_icon_down; ?>; }
		.touchy-by-bonfire .menu-item-has-children .menu-expanded:after { color:<?php echo $bonfire_touchy_accordion_expand_icon_up; ?>; }
		
		/* accordion menu item */
		.touchy-by-bonfire .menu a { color:<?php echo $bonfire_touchy_accordion_menu_item; ?>; }
		.touchy-by-bonfire .menu a:hover, .touchy-by-bonfire .menu a:active { color:<?php echo $bonfire_touchy_accordion_menu_item_hover; ?>; }

		/* expanded menu item */
		.touchy-by-bonfire .menu-item-has-children .menu-expanded, .touchy-by-bonfire .menu-item-has-children .menu-expanded:hover { color:<?php echo $bonfire_touchy_accordion_expanded_menu_item; ?>; }

		/* expanded menu item background */
		.touchy-by-bonfire .menu ul, .touchy-by-bonfire .menu-item-has-children .menu-expanded { background-color:<?php echo $bonfire_touchy_accordion_expanded_menu_item_background; ?> !important; }

		/* accordion sub-menu with "text" class */
		.touchy-by-bonfire .sub-menu li.text a { color:<?php echo $bonfire_touchy_accordion_submenu_text_class; ?>; }
		
		/* accordion sub-menu item */
		.touchy-by-bonfire .sub-menu a { color:<?php echo $bonfire_touchy_accordion_submenu_item; ?>; }
		.touchy-by-bonfire .sub-menu a:hover, .touchy-by-bonfire .sub-menu a:active { color:<?php echo $bonfire_touchy_accordion_submenu_item_hover; ?>; }
		
		/* content overlay (when menu open) */
		.touchy-menu-close { background-color:<?php echo $bonfire_touchy_content_overlay; ?>; }
		
		/* menu transparency */
		.touchy-wrapper { opacity:<?php echo get_option('touchy_set_transparency'); ?> }
		</style>
		<!-- END CUSTOM COLORS (WP THEME CUSTOMIZER) -->
	
	<?php
	}
	add_action('wp_footer','bonfire_touchy_header_customize');

?>