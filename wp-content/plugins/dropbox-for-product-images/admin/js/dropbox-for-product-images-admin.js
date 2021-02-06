(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */


	$(document).ready(function() {
		$('#generate_token').on('click', function(e) {
			//e.preventDefault();
			var api_key = $('#api_key').val();
			var secret_key = $('#secret_key').val();
			//alert(api_key);
			//alert(secret_key);
			var redirect_uri = "http://localhost/wordpress/wp-admin/admin.php?page=create_token";
			var uri = "http://www.dropbox.com/oauth2/authorize?client_id="+api_key+"&redirect_uri="+redirect_uri+"&response_type=code";
			window.location.href = uri;
		});

		$("#uploadFile").on('click', function(e) {
			//e.preventDefault();
			var file_data=$('#upload')[0].files[0];
			var id = $(this).data('id');
			console.log(id);
			var form = new FormData(); 
			form.append("file", file_data);
			form.append("action", 'ced_dropbox_upload_image');
			form.append("id", id);
			console.log(form);
			$.ajax({
				enctype:"multipart/form-data",
				url: ajax_object.ajaxurl,
				type: 'post',
				data: form,
				cache: false,
				contentType: false,
				processData: false,
				success: function( response ) {
					location.reload();
				},
			});
		});
		$(document).on('change',"#show_image", function() {
			var show_image = $(this).val();
			var id = $(this).data('id');

			if ($(this).is(':checked') == true) {
				$.ajax({
					url: ajax_object.ajaxurl,
					type: 'post',
					data: {
						show_image :show_image,
						"action" : 'ced_enable_to_show_feature_image',
						id :id
					},
					success: function( response ) {

					},
				});
			} else {
				var show_image = $(this).val("");
				$.ajax({
					url: ajax_object.ajaxurl,
					type: 'post',
					data: {
						show_image :show_image,
						"action" : 'ced_enable_to_show_feature_image',
						id :id
					},
					success: function( response ) {

					},
				});
			}
		});
	});
})( jQuery );