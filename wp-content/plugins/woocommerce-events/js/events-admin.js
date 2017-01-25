(function($) {
	
	if ( $( "#WooCommerceEventsEvent" ).length ) {
	
		checkEventForm();
		
		$('#WooCommerceEventsEvent').change(function() {

			checkEventForm();
			
		})

		jQuery('#WooCommerceEventsDate').datepicker({
				dateFormat : 'd MM yy'
		});

		
		var fileInput = '';

		jQuery('.wrap').on('click', '.upload_image_button_woocommerce_events', function(e) {
			e.preventDefault();

			var button = jQuery(this);
			var id = jQuery(this).parent().prev('input.uploadfield');
			wp.media.editor.send.attachment = function(props, attachment) {
				id.val(attachment.url);
			};
			wp.media.editor.open(button);
			return false;
		});

		jQuery('.upload_reset').click(function() {
				jQuery(this).parent().prev('input.uploadfield').val('');
		});

		// user inserts file into post. only run custom if user started process using the above process
		// window.send_to_editor(html) is how wp would normally handle the received data

		window.original_send_to_editor = window.send_to_editor;
		window.send_to_editor = function(html){

			window.original_send_to_editor(html);

		};

	}
	
	// Start functions 
		function checkEventForm() {
			
			var WooCommerceEventsEvent = $('#WooCommerceEventsEvent').val();
			
			if(WooCommerceEventsEvent == 'Event') {

				jQuery('#WooCommerceEventsForm').show();

			} else {
				
				jQuery('#WooCommerceEventsForm').hide();
				
			}

		} 
	
})(jQuery);


(function( $ ) {
    
        jQuery(function() {
        jQuery('.color-field').wpColorPicker();
        
    });
    
})( jQuery );

