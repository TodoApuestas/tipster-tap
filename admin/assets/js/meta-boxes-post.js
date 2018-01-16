(function ( $ ) {
	"use strict";

	$(function () {
        var eachChange = function($e){
            var post_custom_option = $e.children('option:selected').val();
            if (post_custom_option === 'post') {
                jQuery('#pick_informacion_general').hide();
            } else if (post_custom_option === 'pick') {
                jQuery('#pick_informacion_general').show();
            } else {
                jQuery('#pick_informacion_general').hide();
            }
        };
	    /**
         * Metabox of Post
         */
        jQuery('select#_post_tipo_publicacion')
            .each(function() {
                eachChange(jQuery(this));
            })
            .change(function() {
                eachChange(jQuery(this));
            })
        ;
        /**
         * End Metabox of Post
         */
	});

}(jQuery));