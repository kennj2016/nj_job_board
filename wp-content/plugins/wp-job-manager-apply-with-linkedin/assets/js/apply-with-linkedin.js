jQuery( function( $ ) {
	if ( typeof IN === 'object' ) {
		$('.application_button').click(function(){
			$('.wp-job-manager-application-details').slideUp();
		});
		$('input.apply-with-linkedin').click(function() {
			$(this).attr('disabled','disabled');
        	$(this).css('opacity', '0.7');
			$('.application_details, .wp-job-manager-application-details').slideUp();
			if ( IN.User.isAuthorized() ) {
				$('.apply-with-linkedin-details').slideDown();
			} else {
				IN.UI.Authorize().place();
			}
			return false;
		});

		function displayProfileData( profile ) {
			var profile  = profile.values[0];
			var $profile = $('.apply-with-linkedin-profile');

			if ( ! profile.publicProfileUrl ) {
				$('input.apply-with-linkedin').removeAttr( 'disabled' );
				$('input.apply-with-linkedin').css('opacity', '1');
				IN.User.logout();
				alert( apply_with_linkedin_params.profile_public_i18n );
				return;
			}

			$profile.find('.profile-name').html( profile.formattedName );
			$profile.find('.profile-headline').html( profile.headline );
			$profile.find('.profile-location').html( profile.location.name );
			$profile.find('dd.profile-email').html( profile.emailAddress );
			$('textarea#apply-with-linkedin-cover-letter').append( profile.formattedName );
			$('#apply-with-linkedin-profile-data').val( JSON.stringify( profile, null, '' ) );

			if ( profile.pictureUrl ) {
				$profile.find('img').attr('src', profile.pictureUrl );
				$profile.find('img').attr('alt', profile.formattedName );
			} else {
				$profile.find('img').hide();
			}

			if ( profile.threeCurrentPositions._total > 0 ) {
				$( profile.threeCurrentPositions.values ).each( function( index ) {
					$profile.find('dd.profile-current-positions ul').append( '<li>' + profile.threeCurrentPositions.values[ index ].title + " - " + profile.threeCurrentPositions.values[ index ].company.name + '</li>' );
				});
			} else {
				$profile.find('.profile-current-positions').hide();
			}

			if ( profile.threePastPositions._total > 0 ) {
				$( profile.threePastPositions.values ).each( function( index ) {
					$profile.find('dd.profile-past-positions ul').append( '<li>' + profile.threePastPositions.values[ index ].title + " - " + profile.threePastPositions.values[ index ].company.name + '</li>' );
				});
			} else {
				$profile.find('.profile-past-positions').hide();
			}

			if ( profile.educations._total > 0 ) {
				$( profile.educations.values ).each( function( index ) {
					if ( profile.educations.values[ index ].degree ) {
						$profile.find('dd.profile-educations ul').append( '<li>' + profile.educations.values[ index ].schoolName + ', ' + profile.educations.values[ index ].degree + '</li>' );
					} else {
						$profile.find('dd.profile-educations ul').append( '<li>' + profile.educations.values[ index ].schoolName + '</li>' );
					}
				});
			} else {
				$profile.find('.profile-educations').hide();
			}

			$('.apply-with-linkedin-details').slideDown().triggerHandler('wp-job-manager-application-details-show');
		}
		function onLinkedInAuth() {
			IN.API.Profile("me")
				.fields(
					[
						"firstName",
						"lastName",
						"formattedName",
						"headline",
						"summary",
						"specialties",
						"associations",
						"interests",
						"pictureUrl",
						"publicProfileUrl",
						"emailAddress",
						"location:(name)",
						"dateOfBirth",
						"threeCurrentPositions:(title,company,summary,startDate,endDate,isCurrent)",
						"threePastPositions:(title,company,summary,startDate,endDate,isCurrent)",
						"positions:(title,company,summary,startDate,endDate,isCurrent)",
						"educations:(schoolName,degree,fieldOfStudy,startDate,endDate,activities,notes)",
						"skills:(skill)",
						"phoneNumbers",
						"primaryTwitterAccount"
					]
				)
				.result( function(result) {
					displayProfileData( result );
				}
			);
		}

		IN.Event.on(IN, "auth", onLinkedInAuth);
	}
});