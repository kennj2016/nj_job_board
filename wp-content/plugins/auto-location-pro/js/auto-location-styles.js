jQuery( document ).ready(function() {

	// Get all styles associated with element
	jQuery.fn.getStyleObject = function(){
	    var dom = this.get(0);
	    var style;
	    var returns = {};
	    if(window.getComputedStyle){
	        var camelize = function(a,b){
	            return b.toUpperCase();
	        }
	        style = window.getComputedStyle(dom, null);
	        for(var i = 0, l = style.length; i < l; i++){
	            var prop = style[i];
	            var camel = prop.replace(/\-([a-z])/, camelize);
	            var val = style.getPropertyValue(prop);
	            returns[camel] = val;
	        }
	        return returns;
	    }
	    if(dom.currentStyle){
	        style = dom.currentStyle;
	        for(var prop in style){
	            returns[prop] = style[prop];
	        }
	        return returns;
	    }
	    if(style = dom.style){
	        for(var prop in style){
	            if(typeof style[prop] != 'function'){
	                returns[prop] = style[prop];
	            }
	        }
	        return returns;
	    }
	    return returns;
	}
	 

	// Apply styles to target element
	if (jQuery('#search_location, #job_location, #_job_location, #candidate_location, #_candidate_location').length > 0 ) {
		var style = jQuery('#search_location, #job_location, #_job_location, #candidate_location, #_candidate_location').getStyleObject();

		jQuery('body').on('DOMNodeInserted ', '.pac-container', function(){
		    jQuery('.pac-item').css(style);
		});
	};


	// Prevent form from being submitted when hitting the enter key to select
/*
	jQuery('#search_location, #job_location').keydown(function (e) {
		if (e.which == 13 && jQuery('.pac-container:visible').length) return false;
	});
*/

});

