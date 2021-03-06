/**
 * Send ajax request to the Magento store in order to insert dynamic content into the
 * static page delivered from Varnish
 *
 * @author Fabrizio Branca
 */
//$.noConflict();
jQuery(document).ready(function($) {

	var data = { 
		getBlocks: {}
	};

	// add placeholders
	var counter = 0;
	jQuery('.placeholder').each(function() {
		var id = $(this).attr('id');
		if (!id) {
			// create dynamic id
			id = 'ph_' + counter;
			jQuery(this).attr('id', id);
		}
		var rel = jQuery(this).attr('rel');
		if (rel) {
			data.getBlocks[id] = rel;
			counter++;
		}
	});

	// add current product
	if (typeof CURRENTPRODUCTID !== 'undefined' && CURRENTPRODUCTID) {
		data.currentProductId = CURRENTPRODUCTID;
	}

    if (typeof PAGETAG !== 'undefined' && PAGETAG) {
        data.pageTag = PAGETAG;
    }

	// E.T. phone home
	if (typeof data.currentProductId !== 'undefined' || counter > 0) {
		jQuery.get(
			AJAXHOME_URL,
			data,
			function (response) {
				for(var id in response.blocks) {
					jQuery('#' + id).html(response.blocks[id]);
				}
				// inject session if (TODO: check if this is really needed)
				// $.cookie('frontend', response.sid, { path: '/' });
				
				// TODO: trigger event
			},
			'json'
		);
	}
	
});