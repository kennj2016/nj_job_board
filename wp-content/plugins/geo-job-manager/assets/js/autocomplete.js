jQuery(document).ready(function($) {

	//disable the original change event of the location inputbox.
	//we do so because of the conflict between the change event to the autocomplete when selecting location.
	//we add keypress event instead so enter key will submit the search	
	if ( $('#search_location').length ) {
		$('#search_location').unbind('change').bind('keypress', function (e){
	        if(e.keyCode == 13) {
	        	$('#search_location').val( $(this).val() );
	        	var target = $(this).closest('div.'+AutoCompOptions['form_type']);
	            target.trigger('update_results', [1, false]);
	        }	
	    });
	}
	
	$( '#search_location' ).change( function(e) {
		$('#gjm_lat').val('');
		$('#gjm_lng').val('');
	})
		
	input = document.getElementById(AutoCompOptions['input_field']);

	//autocomplete for all countries
	if ( AutoCompOptions['options']['country'] == '' ) {
		var options = {};
    //otherwise restrict to single country
    } else {
    	var options = {
        		componentRestrictions: { country: AutoCompOptions['options']['country'] }
        };
    }

    var autocomplete = new google.maps.places.Autocomplete(input, options);
    
    google.maps.event.addListener(autocomplete, 'place_changed', function(e) {

    	var place = autocomplete.getPlace();

		if (!place.geometry) {
			return;
		}
		
		//only for search forms autocomplete
		if ( $('#search_location').length ) {
			//update coords fields
			$('#gjm_lat').val(place.geometry.location.lat());
			$('#gjm_lng').val(place.geometry.location.lng());
			
			//update address field
			$('#search_location').attr('value',place.formatted_address);
			
			var target = $('#search_location').closest('div.'+AutoCompOptions['form_type']);
			
			//submit the form
	        target.trigger('update_results', [1, false]);
		}
    });      
});