jQuery( document ).ready(
	function(){
		jQuery( "#ced_catch_save_license" ).click(
			function(){

				jQuery( ".ced_catch_loader" ).show();

				var license_key = jQuery( "#ced_catch_license_key" ).val();

				if (license_key == '' || license_key == null) {

					jQuery( "#ced_catch_license_key" ).attr( 'style','border:1px solid red' );
					 jQuery( ".ced_catch_loader" ).hide( 2000 );
					return false;
				} else {
					jQuery( "#ced_catch_license_key" ).removeAttr( 'style' );
				}

				var data = {	'action':'ced_catch_validate_licensce',
					'license_key':license_key,
				};

				jQuery.post(
					ajaxurl,
					data,
					function(data){

						jQuery( ".ced_catch_loader" ).hide();

						if (data.hasOwnProperty( 'response' )) {
							if (data['response'] == 'success') {
								jQuery( '.licennse_notification' ).text( 'Validated' );
								jQuery( '.licennse_notification' ).attr( 'style','color:green' );
								location.reload();
							} else if (data['response'] == 'failure') {
								var html = "";
								html    += '<div class="notice notice-error"><p>Invalid License Key</p></div>';
								jQuery( ".success-admin-notices" ).append( html );
							}
						}
					},
					'json'
				);
			}
		);
	}
);
