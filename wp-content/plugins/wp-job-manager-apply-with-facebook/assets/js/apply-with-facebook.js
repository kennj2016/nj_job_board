jQuery( function( $ ) {
    $('input.apply-with-facebook-button').click(function() {
        fblogin();
        $(this).attr('disabled','disabled');
        $(this).css('opacity', '0.7');
        return false;
    });

    function statusChangeCallback(response) {
        if ( response.status === 'connected' ) {
            $('.application_details, .wp-job-manager-application-details').slideUp();
            WPJMFacebook();
            $('.apply-with-facebook-details').slideDown().triggerHandler('wp-job-manager-application-details-show');
        } else if (response.status === 'not_authorized') {
            console.log('Facebook user is not authorized');
        } else {
            console.log('Please login to Facebook');
        }
    }

    function checkLoginState() {
        FB.getLoginStatus(function (response) {
            statusChangeCallback(response);
        });
    }

    function fblogin() {
        FB.login(function (response) {
            checkLoginState();
        }, {scope: 'public_profile,email,user_website,user_education_history,user_work_history,user_location,user_about_me'});
    }

    window.fbAsyncInit = function () {
        FB.init({
            appId: apply_with_facebook.appID,
            cookie: true,
            xfbml: true,
            version: 'v2.4'
        });
    };

    (function (d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s);
        js.id = id;
        js.src = "//connect.facebook.net/en_US/sdk.js";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));

    function WPJMFacebook() {
        FB.api('/me', {fields: 'name,bio,location,email,work,education'}, function(response) {
            $('#apply-with-facebook-profile-data').val( JSON.stringify( response, null, '' ) );

            // Profile
            $('.apply-with-facebook-profile .profile-name').append(response.name);
            $('.apply-with-facebook-profile .profile-bio').append(response.bio);
            $('.apply-with-facebook-profile .profile-location').append(response.location.name);
            $('.apply-with-facebook-profile dd.profile-email').append(response.email);

            // Work
            $.each(response.work, function( key, value ) {
                if (typeof value.end_date == 'undefined') {
                    $('.apply-with-facebook-profile .profile-current-positions ul').append(
                        '<li>' + value.position.name + ' - ' + value.employer.name + '</li>'
                    );
                } else {
                    $('.apply-with-facebook-profile .profile-past-positions ul').append(
                        '<li>' + value.position.name + ' - ' + value.employer.name + '</li>'
                    );
                }
            });

            // Education
            $.each(response.education, function( key, value ) {
                if (typeof value.concentration != 'undefined') {
                    var education = '<ul>';
                    $.each(value.concentration, function( key, value ) {
                        education += '<li>' + value.name + '</li>';
                    });
                    education += '</ul>';
                } else {
                    var education = '';
                }
                if ( typeof value.year != 'undefined' ) {
                    var year = ' (' + value.year.name + ')';
                } else {
                    var year = '';
                }
                $('.apply-with-facebook-profile .profile-educations ul').append(
                    '<li>' + value.type + ' - ' + value.school.name + year + education + '</li>'
                );
            });
        });
        FB.api('/me/picture/?redirect=0&type=normal&width=100', function (response) {
            $('.apply-with-facebook-profile img').attr('src', response.data.url);
            $('#apply-with-facebook-profile-picture').val( response.data.url );
        });
    }
});
