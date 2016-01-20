<!-- BEGIN SHOW/HIDE MAIN MENU -->
jQuery('.touchy-menu-button').on('touchstart click', function(e) {
e.preventDefault();

	/* touchstart events */
	if(jQuery('.touchy-by-bonfire').hasClass('touchy-menu-active'))
	{
		/* hide accordion menu */
		jQuery(".touchy-by-bonfire").removeClass("touchy-menu-active");
		jQuery(".touchy-accordion-tooltip").removeClass("touchy-tooltip-active");
		/* hide menu button active colors */
		jQuery(".touchy-menu-button").removeClass("touchy-menu-button-active");
		jQuery(".touchy-menu-button").toggleClass("touchy-menu-button-hover");
		jQuery(".touchy-menu-button").removeClass("touchy-menu-button-hover-touch");
		/* hide close div */
		jQuery('.touchy-menu-close').removeClass('touchy-menu-close-active-opacity');
		setTimeout(function(){
			jQuery('.touchy-menu-close').removeClass('touchy-menu-close-active-position');
		},400);
	} else {
		/* show accordion menu */
		jQuery(".touchy-by-bonfire").addClass("touchy-menu-active");
		jQuery(".touchy-accordion-tooltip").addClass("touchy-tooltip-active");
		/* show menu button active colors */
		jQuery(".touchy-menu-button").addClass("touchy-menu-button-active");
		jQuery(".touchy-menu-button").toggleClass("touchy-menu-button-hover");
		jQuery(".touchy-menu-button").removeClass("touchy-menu-button-hover-touch");
		/* show close div */
		jQuery('.touchy-menu-close').addClass('touchy-menu-close-active-opacity');
		jQuery('.touchy-menu-close').addClass('touchy-menu-close-active-position');
	}

});

jQuery(".touchy-menu-button").hover(
	function() {
		jQuery(".touchy-menu-button").addClass("touchy-menu-button-hover-touch");
	},
	function() {
		jQuery(".touchy-menu-button").removeClass("touchy-menu-button-hover-touch");
});
<!-- END SHOW/HIDE MAIN MENU -->


<!-- BEGIN HIDE MAIN MENU WHEN CLICKED/TAPPED ON CLOSE DIV -->
jQuery('.touchy-menu-close').on('touchstart click', function(e) {
e.preventDefault();

	/* touchstart events */
	/* hide accordion menu */
	jQuery(".touchy-by-bonfire").removeClass("touchy-menu-active");
	jQuery(".touchy-accordion-tooltip").removeClass("touchy-tooltip-active");
	/* hide menu button active colors */
	jQuery(".touchy-menu-button").removeClass("touchy-menu-button-active");
	jQuery(".touchy-menu-button").toggleClass("touchy-menu-button-hover");
	/* hide close div */
	jQuery('.touchy-menu-close').removeClass('touchy-menu-close-active-opacity');
	setTimeout(function(){
		jQuery('.touchy-menu-close').removeClass('touchy-menu-close-active-position');
	},400);

	return false;

});
<!-- END HIDE MAIN MENU WHEN CLICKED/TAPPED ON CLOSE DIV -->


<!-- BEGIN CONVERTING DEFAULT WP MENU TO A SLIDE-DOWN ONE -->
jQuery(document).ready(function ($) {
    jQuery('.menu ul').slideUp(0);

    jQuery('.touchy-by-bonfire .menu-item-has-children').click(function () {
        var target = jQuery(this).children('a');
        if(target.hasClass('menu-expanded')){
            target.removeClass('menu-expanded');
        }else{
            jQuery('.menu-item > a').removeClass('menu-expanded');
            target.addClass('menu-expanded');
        }
        jQuery(this).find('ul:first')
                    .slideToggle(350)
                    .end()
                    .siblings('li')
                    .find('ul')
                    .slideUp(350);
    });

});
<!-- END CONVERTING DEFAULT WP MENU TO A SLIDE-DOWN ONE -->