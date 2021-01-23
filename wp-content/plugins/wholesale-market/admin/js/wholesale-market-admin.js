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
		$(".form-table").on('click', '#wholesale_qty' , function() {
			if ($(this).is(':checked') == true) {
				$('.forminp-radio').show();
				$('label[for="set_wholesale_qty"]').show();
			} else {
				$('.forminp-radio').hide();
				$('label[for="set_wholesale_qty"]').hide();
			}
		});

		$("input[name='set_wholesale_qty']").on('click',function() {
			var value = $(this).val();
			if (value == "all_product") {
				$('#set_min_qty_for_all_product').show();
				$('label[for="set_min_qty_for_all_product"]').show();
			} else {
				$('#set_min_qty_for_all_product').hide();
				$('label[for="set_min_qty_for_all_product"]').hide();
			}
		});

		
	});

})( jQuery );
