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
	$(document).on("click" ,'#import', function() {
		var file = $('#selected_file').val();
		var id   = $(this).data('id');
		$.ajax({
			url: ajax_object.ajaxurl,
			type: 'post',
			data: {
				'action':'ced_import_json_file_data',
				id : id,
				file:file,
				verify_nonce_for_import_single_product : myAjaxObject.nonce_verifify,
			},
			dataType :"html",
			success: function( response ) {
				//console.log(response);
				$('#admin_notice').html(response);
				$('html, body').animate({
					scrollTop: parseInt($("#admin_notice").offset().top)
				}, 2000);
			},
		});
	});

	$(document).on('click','#doaction', function(e) {
		//e.preventDefault();
		var file        = $('#selected_file').val();
		var bulk_action = $('#bulk-action-selector-top').val();
		var selected_id = [];
		$(".bulk-import:checked").each(function(){
			selected_id.push($(this).val());
		});
		//alert(selected_id);
		if (bulk_action == "bulk-import") {
			$.ajax({
				url: ajax_object.ajaxurl,
				type: 'post',
				data: {
					'action':'ced_bulk_import_product',
					selected_id : selected_id,
					file : file,
					verify_nonce_for_import_bulk : myAjaxObject.nonce_verifify,
				},
				dataType :"html",
				success: function( response ) {
					///console.log(admin_notice);
					$('#admin_notice').html(response);
					$('html, body').animate({
						scrollTop: parseInt($("#admin_notice").offset().top)
					}, 2000);
				},
			});
		}
	});

	$(document).ready(function() {
		$('#selected_file').on('change', function() {
			var file = $(this).val();
			$.ajax({
				url: ajax_object.ajaxurl,
				type: 'post',
				data: {
					'action':'ced_display_json_file_data',
					file : file,
					verify_nonce_for_file : myAjaxObject.nonce_verifify,
				},
				dataType :"html",
				success: function( response ) {
					//console.log(response);
					$('#display_json_file_content').html(response);
				},
			});
		});
	});

})( jQuery );
