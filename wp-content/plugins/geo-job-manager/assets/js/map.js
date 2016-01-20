function gjmMapInit(gjmMapArgs) {

	//clear markers
	if (gjmMarkerCluster != false) {
		gjmMarkerCluster.clearMarkers();
	}

	jobMarkers = new Array();

	var i, gjmIw;
	var gjmOptions 	 = gjmMapArgs['options'];
	var latlngbounds = new google.maps.LatLngBounds();

	if ( removeMarkers == true ) {
		gjmLocations = gjmMapArgs['locations'];
	} else {
		jQuery.merge(gjmLocations, gjmMapArgs['locations']);
	}

	for ( i = 0; i < gjmLocations.length; i++ ) {
			
		//make sure job has geolocation. Only then it can be displayed on the map
		if ( gjmLocations[i]['lat'] != null ) { 
			
			var jobLocation = new google.maps.LatLng(gjmLocations[i]['lat'], gjmLocations[i]['long']);	

			latlngbounds.extend(jobLocation);
		
			jobMarkers[i] = new google.maps.Marker({
				position : jobLocation,
				icon : mapIcon,
				id : i,
				postId:gjmLocations[i]['ID'],
				map : gjmMap
			});
	
			jobMarkers.push(jobMarkers[i]);

			with ({ jobMarker : jobMarkers[i] }) {
				
				//show info window on mouseover
				google.maps.event.addListener( jobMarker, 'mouseover', function() {	
					if (gjmIw) {
						gjmIw.close();
						gjmIw = null;
					}

					gjmIw = new google.maps.InfoWindow({
						content: gjmLocations[jobMarker.id]['info_window']
					});
					gjmIw.open( gjmMap, jobMarker );
				});
								
				google.maps.event.addListener(jobMarker, 'click', function() {
					jQuery('li').removeClass('gjm-marker-clicked');
					jQuery('.post-' + jobMarker.postId).addClass(gjmMapArgs['prefix'] + '-marker-clicked');
	
					if (gjmOptions[gjmMapArgs['prefix']+'_scroll'] == 1) {
						jQuery('html, body').animate({
							scrollTop : jQuery(".post-" + jobMarker.postId).offset().top - 50}
						, 1000);
					}
				});
			}
		}
	}

	gjmMarkerCluster = new MarkerClusterer(gjmMap, jobMarkers);

	if (userMarker != false)
		userMarker.setMap(null);

	if (gjmMapArgs.options['user_location']['lat'] && gjmMapArgs.options['user_location']['lng']) {

		var yourLocation = new google.maps.LatLng( gjmMapArgs.options['user_location']['lat'], gjmMapArgs.options['user_location']['lng']);
		latlngbounds.extend(yourLocation);

		var userIcon;
		userIcon = 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png';

		userMarker = new google.maps.Marker({
			position : new google.maps.LatLng(
					gjmMapArgs.options['user_location']['lat'],
					gjmMapArgs.options['user_location']['lng']),
			map : gjmMap,
			icon : userIcon,
			id : 0
		});
		//jobMarkers.push( userMarker );
	}
	
	gjmMap.fitBounds(latlngbounds);
	
	gjmMapObject  = {
			mapType: 'gjm',
			map:gjmMap,	
			ulMarker: userMarker,
			bounds:latlngbounds,
			markers:jobMarkers
    };
	removeMarkers = true;
};