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

		$("#set_min_qty_for_all_product").after("<span class='qty'></span>");
		$("#set_min_qty_for_all_product").focusout(function(){
			var qty = $(this).val();
			if(qty == "") {
				$(".qty").html("This field is required");
				$(".qty").css("color","red");
				return false;
			}else {
				if(qty < 0) {
					$(".qty").html(" Quantity field can not be negative ");
					$(".qty").css("color","red");
					$(this).val("");
					return false;
				}
				$(".qty").html("");
			}
		});

		$("#wholesale_price_for_simple_product").after("<span class='simple_wholesale_price'></span>");
		$("#wholesale_price_for_simple_product").focusout(function() {
			var regular_price = $('#_regular_price').val();
			var simple_price = $(this).val();
			if(simple_price < 0) {
				$(".simple_wholesale_price").html(" Price field can not be negative ");
				$(".simple_wholesale_price").css("color","red");
				$(this).val("");
				return false;
			}
			if(parseInt(simple_price) > parseInt(regular_price)) {
				$(".simple_wholesale_price").html("Wholesale Price must be less than Regular Price");
				$(".simple_wholesale_price").css("color","red");
				$(this).val("");
				return false;
			}
			$(".simple_wholesale_price").html("");
		});	

		$("#wholesale_qty_for_simple_product").after("<span class='simple_wholesale_qty'></span>");
		$("#wholesale_qty_for_simple_product").focusout(function() {
			var simple_price = $(this).val();
			if(simple_price < 0) {
				$(".simple_wholesale_qty").html("Quantity field can not be negative ");
				$(".simple_wholesale_qty").css("color","red");
				$(this).val("");
				return false;
			}
			$(".simple_wholesale_qty").html("");
		});	

		// $("#variation_wholesale_price").after("<span class='variation_wholesale_error'></span>");
		// $(".save-variation-changes").click(function() {
		// 	var id = $("#wholesale_price_id").val();
		// 	alert(id);
		// });	
	});

})( jQuery );
